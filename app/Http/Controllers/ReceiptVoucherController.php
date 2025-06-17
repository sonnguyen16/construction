<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVoucher;
use App\Models\Customer;
use App\Models\Project;
use App\Models\BidPackage;
use App\Models\ReceiptCategory;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReceiptVoucherController extends Controller
{
    /**
     * Hiển thị danh sách phiếu thu
     */
    public function index(Request $request)
    {
        $query = ReceiptVoucher::query()->whereNull('deleted_at')
            ->with(['customer', 'project', 'bidPackage', 'creator', 'receiptCategory'])
            ->whereHas('bidPackage', function ($query) {
                $query->whereNull('deleted_at');
            });

        // Tạo một truy vấn cơ bản cho thống kê
        $statsQuery = ReceiptVoucher::query()->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('customer', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });

            // Áp dụng filter tương tự cho statsQuery
            $statsQuery->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('customer', function($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Lọc theo khách hàng
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
            $statsQuery->where('customer_id', $request->customer_id);
        }

        // Lọc theo dự án
        if ($request->has('project_id')) {
            if ($request->project_id === 'null') {
                // Lọc các phiếu thu ngoài dự án (project_id = null)
                $query->whereNull('project_id');
                $statsQuery->whereNull('project_id');
            } else if ($request->project_id) {
                // Lọc theo dự án cụ thể
                $query->where('project_id', $request->project_id);
                $statsQuery->where('project_id', $request->project_id);
            }
        }

        if ($request->has('receipt_category_id') && $request->receipt_category_id) {
            $query->where('receipt_category_id', $request->receipt_category_id);
            $statsQuery->where('receipt_category_id', $request->receipt_category_id);
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
            $statsQuery->where('status', $request->status);
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

        // Lấy danh sách các dự án mà người dùng có quyền xem phiếu thu
        $projectIds = ProjectPermission::getProjectsWithPermission('receipt-vouchers.view');

        // Áp dụng phân quyền theo dự án (phải để cuối cùng để tránh bypass)
        $query->where(function($q) use ($projectIds) {
            $q->whereIn('project_id', $projectIds)
              ->orWhereNull('project_id'); // Cho phép xem phiếu thu không thuộc dự án nào
        });

        $statsQuery->where(function($q) use ($projectIds) {
            $q->whereIn('project_id', $projectIds)
              ->orWhereNull('project_id');
        });

        // Tính toán thống kê dựa trên các filter đã áp dụng
        $totalReceiptCount = $statsQuery->count();

        $totalReceiptAmount = clone $statsQuery;
        $totalReceiptAmount = $totalReceiptAmount->where('status', 'paid')->sum('amount');

        $totalReceiptAmountUnpaid = clone $statsQuery;
        $totalReceiptAmountUnpaid = $totalReceiptAmountUnpaid->where('status', 'unpaid')->sum('amount');

        // Sắp xếp theo ngày tạo mới nhất
        $query->orderBy('created_at', 'desc');

        $receiptVouchers = $query->paginate(10)->withQueryString();

        // Lấy danh sách khách hàng, dự án và gói thầu cho bộ lọc
        $customers = Customer::orderBy('name')->whereNull('deleted_at')->get();
        $projects = Project::orderBy('name')->whereNull('deleted_at')->whereIn('id', $projectIds)->get();
        $receiptCategories = ReceiptCategory::orderBy('name')->get();

        return Inertia::render('ReceiptVouchers/Index', [
            'receiptVouchers' => $receiptVouchers,
            'customers' => $customers,
            'projects' => $projects,
            'receiptCategories' => $receiptCategories,
            'statuses' => $this->getStatuses(),
            'filters' => $request->only(['search', 'customer_id', 'project_id', 'receipt_category_id', 'status', 'date_from', 'date_to']),
            'totalReceiptCount' => $totalReceiptCount,
            'totalReceiptAmount' => $totalReceiptAmount,
            'totalReceiptAmountUnpaid' => $totalReceiptAmountUnpaid,
        ]);
    }

    /**
     * Hiển thị form tạo phiếu thu mới
     */
    public function create(Request $request)
    {
        // Kiểm tra quyền tạo phiếu thu
        // Sẽ kiểm tra theo dự án sau khi người dùng chọn dự án trong form

        $customers = Customer::orderBy('name')->get();

        // Chỉ hiển thị các dự án mà người dùng có quyền tạo phiếu thu
        $projectIds = ProjectPermission::getProjectsWithPermission('receipt-vouchers.create');
        $projects = Project::orderBy('name')->whereIn('id', $projectIds)->get();

        $receiptCategories = ReceiptCategory::orderBy('name')->get();

        // Lấy thông tin từ request nếu có
        $preselectedCustomerId = $request->input('customer_id');
        $preselectedProjectId = $request->input('project_id');

        return Inertia::render('ReceiptVouchers/Create', [
            'customers' => $customers,
            'projects' => $projects,
            'receiptCategories' => $receiptCategories,
            'statuses' => $this->getStatuses(),
            'preselectedCustomerId' => $preselectedCustomerId,
            'preselectedProjectId' => $preselectedProjectId,
        ]);
    }

    /**
     * Lưu phiếu thu mới
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền tạo phiếu thu theo dự án
        if ($request->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.create', $request->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo phiếu thu cho dự án này!');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'receipt_category_id' => 'nullable|exists:receipt_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:paid,unpaid',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'completed' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $receiptVoucher = ReceiptVoucher::create($validated);

        return redirect()->route('receipt-vouchers.index')
            ->with('success', 'Phiếu thu đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết phiếu thu
     */
    public function show(ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền xem phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.view', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền xem phiếu thu này!');
        }

        $receiptVoucher->load(['customer', 'project', 'bidPackage', 'creator', 'updater']);

        return Inertia::render('ReceiptVouchers/Show', [
            'receiptVoucher' => $receiptVoucher,
            'statuses' => $this->getStatuses()
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa phiếu thu
     */
    public function edit(ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền sửa phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.edit', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền sửa phiếu thu này!');
        }

        $receiptVoucher->load(['customer', 'project', 'bidPackage', 'creator', 'updater', 'receiptCategory']);

        $customers = Customer::orderBy('name')->get();

        // Chỉ hiển thị các dự án mà người dùng có quyền sửa phiếu thu
        $projectIds = ProjectPermission::getProjectsWithPermission('receipt-vouchers.edit');
        $projects = Project::orderBy('name')->whereIn('id', $projectIds)->get();

        $receiptCategories = ReceiptCategory::orderBy('name')->get();

        return Inertia::render('ReceiptVouchers/Edit', [
            'receiptVoucher' => $receiptVoucher,
            'customers' => $customers,
            'projects' => $projects,
            'receiptCategories' => $receiptCategories,
            'statuses' => $this->getStatuses()
        ]);
    }

    /**
     * Cập nhật thông tin phiếu thu
     */
    public function update(Request $request, ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền sửa phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.edit', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền sửa phiếu thu này!');
        }

        // Kiểm tra nếu người dùng đang cố gắng chuyển phiếu thu sang dự án khác mà họ không có quyền
        if ($request->project_id && $request->project_id != $receiptVoucher->project_id &&
            !ProjectPermission::hasPermissionInProject('receipt-vouchers.edit', $request->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền chuyển phiếu thu sang dự án này!');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'receipt_category_id' => 'nullable|exists:receipt_categories,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:paid,unpaid',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'completed' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $receiptVoucher->update($validated);

        return redirect()->route('receipt-vouchers.index')
            ->with('success', 'Phiếu thu đã được cập nhật thành công.');
    }

    /**
     * Xóa phiếu thu
     */
    public function destroy(ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền xóa phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.delete', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền xóa phiếu thu này!');
        }

        $receiptVoucher->deleted_at = now();
        $receiptVoucher->save();

        return redirect()->route('receipt-vouchers.index')
            ->with('success', 'Phiếu thu đã được xóa thành công.');
    }

    /**
     * Cập nhật trạng thái phiếu thu
     */
    public function updateStatus(Request $request, ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền cập nhật trạng thái phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.update-status', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền cập nhật trạng thái phiếu thu này!');
        }

        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'paid' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $receiptVoucher->update($validated);

        return back()->with('success', 'Trạng thái phiếu thu đã được cập nhật thành công.');
    }

    function getStatuses()
    {
        return [
            'unpaid' => 'Dự thu',
            'paid' => 'Đã thu'
        ];
    }

    /**
     * In phiếu thu
     */
    public function print(ReceiptVoucher $receiptVoucher)
    {
        // Kiểm tra quyền in phiếu thu theo dự án
        if ($receiptVoucher->project_id && !ProjectPermission::hasPermissionInProject('receipt-vouchers.print', $receiptVoucher->project_id)) {
            return redirect()->route('receipt-vouchers.index')
                ->with('error', 'Bạn không có quyền in phiếu thu này!');
        }

        $receiptVoucher->load(['customer', 'project', 'bidPackage', 'creator']);

        // Chuyển đổi số tiền thành chữ
        $amountInWords = $this->convertNumberToWords($receiptVoucher->amount);

        // Tạo view cho phiếu thu
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.receipt-voucher', [
            'receiptVoucher' => $receiptVoucher,
            'amountInWords' => $amountInWords,
            'company' => [
                'name' => 'CÔNG TY TNHH HOÀNG TÂM CONSULTANTS',
                'address' => '152/4 Ba Cu, Phường 3, Thành phố Vũng Tàu, Tỉnh Bà Rịa - Vũng Tàu, Việt Nam'
            ]
        ]);

        return $pdf->stream("phieu-thu-{$receiptVoucher->code}.pdf");
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
