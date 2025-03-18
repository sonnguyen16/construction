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
        $totalProjects = Project::count();
        $totalBidPackages = BidPackage::count();
        $totalContractors = Contractor::count();
        $totalCustomers = Customer::count();
        $totalPaymentAmount = PaymentVoucher::sum('amount');
        $totalReceiptAmount = ReceiptVoucher::sum('amount');
        $balance = $totalReceiptAmount - $totalPaymentAmount;
        $pendingReceiptCount = ReceiptVoucher::where('status', 'unpaid')->count();
        $pendingPaymentCount = PaymentVoucher::where('status', 'unpaid')->count();

        // Lấy 5 phiếu chi mới nhất
        $recentPaymentVouchers = PaymentVoucher::with(['contractor', 'bidPackage.project', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Lấy 5 phiếu thu mới nhất
        $recentReceiptVouchers = ReceiptVoucher::with(['customer', 'project', 'bidPackage', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Lấy tổng tiền chi theo nhà thầu
        $paymentsByContractor = PaymentVoucher::selectRaw('contractor_id, SUM(amount) as total_amount')
            ->with('contractor')
            ->groupBy('contractor_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền thu theo khách hàng
        $receiptsByCustomer = ReceiptVoucher::selectRaw('customer_id, SUM(amount) as total_amount')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền chi theo tháng trong năm hiện tại
        $currentYear = Carbon::now()->year;
        $paymentsByMonth = PaymentVoucher::selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
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
        $receiptsByMonth = ReceiptVoucher::selectRaw('MONTH(created_at) as month, SUM(amount) as total_amount')
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
                'totalBidPackages' => $totalBidPackages,
                'totalContractors' => $totalContractors,
                'totalCustomers' => $totalCustomers,
                'totalPaymentAmount' => $totalPaymentAmount,
                'totalReceiptAmount' => $totalReceiptAmount,
                'balance' => $balance,
                'pendingReceiptCount' => $pendingReceiptCount,
                'pendingPaymentCount' => $pendingPaymentCount
            ],
            'recentPaymentVouchers' => $recentPaymentVouchers,
            'recentReceiptVouchers' => $recentReceiptVouchers,
            'paymentsByContractor' => $paymentsByContractor,
            'receiptsByCustomer' => $receiptsByCustomer,
            'paymentsByMonth' => $paymentsByMonth,
            'receiptsByMonth' => $receiptsByMonth,
            'currentYear' => $currentYear
        ]);
    }
}
