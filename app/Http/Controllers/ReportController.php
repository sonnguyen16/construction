<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\PaymentCategory;
use App\Models\PaymentVoucher;
use App\Models\Project;
use App\Models\ReceiptCategory;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Hiển thị trang báo cáo công nợ nhà cung cấp/nhà thầu
     */
    public function contractorDebtReport()
    {
        // Lấy danh sách nhà thầu/nhà cung cấp có phiếu chi
        $contractors = Contractor::whereHas('paymentVouchers')->get();

        $debtData = [];

        foreach ($contractors as $contractor) {
            // Tổng giao thầu: tổng display_client_price của các gói thầu cha
            $totalPurchase = 0;
            $contractorBidPackages = BidPackage::whereNull('parent_id')
                ->where('selected_contractor_id', $contractor->id)
                ->whereNull('deleted_at')
                ->get();
            
            foreach ($contractorBidPackages as $bidPackage) {
                $totalPurchase += $bidPackage->display_client_price;
            }

            // Tổng chi trả: tổng amount các phiếu chi có trạng thái paid
            $totalPaid = PaymentVoucher::where('contractor_id', $contractor->id)
                ->where('status', 'paid')
                ->whereNull('deleted_at')
                ->sum('amount');

            // Còn lại: tổng giao thầu trừ đi tổng chi trả
            $remaining = $totalPurchase - $totalPaid;

            // Lấy chi tiết theo từng dự án
            $projectDetails = [];
            $contractorProjects = Project::whereHas('bidPackages', function ($query) use ($contractor) {
                $query->where('selected_contractor_id', $contractor->id);
            })->get();

            foreach ($contractorProjects as $project) {
                // Tổng giao thầu của dự án
                $projectPurchase = 0;
                $projectBidPackages = BidPackage::whereNull('parent_id')
                    ->where('project_id', $project->id)
                    ->where('selected_contractor_id', $contractor->id)
                    ->whereNull('deleted_at')
                    ->get();
                
                foreach ($projectBidPackages as $bidPackage) {
                    $projectPurchase += $bidPackage->display_client_price;
                }

                // Tổng chi trả của dự án
                $projectPaid = PaymentVoucher::where('contractor_id', $contractor->id)
                    ->where('project_id', $project->id)
                    ->where('status', 'paid')
                    ->whereNull('deleted_at')
                    ->sum('amount');

                // Còn lại của dự án
                $projectRemaining = $projectPurchase - $projectPaid;

                if ($projectPurchase > 0 || $projectPaid > 0) {
                    $projectDetails[] = [
                        'project' => $project,
                        'total_purchase' => $projectPurchase,
                        'total_paid' => $projectPaid,
                        'remaining' => $projectRemaining
                    ];
                }
            }

            // Chỉ hiển thị nhà thầu có công nợ
            if ($totalPurchase > 0 || $totalPaid > 0) {
                $debtData[] = [
                    'contractor' => $contractor,
                    'total_purchase' => $totalPurchase,
                    'total_paid' => $totalPaid,
                    'remaining' => $remaining,
                    'project_details' => $projectDetails
                ];
            }
        }

        return Inertia::render('Reports/ContractorDebt', [
            'debtData' => $debtData
        ]);
    }

    /**
     * Hiển thị trang báo cáo công nợ khách hàng
     */
    public function customerDebtReport()
    {
        // Lấy danh sách khách hàng có phiếu thu
        $customers = Customer::whereHas('receiptVouchers')->get();

        $debtData = [];

        foreach ($customers as $customer) {
            // Lấy các dự án của khách hàng
            $customerProjects = Project::where('customer_id', $customer->id)->get();
            
            // Tổng dự án: tổng display_estimated_price của các gói thầu cha
            $totalProject = 0;
            $projectDetails = [];

            foreach ($customerProjects as $project) {
                // Tổng giá trị dự án
                $projectValue = 0;
                $projectBidPackages = BidPackage::whereNull('parent_id')
                    ->where('project_id', $project->id)
                    ->whereNull('deleted_at')
                    ->get();
                
                foreach ($projectBidPackages as $bidPackage) {
                    $projectValue += $bidPackage->display_estimated_price;
                }

                // Tổng chi trả của dự án
                $projectPaid = ReceiptVoucher::where('customer_id', $customer->id)
                    ->where('project_id', $project->id)
                    ->where('status', 'paid')
                    ->whereNull('deleted_at')
                    ->sum('amount');

                // Còn lại của dự án
                $projectRemaining = $projectValue - $projectPaid;

                if ($projectValue > 0 || $projectPaid > 0) {
                    $projectDetails[] = [
                        'project' => $project,
                        'total_project' => $projectValue,
                        'total_paid' => $projectPaid,
                        'remaining' => $projectRemaining
                    ];
                    $totalProject += $projectValue;
                }
            }

            // Tổng chi trả: tổng amount các phiếu có trạng thái paid
            $totalPaid = ReceiptVoucher::where('customer_id', $customer->id)
                ->where('status', 'paid')
                ->whereNull('deleted_at')
                ->sum('amount');

            // Còn lại: tổng dự án trừ đi tổng chi trả
            $remaining = $totalProject - $totalPaid;

            // Chỉ hiển thị khách hàng có công nợ
            if ($totalProject > 0 || $totalPaid > 0) {
                $debtData[] = [
                    'customer' => $customer,
                    'total_project' => $totalProject,
                    'total_paid' => $totalPaid,
                    'remaining' => $remaining,
                    'project_details' => $projectDetails
                ];
            }
        }

        return Inertia::render('Reports/CustomerDebt', [
            'debtData' => $debtData
        ]);
    }

    /**
     * Hiển thị trang báo cáo thu chi
     */
    public function financialReport(Request $request)
    {
        // Lấy danh sách dự án để filter
        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();

        // Lấy project_id từ request hoặc lấy dự án đầu tiên nếu không có
        $projectId = $request->input('project_id', $projects->first()->id ?? null);

        // Nếu không có dự án nào, trả về trang báo cáo trống
        if (!$projectId) {
            return Inertia::render('Reports/Financial', [
                'projects' => $projects,
                'selectedProject' => null,
                'reportData' => null
            ]);
        }

        // Lấy thông tin dự án được chọn
        $selectedProject = Project::with(['bidPackages' => function ($query) {
            $query->whereNull('parent_id'); // Chỉ lấy các gói thầu cha
        }])->findOrFail($projectId);

        // Tính tổng dự toán, phát sinh và tổng giao thầu
        $totalEstimatedPrice = $selectedProject->bidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_estimated_price;
        });

        $totalAdditionalPrice = $selectedProject->bidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_additional_price;
        });

        $totalClientPrice = $selectedProject->bidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_client_price;
        });

        // Lấy danh sách phiếu thu có trạng thái paid
        $receipts = ReceiptVoucher::with('receiptCategory')
            ->where('project_id', $projectId)
            ->where('status', 'paid')
            ->get()
            ->groupBy('receipt_category_id');

        // Lấy danh sách phiếu chi có trạng thái paid
        $payments = PaymentVoucher::with('paymentCategory')
            ->where('project_id', $projectId)
            ->where('status', 'paid')
            ->get()
            ->groupBy('payment_category_id');

        // Tổng hợp danh sách loại thu chi và số tiền tương ứng
        $receiptCategories = ReceiptCategory::all();
        $paymentCategories = PaymentCategory::all();

        $receiptItems = [];
        foreach ($receiptCategories as $category) {
            $amount = isset($receipts[$category->id])
                ? $receipts[$category->id]->sum('amount')
                : 0;

            if ($amount > 0) {
                $receiptItems[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'amount' => $amount
                ];
            }
        }

        $paymentItems = [];
        foreach ($paymentCategories as $category) {
            $amount = isset($payments[$category->id])
                ? $payments[$category->id]->sum('amount')
                : 0;

            if ($amount > 0) {
                $paymentItems[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'amount' => $amount
                ];
            }
        }

        // Tính tổng thu và tổng chi
        $totalReceipt = array_sum(array_column($receiptItems, 'amount'));
        $totalPayment = array_sum(array_column($paymentItems, 'amount'));

        // Tính phải thu và phải chi
        $receivables = $totalEstimatedPrice - $totalReceipt;
        $payables = $totalClientPrice - $totalPayment;

        // Chuẩn bị dữ liệu báo cáo
        $reportData = [
            'totalEstimatedPrice' => $totalEstimatedPrice,
            'totalAdditionalPrice' => $totalAdditionalPrice,
            'totalClientPrice' => $totalClientPrice,
            'receiptItems' => $receiptItems,
            'paymentItems' => $paymentItems,
            'totalReceipt' => $totalReceipt,
            'totalPayment' => $totalPayment,
            'receivables' => $receivables,
            'payables' => $payables
        ];

        return Inertia::render('Reports/Financial', [
            'projects' => $projects,
            'selectedProject' => $selectedProject,
            'reportData' => $reportData
        ]);
    }
}
