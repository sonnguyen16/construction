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

        // Tính toán doanh thu (tổng giá dự thầu - estimated_price)
        $totalRevenue = BidPackage::whereNull('deleted_at')->sum('estimated_price');

        // Tính toán tổng thu (phiếu thu có trạng thái paid)
        $totalReceiptAmount = ReceiptVoucher::whereNull('deleted_at')->where('status', 'paid')->sum('amount');

        // Tính toán phải thu (tổng giá dự thầu - tổng thu)
        $receivables = $totalRevenue - $totalReceiptAmount;

        // Tính toán chi phí (tổng giá giao thầu - client_price)
        $totalExpense = BidPackage::whereNull('deleted_at')->sum('client_price');

        // Tính toán tổng chi (phiếu chi có trạng thái paid)
        $totalPaymentAmount = PaymentVoucher::whereNull('deleted_at')->where('status', 'paid')->sum('amount');

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

        // Lấy tổng tiền chi theo tháng trong năm hiện tại
        $currentYear = Carbon::now()->year;
        $paymentsByMonth = PaymentVoucher::whereNull('deleted_at')->selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

        // Lấy tổng tiền thu theo tháng trong năm hiện tại
        $receiptsByMonth = ReceiptVoucher::whereNull('deleted_at')->selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month')
            ->map(function ($item) {
                return $item->total_amount;
            })
            ->toArray();

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
