<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BidPackage;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\PaymentVoucher;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index()
    {
        $totalProjects = Project::whereNull('deleted_at')->count();
        $completedProjects = Project::whereNull('deleted_at')->where('status', 'completed')->count();
        $inProgressProjects = Project::whereNull('deleted_at')->where('status', 'active')->count();

        // Lấy các gói thầu gốc (không có parent_id hoặc parent_id = null)
        $rootBidPackages = BidPackage::whereNull('deleted_at')
            ->whereNull('parent_id')
            ->get();
            
        // Tính toán doanh thu (tổng giá dự thầu hiển thị - display_estimated_price)
        $totalRevenue = $rootBidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_estimated_price;
        });

        // Tính toán tổng thu (phiếu thu có trạng thái paid)
        $totalReceiptAmount = ReceiptVoucher::whereNull('deleted_at')
            ->where('status', 'paid')
            ->sum('amount');

        // Tính toán phải thu (tổng giá dự thầu - tổng thu)
        $receivables = $totalRevenue - $totalReceiptAmount;

        // Tính toán chi phí (tổng giá giao thầu hiển thị - display_client_price)
        $totalExpense = $rootBidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_client_price;
        });

        // Tính toán tổng chi (phiếu chi có trạng thái paid)
        $totalPaymentAmount = PaymentVoucher::whereNull('deleted_at')
            ->where('status', 'paid')
            ->sum('amount');

        // Tính toán phải chi (tổng giá giao thầu - tổng chi)
        $payables = $totalExpense - $totalPaymentAmount;

        // Tính toán lợi nhuận (tổng giá dự thầu - tổng giá giao thầu)
        $profit = $totalRevenue - $totalExpense;

        // Đề xuất thu (phiếu thu có trạng thái unpaid)
        $pendingReceiptCount = ReceiptVoucher::whereNull('deleted_at')->where('status', 'unpaid')->count();
        $pendingReceiptAmount = ReceiptVoucher::whereNull('deleted_at')->where('status', 'unpaid')->sum('amount');

        // Đề xuất chi (phiếu chi có trạng thái approved)
        $pendingPaymentCount = PaymentVoucher::whereNull('deleted_at')->where('status', 'proposed')->count();
        $pendingPaymentAmount = PaymentVoucher::whereNull('deleted_at')->where('status', 'proposed')->sum('amount');
        // Lấy tổng tiền chi theo nhà thầu
        $paymentsByContractor = PaymentVoucher::whereNull('deleted_at')->selectRaw('contractor_id, SUM(amount) as total_amount')
            ->with('contractor')
            ->groupBy('contractor_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền thu theo khách hàng
        $receiptsByCustomer = ReceiptVoucher::whereNull('deleted_at')->selectRaw('customer_id, SUM(amount) as total_amount')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền chi theo tháng trong năm hiện tại, phân tách theo trạng thái
        $currentYear = Carbon::now()->year;
        $paymentsByMonth = [];
        
        // Lấy dữ liệu phiếu chi theo tháng và trạng thái
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();
            
        // Tổ chức dữ liệu theo tháng và trạng thái
        foreach ($paymentData as $payment) {
            if (!isset($paymentsByMonth[$payment->month])) {
                $paymentsByMonth[$payment->month] = [
                    'paid' => 0,
                    'unpaid' => 0
                ];
            }
            
            if ($payment->status === 'paid') {
                $paymentsByMonth[$payment->month]['paid'] = $payment->total_amount;
            } else {
                $paymentsByMonth[$payment->month]['unpaid'] = $payment->total_amount;
            }
        }

        // Lấy tổng tiền thu theo tháng trong năm hiện tại, phân tách theo trạng thái
        $receiptsByMonth = [];
        
        // Lấy dữ liệu phiếu thu theo tháng và trạng thái
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();
            
        // Tổ chức dữ liệu theo tháng và trạng thái
        foreach ($receiptData as $receipt) {
            if (!isset($receiptsByMonth[$receipt->month])) {
                $receiptsByMonth[$receipt->month] = [
                    'paid' => 0,
                    'unpaid' => 0
                ];
            }
            
            if ($receipt->status === 'paid') {
                $receiptsByMonth[$receipt->month]['paid'] = $receipt->total_amount;
            } else {
                $receiptsByMonth[$receipt->month]['unpaid'] = $receipt->total_amount;
            }
        }

        return Inertia::render('Home', [
            'stats' => [
                'totalProjects' => $totalProjects,
                'completedProjects' => $completedProjects,
                'inProgressProjects' => $inProgressProjects,
                'totalBidPackages' => BidPackage::whereNull('deleted_at')->count(),
                'totalContractors' => Contractor::whereNull('deleted_at')->count(),
                'totalCustomers' => Customer::whereNull('deleted_at')->count(),
                'totalRevenue' => $totalRevenue,
                'totalReceiptAmount' => $totalReceiptAmount,
                'receivables' => $receivables,
                'totalExpense' => $totalExpense,
                'totalPaymentAmount' => $totalPaymentAmount,
                'payables' => $payables,
                'profit' => $profit,
                'pendingReceiptCount' => $pendingReceiptCount,
                'pendingReceiptAmount' => $pendingReceiptAmount,
                'pendingPaymentCount' => $pendingPaymentCount,
                'pendingPaymentAmount' => $pendingPaymentAmount
            ],
            'paymentsByContractor' => $paymentsByContractor,
            'receiptsByCustomer' => $receiptsByCustomer,
            'paymentsByMonth' => $paymentsByMonth,
            'receiptsByMonth' => $receiptsByMonth,
            'currentYear' => $currentYear
        ]);
    }
}
