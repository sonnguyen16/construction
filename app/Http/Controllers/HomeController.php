<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\PaymentVoucher;
use App\Models\Project;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index(Request $request)
    {
        $totalProjects = Project::whereNull('deleted_at')->count();
        $completedProjects = Project::whereNull('deleted_at')->where('status', 'completed')->count();
        $inProgressProjects = Project::whereNull('deleted_at')->where('status', 'active')->count();

        // Lấy thông tin ngày hiện tại hoặc từ request
        $currentDate = Carbon::now();
        $currentYear = $request->input('year', $currentDate->year);
        $currentMonth = $request->input('month', $currentDate->month);
        $view = $request->input('view', null);

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
        $currentYearForPayments = Carbon::now()->year;
        $paymentsByMonth = [];

        // Lấy dữ liệu phiếu chi theo tháng và trạng thái
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYearForPayments)
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

        // Lấy dữ liệu chi tiết cho biểu đồ đường phiếu thu và phiếu chi
        // Nếu có tham số 'view' trong request, chỉ lấy dữ liệu cho chế độ xem đó

        // Lấy dữ liệu theo ngày trong tháng được chọn
        $daysInMonth = Carbon::createFromDate($currentYear, $currentMonth, 1)->daysInMonth;
        $dailyData = $view === 'day' || $view === null ? $this->getDailyVoucherData($currentYear, $currentMonth, $daysInMonth) : null;

        // Lấy dữ liệu theo tháng trong năm được chọn
        $monthlyData = $view === 'month' || $view === null ? $this->getMonthlyVoucherData($currentYear) : null;

        // Lấy dữ liệu theo năm (5 năm gần đây)
        $yearlyData = $view === 'year' || $view === null ? $this->getYearlyVoucherData(5) : null;

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
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'dailyData' => $dailyData,
            'monthlyData' => $monthlyData,
            'yearlyData' => $yearlyData
        ]);
    }

    /**
     * Lấy dữ liệu phiếu thu và phiếu chi theo ngày trong tháng
     */
    private function getDailyVoucherData($year, $month, $daysInMonth)
    {
        $result = [
            'labels' => [],
            'receipts' => [
                'paid' => [],
                'unpaid' => []
            ],
            'payments' => [
                'paid' => [],
                'unpaid' => []
            ]
        ];

        // Tạo mảng nhãn cho các ngày trong tháng
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $result['labels'][] = $day;

            // Khởi tạo giá trị mặc định
            $result['receipts']['paid'][$day] = 0;
            $result['receipts']['unpaid'][$day] = 0;
            $result['payments']['paid'][$day] = 0;
            $result['payments']['unpaid'][$day] = 0;
        }

        // Lấy dữ liệu phiếu thu theo ngày và trạng thái
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->selectRaw('DAY(created_at) as day, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day', 'status')
            ->orderBy('day')
            ->get();

        // Cập nhật dữ liệu phiếu thu
        foreach ($receiptData as $receipt) {
            $day = $receipt->day;
            if ($receipt->status === 'paid') {
                $result['receipts']['paid'][$day] = (float) $receipt->total_amount;
            } else {
                $result['receipts']['unpaid'][$day] = (float) $receipt->total_amount;
            }
        }

        // Lấy dữ liệu phiếu chi theo ngày và trạng thái
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->selectRaw('DAY(created_at) as day, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day', 'status')
            ->orderBy('day')
            ->get();

        // Cập nhật dữ liệu phiếu chi
        foreach ($paymentData as $payment) {
            $day = $payment->day;
            if ($payment->status === 'paid') {
                $result['payments']['paid'][$day] = (float) $payment->total_amount;
            } else {
                $result['payments']['unpaid'][$day] = (float) $payment->total_amount;
            }
        }

        // Chuyển dữ liệu từ dạng associative array sang dạng indexed array
        $result['receipts']['paid'] = array_values($result['receipts']['paid']);
        $result['receipts']['unpaid'] = array_values($result['receipts']['unpaid']);
        $result['payments']['paid'] = array_values($result['payments']['paid']);
        $result['payments']['unpaid'] = array_values($result['payments']['unpaid']);

        return $result;
    }

    /**
     * Lấy dữ liệu phiếu thu và phiếu chi theo tháng trong năm
     */
    private function getMonthlyVoucherData($year)
    {
        $result = [
            'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            'receipts' => [
                'paid' => array_fill(0, 12, 0),
                'unpaid' => array_fill(0, 12, 0)
            ],
            'payments' => [
                'paid' => array_fill(0, 12, 0),
                'unpaid' => array_fill(0, 12, 0)
            ]
        ];

        // Lấy dữ liệu phiếu thu theo tháng và trạng thái
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $year)
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        // Cập nhật dữ liệu phiếu thu
        foreach ($receiptData as $receipt) {
            $monthIndex = $receipt->month - 1; // Chuyển từ 1-12 sang 0-11 cho mảng
            if ($receipt->status === 'paid') {
                $result['receipts']['paid'][$monthIndex] = (float) $receipt->total_amount;
            } else {
                $result['receipts']['unpaid'][$monthIndex] = (float) $receipt->total_amount;
            }
        }

        // Lấy dữ liệu phiếu chi theo tháng và trạng thái
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $year)
            ->groupBy('month', 'status')
            ->orderBy('month')
            ->get();

        // Cập nhật dữ liệu phiếu chi
        foreach ($paymentData as $payment) {
            $monthIndex = $payment->month - 1; // Chuyển từ 1-12 sang 0-11 cho mảng
            if ($payment->status === 'paid') {
                $result['payments']['paid'][$monthIndex] = (float) $payment->total_amount;
            } else {
                $result['payments']['unpaid'][$monthIndex] = (float) $payment->total_amount;
            }
        }

        return $result;
    }

    /**
     * Lấy dữ liệu phiếu thu và phiếu chi theo năm
     */
    private function getYearlyVoucherData($numberOfYears)
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - $numberOfYears + 1;

        $years = [];
        for ($year = $startYear; $year <= $currentYear; $year++) {
            $years[] = $year;
        }

        $result = [
            'labels' => $years,
            'receipts' => [
                'paid' => array_fill(0, $numberOfYears, 0),
                'unpaid' => array_fill(0, $numberOfYears, 0)
            ],
            'payments' => [
                'paid' => array_fill(0, $numberOfYears, 0),
                'unpaid' => array_fill(0, $numberOfYears, 0)
            ]
        ];

        // Lấy dữ liệu phiếu thu theo năm và trạng thái
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->selectRaw('YEAR(created_at) as year, status, SUM(amount) as total_amount')
            ->whereYear('created_at', '>=', $startYear)
            ->whereYear('created_at', '<=', $currentYear)
            ->groupBy('year', 'status')
            ->orderBy('year')
            ->get();

        // Cập nhật dữ liệu phiếu thu
        foreach ($receiptData as $receipt) {
            $yearIndex = $receipt->year - $startYear;
            if ($receipt->status === 'paid') {
                $result['receipts']['paid'][$yearIndex] = (float) $receipt->total_amount;
            } else {
                $result['receipts']['unpaid'][$yearIndex] = (float) $receipt->total_amount;
            }
        }

        // Lấy dữ liệu phiếu chi theo năm và trạng thái
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->selectRaw('YEAR(created_at) as year, status, SUM(amount) as total_amount')
            ->whereYear('created_at', '>=', $startYear)
            ->whereYear('created_at', '<=', $currentYear)
            ->groupBy('year', 'status')
            ->orderBy('year')
            ->get();

        // Cập nhật dữ liệu phiếu chi
        foreach ($paymentData as $payment) {
            $yearIndex = $payment->year - $startYear;
            if ($payment->status === 'paid') {
                $result['payments']['paid'][$yearIndex] = (float) $payment->total_amount;
            } else {
                $result['payments']['unpaid'][$yearIndex] = (float) $payment->total_amount;
            }
        }

        return $result;
    }
}
