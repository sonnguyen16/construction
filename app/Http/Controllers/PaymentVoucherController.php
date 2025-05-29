<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\Contractor;
use App\Models\BidPackage;
use App\Models\Project;
use App\Models\PaymentCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentVoucherController extends Controller
{
    /**
     * Hiển thị danh sách phiếu chi
     */
    public function index(Request $request)
    {
        $query = PaymentVoucher::query()->whereNull('deleted_at')
            ->with(['contractor', 'bidPackage.project', 'creator', 'paymentCategory']);

        // Tạo một truy vấn cơ bản cho thống kê
        $statsQuery = PaymentVoucher::query()->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('contractor', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });

            // Áp dụng filter tương tự cho statsQuery
            $statsQuery->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('contractor', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Lọc theo nhà thầu
        if ($request->has('contractor_id') && $request->contractor_id) {
            $query->where('contractor_id', $request->contractor_id);
            $statsQuery->where('contractor_id', $request->contractor_id);
        }

        // Lọc theo dự án
        if ($request->has('project_id')) {
            if ($request->project_id === 'null') {
                // Lọc các phiếu chi ngoài dự án (project_id = null)
                $query->whereNull('project_id');
                $statsQuery->whereNull('project_id');
            } else if ($request->project_id) {
                // Lọc theo dự án cụ thể
                $query->where('project_id', $request->project_id);
                $statsQuery->where('project_id', $request->project_id);
            }
        }

        // Lọc theo gói thầu
        if ($request->has('bid_package_id')) {
            if ($request->bid_package_id === 'null') {
                // Lọc các phiếu chi không thuộc gói thầu nào (bid_package_id = null)
                $query->whereNull('bid_package_id');
                $statsQuery->whereNull('bid_package_id');
            } else if ($request->bid_package_id) {
                // Lọc theo gói thầu cụ thể
                $query->where('bid_package_id', $request->bid_package_id);
                $statsQuery->where('bid_package_id', $request->bid_package_id);
            }
        }

        if ($request->has('payment_category_id') && $request->payment_category_id) {
            $query->where('payment_category_id', $request->payment_category_id);
            $statsQuery->where('payment_category_id', $request->payment_category_id);
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            if($request->status === 'unpaid') {
                $query->whereIn('status', ['proposed', 'approved']);
                $statsQuery->whereIn('status', ['proposed', 'approved']);
            } else {
                $query->where('status', $request->status);
                $statsQuery->where('status', $request->status);
            }
        }

        // Lọc theo khoảng thời gian
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('payment_date', '>=', $request->date_from);
            $statsQuery->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('payment_date', '<=', $request->date_to);
            $statsQuery->whereDate('payment_date', '<=', $request->date_to);
        }

        // Tính toán thống kê dựa trên các filter đã áp dụng
        $totalPaymentCount = $statsQuery->count();
        $totalPaymentAmount = clone $statsQuery;
        $totalPaymentAmount = $totalPaymentAmount->where('status', 'paid')->sum('amount');

        $totalPaymentAmountProposed = clone $statsQuery;
        $totalPaymentAmountProposed = $totalPaymentAmountProposed->where('status', 'proposed')->sum('amount');

        $totalPaymentAmountApproved = clone $statsQuery;
        $totalPaymentAmountApproved = $totalPaymentAmountApproved->where('status', 'approved')->sum('amount');

        // Sắp xếp theo ngày tạo mới nhất
        $query->orderBy('created_at', 'desc');

        $paymentVouchers = $query->paginate(10)->withQueryString();

        // Lấy danh sách nhà thầu và gói thầu cho bộ lọc
        $contractors = Contractor::orderBy('name')->whereNull('deleted_at')->get();
        $projects = Project::orderBy('name')->whereNull('deleted_at')->get();
        $bidPackages = BidPackage::orderBy('created_at', 'desc')->whereNull('deleted_at')->get();
        $paymentCategories = PaymentCategory::orderBy('name')->get();

        return Inertia::render('PaymentVouchers/Index', [
            'paymentVouchers' => $paymentVouchers,
            'contractors' => $contractors,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'paymentCategories' => $paymentCategories,
            'statuses' => $this->getStatuses(),
            'filters' => $request->only(['search', 'contractor_id', 'project_id', 'bid_package_id', 'payment_category_id', 'status', 'date_from', 'date_to']),
            'totalPaymentCount' => $totalPaymentCount,
            'totalPaymentAmount' => $totalPaymentAmount,
            'totalPaymentAmountProposed' => $totalPaymentAmountProposed,
            'totalPaymentAmountApproved' => $totalPaymentAmountApproved
        ]);
    }

    /**
     * Hiển thị form tạo phiếu chi mới
     */
    public function create()
    {
        $contractors = Contractor::select('id', 'name', 'phone')
            ->orderBy('name')
            ->whereNull('deleted_at')
            ->get();

        $projects = Project::select('id', 'name', 'code')
            ->orderBy('name')
            ->whereNull('deleted_at')
            ->get();

        $bidPackages = BidPackage::select('id', 'project_id', 'name', 'code')
            ->with('project:id,name')
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($bidPackage) {
                $bidPackage->display_name = "{$bidPackage->code} - {$bidPackage->name}";
                return $bidPackage;
            });

        $paymentCategories = PaymentCategory::orderBy('name')->get();

        return Inertia::render('PaymentVouchers/Create', [
            'contractors' => $contractors,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'paymentCategories' => $paymentCategories,
            'statuses' => $this->getStatuses(),
            'preselectedContractorId' => request('contractor_id'),
            'preselectedProjectId' => request('project_id'),
            'preselectedBidPackageId' => request('bid_package_id'),
            'redirectToExpenses' => request('redirect_to_expenses') === 'true'
        ]);
    }

    /**
     * Lưu phiếu chi mới
     */
    public function store(Request $request)
    {
        $rules = [
            'contractor_id' => 'required|exists:contractors,id',
            'project_id' => 'nullable|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required',
            'payment_date' => 'nullable|date'
        ];

        // Nếu đã chọn project thì phải chọn bid_package
        if ($request->filled('project_id')) {
            $rules['bid_package_id'] = 'required|exists:bid_packages,id';
        }

        $validated = $request->validate($rules);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'completed' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $paymentVoucher = PaymentVoucher::create($validated);
        $successMessage = 'Phiếu chi đã được tạo thành công.';

        // Kiểm tra tham số redirect_to_expenses được gửi lên từ form
        if ($request->has('redirect_to_expenses') && $request->redirect_to_expenses && $paymentVoucher->project_id) {
            // Nếu được tạo từ trang expenses của project, quay về trang đó
            return redirect()->route('projects.expenses', $paymentVoucher->project_id)
                ->with('success', $successMessage);
        } else {
            // Ngược lại, quay về trang danh sách phiếu chi
            return redirect()->route('payment-vouchers.index')
                ->with('success', $successMessage);
        }
    }

    /**
     * Hiển thị chi tiết phiếu chi
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['contractor', 'project', 'bidPackage', 'creator', 'updater', 'paymentCategory']);

        return Inertia::render('PaymentVouchers/Show', [
            'paymentVoucher' => $paymentVoucher,
            'statuses' => $this->getStatuses()
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa phiếu chi
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['contractor', 'bidPackage', 'creator', 'updater', 'paymentCategory']);

        $contractors = Contractor::orderBy('name')->whereNull('deleted_at')->get();
        $projects = Project::orderBy('name')->whereNull('deleted_at')-> get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get();
        $paymentCategories = PaymentCategory::orderBy('name')->get();

        return Inertia::render('PaymentVouchers/Edit', [
            'paymentVoucher' => $paymentVoucher,
            'contractors' => $contractors,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'paymentCategories' => $paymentCategories,
            'statuses' => $this->getStatuses()
        ]);
    }

    /**
     * Cập nhật thông tin phiếu chi
     */
    public function update(Request $request, PaymentVoucher $paymentVoucher)
    {
        $rules = [
            'contractor_id' => 'required|exists:contractors,id',
            'project_id' => 'nullable|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'payment_category_id' => 'nullable|exists:payment_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required',
            'payment_date' => 'nullable|date'
        ];

        // Nếu đã chọn project thì phải chọn bid_package
        if ($request->filled('project_id')) {
            $rules['bid_package_id'] = 'required|exists:bid_packages,id';
        }

        $validated = $request->validate($rules);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'paid' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $paymentVoucher->update($validated);
        $successMessage = 'Phiếu chi đã được cập nhật thành công.';

        // Kiểm tra xem phiếu được cập nhật từ trang expenses của project hay không
        $referer = request()->headers->get('referer');
        $isFromExpenses = $paymentVoucher->project_id && strpos($referer, 'projects/' . $paymentVoucher->project_id . '/expenses') !== false;

        if ($isFromExpenses) {
            // Nếu được cập nhật từ trang expenses của project, quay về trang đó
            return redirect()->route('projects.expenses', $paymentVoucher->project_id)
                ->with('success', $successMessage);
        } else {
            // Ngược lại, quay về trang danh sách phiếu chi
            return redirect()->route('payment-vouchers.index')
                ->with('success', $successMessage);
        }
    }

    /**
     * Xóa phiếu chi
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->deleted_at = now();
        $paymentVoucher->save();

        return redirect()->route('payment-vouchers.index')
            ->with('success', 'Phiếu chi đã được xóa thành công.');
    }

    /**
     * Cập nhật trạng thái phiếu chi
     */
    public function updateStatus(Request $request, PaymentVoucher $paymentVoucher)
    {
        $validated = $request->validate([
            'status' => 'required',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'paid' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $paymentVoucher->update($validated);

        return back()->with('success', 'Trạng thái phiếu chi đã được cập nhật thành công.');
    }

    function getStatuses()
    {
        return [
            'proposed' => 'Đề xuất chi',
            'approved' => 'Đã duyệt',
            'paid' => 'Đã chi',
            'unpaid' => 'Chưa thanh toán'
        ];
    }

    /**
     * In phiếu chi
     */
    public function print(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['contractor', 'project', 'bidPackage', 'creator']);

        // Chuyển đổi số tiền thành chữ
        $amountInWords = $this->convertNumberToWords($paymentVoucher->amount);

        // Tạo view cho phiếu chi
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payment-voucher', [
            'paymentVoucher' => $paymentVoucher,
            'amountInWords' => $amountInWords,
            'company' => [
                'name' => 'CÔNG TY TNHH HOÀNG TÂM CONSULTANTS',
                'address' => '152/4 Ba Cu, Phường 3, Thành phố Vũng Tàu, Tỉnh Bà Rịa - Vũng Tàu, Việt Nam'
            ]
        ]);

        return $pdf->stream("phieu-chi-{$paymentVoucher->code}.pdf");
    }

    /**
     * Chuyển đổi số thành chữ tiếng Việt
     */
    private function convertNumberToWords($number)
    {
        $words = [
            0 => 'không',
            1 => 'một',
            2 => 'hai',
            3 => 'ba',
            4 => 'bốn',
            5 => 'năm',
            6 => 'sáu',
            7 => 'bảy',
            8 => 'tám',
            9 => 'chín',
        ];

        $units = ['', 'nghìn', 'triệu', 'tỷ', 'nghìn tỷ', 'triệu tỷ'];

        if ($number == 0) {
            return 'không đồng';
        }

        if ($number < 0) {
            return 'âm ' . $this->convertNumberToWords(abs($number));
        }

        // Làm tròn số, bỏ phần thập phân
        $number = round($number);

        $result = '';

        // Xử lý từng nhóm 3 chữ số
        $groups = [];
        while ($number > 0) {
            $groups[] = $number % 1000;
            $number = floor($number / 1000);
        }

        // Đọc từng nhóm, bắt đầu từ đơn vị lớn nhất
        for ($i = count($groups) - 1; $i >= 0; $i--) {
            $group = $groups[$i];
            if ($group > 0) {
                $result .= $this->readThreeDigits($group, $words) . ' ' . $units[$i];
                if ($i > 0 && $groups[$i-1] > 0) {
                    $result .= ' ';
                }
            }
        }

        $result = trim($result);
        return $result . ' đồng';
    }

    /**
     * Đọc một nhóm 3 chữ số
     */
    private function readThreeDigits($number, $words)
    {
        $number = (int)$number;
        if ($number == 0) return '';

        $hundreds = floor($number / 100);
        $tens = floor(($number % 100) / 10);
        $ones = $number % 10;

        $result = '';

        // Đọc hàng trăm
        if ($hundreds > 0) {
            $result .= $words[$hundreds] . ' trăm';
            if ($tens > 0 || $ones > 0) $result .= ' ';
        }

        // Đọc hàng chục
        if ($tens > 0) {
            if ($tens == 1) {
                $result .= 'mười';
            } else {
                $result .= $words[$tens] . ' mươi';
            }

            if ($ones > 0) {
                if ($tens > 1 && $ones == 1) {
                    $result .= ' mốt';
                } else if ($ones == 5 && $tens >= 1) {
                    $result .= ' lăm';
                } else if ($tens > 1 && $ones == 4) {
                    $result .= ' tư';
                } else {
                    $result .= ' ' . $words[$ones];
                }
            }
        } else if ($ones > 0) {
            // Trường hợp chỉ có hàng đơn vị
            if ($hundreds > 0) {
                $result .= 'lẻ ' . $words[$ones];
            } else {
                $result .= $words[$ones];
            }
        }

        return $result;
    }
}
