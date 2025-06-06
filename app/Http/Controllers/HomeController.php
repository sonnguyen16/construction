<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
use App\Models\Contractor;
use App\Models\Customer;
use App\Models\Loan;
use App\Models\PaymentVoucher;
use App\Models\Project;
use App\Models\ReceiptVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Helpers\ProjectPermission;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index(Request $request)
    {
        // Lấy danh sách các dự án mà người dùng có quyền truy cập
        $accessibleProjectIds = ProjectPermission::getAccessibleProjectIds();

        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $totalProjects = Project::whereNull('deleted_at')
            ->whereIn('id', $accessibleProjectIds)
            ->count();
        $completedProjects = Project::whereNull('deleted_at')
            ->whereIn('id', $accessibleProjectIds)
            ->where('status', 'completed')
            ->count();
        $inProgressProjects = Project::whereNull('deleted_at')
            ->whereIn('id', $accessibleProjectIds)
            ->where('status', 'active')
            ->count();

        // Lấy thông tin ngày hiện tại hoặc từ request
        $currentDate = Carbon::now();
        $currentYear = $request->input('year', $currentDate->year);
        $currentMonth = $request->input('month', $currentDate->month);
        $view = $request->input('view', null);

        // Lấy các gói thầu gốc (không có parent_id hoặc parent_id = null)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $rootBidPackages = BidPackage::whereNull('deleted_at')
            ->whereNull('parent_id')
            ->whereIn('project_id', $accessibleProjectIds)
            ->get();

        // Tính toán doanh thu (tổng giá dự thầu hiển thị - display_estimated_price)
        $totalRevenue = $rootBidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_estimated_price;
        });

        // Tính toán tổng thu (phiếu thu có trạng thái paid)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $totalReceiptAmount = ReceiptVoucher::whereNull('deleted_at')
            ->where('status', 'paid')
            ->whereIn('project_id', $accessibleProjectIds)
            ->sum('amount');

        // Tính toán phải thu (tổng giá dự thầu - tổng thu)
        $receivables = $totalRevenue - $totalReceiptAmount;

        // Tính toán chi phí (tổng giá giao thầu hiển thị - display_client_price)
        $totalExpense = $rootBidPackages->sum(function ($bidPackage) {
            return $bidPackage->display_client_price;
        });

        // Tính toán tổng chi (phiếu chi có trạng thái paid)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $totalPaymentAmount = PaymentVoucher::whereNull('deleted_at')
            ->where('status', 'paid')
            ->whereIn('project_id', $accessibleProjectIds)
            ->sum('amount');

        // Tính toán phải chi (tổng giá giao thầu - tổng chi)
        $payables = $totalExpense - $totalPaymentAmount;

        // Tính toán lợi nhuận (tổng giá dự thầu - tổng giá giao thầu)
        $profit = $totalRevenue - $totalExpense;

        // Đề xuất thu (phiếu thu có trạng thái unpaid)

        // Người dùng thường chỉ lấy đề xuất thu thuộc dự án có quyền truy cập
        $pendingReceiptCount = ReceiptVoucher::whereNull('deleted_at')
            ->where('status', 'unpaid')
            ->whereIn('project_id', $accessibleProjectIds)
            ->count();
        $pendingReceiptAmount = ReceiptVoucher::whereNull('deleted_at')
            ->where('status', 'unpaid')
            ->whereIn('project_id', $accessibleProjectIds)
            ->sum('amount');


        // Đề xuất chi (phiếu chi có trạng thái approved)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $pendingPaymentCount = PaymentVoucher::whereNull('deleted_at')
            ->where('status', 'proposed')
            ->whereIn('project_id', $accessibleProjectIds)
            ->count();
        $pendingPaymentAmount = PaymentVoucher::whereNull('deleted_at')
            ->where('status', 'proposed')
            ->whereIn('project_id', $accessibleProjectIds)
            ->sum('amount');
        // Lấy tổng tiền chi theo nhà thầu
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $paymentsByContractor = PaymentVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('contractor_id, SUM(amount) as total_amount')
            ->with('contractor')
            ->groupBy('contractor_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền thu theo khách hàng
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $receiptsByCustomer = ReceiptVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('customer_id, SUM(amount) as total_amount')
            ->with('customer')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        // Lấy tổng tiền chi theo tháng trong năm hiện tại, phân tách theo trạng thái
        $currentYearForPayments = Carbon::now()->year;
        $currentYearForReceipts = Carbon::now()->year;
        $paymentsByMonth = [];

        // Lấy dữ liệu phiếu chi theo tháng và trạng thái
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
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
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('MONTH(created_at) as month, status, SUM(amount) as total_amount')
            ->whereYear('created_at', $currentYearForReceipts)
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
        // Lấy danh sách các dự án mà người dùng có quyền truy cập
        $accessibleProjectIds = ProjectPermission::getAccessibleProjectIds();

        $result = [
            'labels' => [],
            'receipts' => [
                'paid' => [],
                'unpaid' => []
            ],
            'payments' => [
                'paid' => [],
                'unpaid' => []
            ],
            'cashflow' => [
                'expected' => [], // Dự thu chi
                'actual' => [],   // Thu chi thực
                'flow' => []      // Dòng tiền
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
            $result['cashflow']['expected'][$day] = 0;
            $result['cashflow']['actual'][$day] = 0;
            $result['cashflow']['flow'][$day] = 0;
        }

        // Lấy dữ liệu phiếu thu theo ngày và trạng thái (sử dụng payment_date thay vì created_at)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('DAY(payment_date) as day, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
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

        // Lấy dữ liệu phiếu chi theo ngày và trạng thái (sử dụng payment_date thay vì created_at)
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('DAY(payment_date) as day, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', $year)
            ->whereMonth('payment_date', $month)
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

        // Lấy dữ liệu khoản vay theo ngày bắt đầu
        $loanData = Loan::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('DAY(start_date) as day, SUM(amount) as total_amount')
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        // Mảng để lưu trữ khoản vay theo ngày
        $loans = array_fill(1, $daysInMonth, 0);

        // Cập nhật dữ liệu khoản vay
        foreach ($loanData as $loan) {
            $day = $loan->day;
            $loans[$day] = (float) $loan->total_amount;
        }

        // Tính toán dữ liệu cho 3 đường biểu đồ mới
        for ($day = 1; $day <= $daysInMonth; $day++) {
            // 1. Dự thu chi = tổng dự thu - tổng dự chi
            $result['cashflow']['expected'][$day] = $result['receipts']['unpaid'][$day] - $result['payments']['unpaid'][$day];

            // 2. Thu chi thực = đã thu + khoản vay - đã chi
            $result['cashflow']['actual'][$day] = $result['receipts']['paid'][$day] + $loans[$day] - $result['payments']['paid'][$day];

            // 3. Dòng tiền = tổng thu - khoản vay - tổng chi
            $result['cashflow']['flow'][$day] = $result['receipts']['paid'][$day] - $loans[$day] - $result['payments']['paid'][$day];
        }

        // Thêm thông tin về ngày hiện tại
        $currentDay = Carbon::now()->day;
        if ($year == Carbon::now()->year && $month == Carbon::now()->month && $currentDay <= $daysInMonth) {
            $result['currentDay'] = $currentDay;
        } else {
            $result['currentDay'] = null;
        }

        // Chuyển dữ liệu từ dạng associative array sang dạng indexed array
        $result['receipts']['paid'] = array_values($result['receipts']['paid']);
        $result['receipts']['unpaid'] = array_values($result['receipts']['unpaid']);
        $result['payments']['paid'] = array_values($result['payments']['paid']);
        $result['payments']['unpaid'] = array_values($result['payments']['unpaid']);
        $result['cashflow']['expected'] = array_values($result['cashflow']['expected']);
        $result['cashflow']['actual'] = array_values($result['cashflow']['actual']);
        $result['cashflow']['flow'] = array_values($result['cashflow']['flow']);

        return $result;
    }

    /**
     * Lấy dữ liệu phiếu thu và phiếu chi theo tháng trong năm
     */
    private function getMonthlyVoucherData($year)
    {
        // Lấy danh sách các dự án mà người dùng có quyền truy cập
        $accessibleProjectIds = ProjectPermission::getAccessibleProjectIds();

        $result = [
            'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'],
            'receipts' => [
                'paid' => array_fill(0, 12, 0),
                'unpaid' => array_fill(0, 12, 0)
            ],
            'payments' => [
                'paid' => array_fill(0, 12, 0),
                'unpaid' => array_fill(0, 12, 0)
            ],
            'cashflow' => [
                'expected' => array_fill(0, 12, 0), // Dự thu chi
                'actual' => array_fill(0, 12, 0),   // Thu chi thực
                'flow' => array_fill(0, 12, 0)      // Dòng tiền
            ]
        ];

        // Lấy dữ liệu phiếu thu theo tháng và trạng thái (sử dụng payment_date thay vì created_at)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('MONTH(payment_date) as month, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', $year)
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

        // Lấy dữ liệu phiếu chi theo tháng và trạng thái (sử dụng payment_date thay vì created_at)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('MONTH(payment_date) as month, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', $year)
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

        // Lấy dữ liệu khoản vay theo tháng bắt đầu
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $loanData = Loan::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('MONTH(start_date) as month, SUM(amount) as total_amount')
            ->whereYear('start_date', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Mảng để lưu trữ khoản vay theo tháng
        $loans = array_fill(0, 12, 0);

        // Cập nhật dữ liệu khoản vay
        foreach ($loanData as $loan) {
            $monthIndex = $loan->month - 1;
            $loans[$monthIndex] = (float) $loan->total_amount;
        }

        // Tính toán dữ liệu cho 3 đường biểu đồ mới
        for ($i = 0; $i < 12; $i++) {
            // 1. Dự thu chi = tổng dự thu - tổng dự chi
            $result['cashflow']['expected'][$i] = $result['receipts']['unpaid'][$i] - $result['payments']['unpaid'][$i];

            // 2. Thu chi thực = đã thu + khoản vay - đã chi
            $result['cashflow']['actual'][$i] = $result['receipts']['paid'][$i] + $loans[$i] - $result['payments']['paid'][$i];

            // 3. Dòng tiền = tổng thu - khoản vay - tổng chi
            $result['cashflow']['flow'][$i] = $result['receipts']['paid'][$i] - $loans[$i] - $result['payments']['paid'][$i];
        }

        // Thêm thông tin về tháng hiện tại
        $currentMonth = Carbon::now()->month;
        if ($year == Carbon::now()->year) {
            $result['currentMonth'] = $currentMonth;
        } else {
            $result['currentMonth'] = null;
        }

        return $result;
    }

    /**
     * Lấy dữ liệu phiếu thu và phiếu chi theo năm
     */
    private function getYearlyVoucherData($numberOfYears)
    {
        // Lấy danh sách các dự án mà người dùng có quyền truy cập
        $accessibleProjectIds = ProjectPermission::getAccessibleProjectIds();

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
            ],
            'cashflow' => [
                'expected' => array_fill(0, $numberOfYears, 0), // Dự thu chi
                'actual' => array_fill(0, $numberOfYears, 0),   // Thu chi thực
                'flow' => array_fill(0, $numberOfYears, 0)      // Dòng tiền
            ]
        ];

        // Lấy dữ liệu phiếu thu theo năm và trạng thái (sử dụng payment_date thay vì created_at)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $receiptData = ReceiptVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('YEAR(payment_date) as year, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', '>=', $startYear)
            ->whereYear('payment_date', '<=', $currentYear)
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

        // Lấy dữ liệu phiếu chi theo năm và trạng thái (sử dụng payment_date thay vì created_at)
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $paymentData = PaymentVoucher::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('YEAR(payment_date) as year, status, SUM(amount) as total_amount')
            ->whereYear('payment_date', '>=', $startYear)
            ->whereYear('payment_date', '<=', $currentYear)
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

        // Lấy dữ liệu khoản vay theo năm bắt đầu
        // Áp dụng phân quyền theo dự án cho tất cả người dùng
        $loanData = Loan::whereNull('deleted_at')
            ->whereIn('project_id', $accessibleProjectIds)
            ->selectRaw('YEAR(start_date) as year, SUM(amount) as total_amount')
            ->whereYear('start_date', '>=', $startYear)
            ->whereYear('start_date', '<=', $currentYear)
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        // Mảng để lưu trữ khoản vay theo năm
        $loans = array_fill(0, $numberOfYears, 0);

        // Cập nhật dữ liệu khoản vay
        foreach ($loanData as $loan) {
            $yearIndex = $loan->year - $startYear;
            $loans[$yearIndex] = (float) $loan->total_amount;
        }

        // Tính toán dữ liệu cho 3 đường biểu đồ mới
        for ($i = 0; $i < $numberOfYears; $i++) {
            // 1. Dự thu chi = tổng dự thu - tổng dự chi
            $result['cashflow']['expected'][$i] = $result['receipts']['unpaid'][$i] - $result['payments']['unpaid'][$i];

            // 2. Thu chi thực = đã thu + khoản vay - đã chi
            $result['cashflow']['actual'][$i] = $result['receipts']['paid'][$i] + $loans[$i] - $result['payments']['paid'][$i];

            // 3. Dòng tiền = tổng thu - khoản vay - tổng chi
            $result['cashflow']['flow'][$i] = $result['receipts']['paid'][$i] - $loans[$i] - $result['payments']['paid'][$i];
        }

        // Thêm thông tin về năm hiện tại
        $result['currentYear'] = $currentYear;
        $result['currentYearIndex'] = $numberOfYears - 1; // Vị trí của năm hiện tại trong mảng (luôn là năm cuối cùng)

        return $result;
    }
}
