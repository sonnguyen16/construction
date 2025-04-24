<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
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
