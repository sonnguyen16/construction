<?php

namespace App\Http\Controllers;

use App\Models\ReceiptVoucher;
use App\Models\Customer;
use App\Models\Project;
use App\Models\BidPackage;
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
            ->with(['customer', 'project', 'bidPackage', 'creator']);

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
        }

        // Lọc theo khách hàng
        if ($request->has('customer_id') && $request->customer_id) {
            $query->where('customer_id', $request->customer_id);
        }

        // Lọc theo dự án
        if ($request->has('project_id') && $request->project_id) {
            $query->where('project_id', $request->project_id);
        }

        // Lọc theo gói thầu
        if ($request->has('bid_package_id') && $request->bid_package_id) {
            $query->where('bid_package_id', $request->bid_package_id);
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Lọc theo khoảng thời gian
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sắp xếp theo ngày tạo mới nhất
        $query->orderBy('created_at', 'desc');

        $receiptVouchers = $query->paginate(10)->withQueryString();

        // Lấy danh sách khách hàng, dự án và gói thầu cho bộ lọc
        $customers = Customer::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get()
            ->map(function ($bidPackage) {
                return [
                    'id' => $bidPackage->id,
                    'code' => $bidPackage->code,
                    'name' => $bidPackage->name,
                    'project_name' => $bidPackage->project->name ?? 'Không có dự án'
                ];
            });

        return Inertia::render('ReceiptVouchers/Index', [
            'receiptVouchers' => $receiptVouchers,
            'customers' => $customers,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'statuses' => [
                'unpaid' => 'Chưa thanh toán',
                'paid' => 'Đã thanh toán'
            ],
            'filters' => $request->only(['search', 'customer_id', 'project_id', 'bid_package_id', 'status', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Hiển thị form tạo phiếu thu mới
     */
    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get()
            ->map(function ($bidPackage) {
                return [
                    'id' => $bidPackage->id,
                    'name' => $bidPackage->name,
                    'code' => $bidPackage->code,
                    'project_name' => $bidPackage->project->name,
                    'display_name' => "[{$bidPackage->code}] {$bidPackage->name} - {$bidPackage->project->name}"
                ];
            });

        // Lấy thông tin từ request nếu có
        $preselectedCustomerId = $request->input('customer_id');
        $preselectedProjectId = $request->input('project_id');
        $preselectedBidPackageId = $request->input('bid_package_id');

        return Inertia::render('ReceiptVouchers/Create', [
            'customers' => $customers,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'statuses' => [
                'unpaid' => 'Chưa thanh toán',
                'paid' => 'Đã thanh toán'
            ],
            'preselectedCustomerId' => $preselectedCustomerId,
            'preselectedProjectId' => $preselectedProjectId,
            'preselectedBidPackageId' => $preselectedBidPackageId
        ]);
    }

    /**
     * Lưu phiếu thu mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
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
        $receiptVoucher->load(['customer', 'project', 'bidPackage', 'creator', 'updater']);

        return Inertia::render('ReceiptVouchers/Show', [
            'receiptVoucher' => $receiptVoucher,
            'statuses' => [
                'unpaid' => 'Chưa thanh toán',
                'paid' => 'Đã thanh toán'
            ]
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa phiếu thu
     */
    public function edit(ReceiptVoucher $receiptVoucher)
    {
        $receiptVoucher->load(['customer', 'project', 'bidPackage', 'creator', 'updater']);

        $customers = Customer::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get()
            ->map(function ($bidPackage) {
                return [
                    'id' => $bidPackage->id,
                    'name' => $bidPackage->name,
                    'code' => $bidPackage->code,
                    'project_name' => $bidPackage->project->name,
                    'display_name' => "[{$bidPackage->code}] {$bidPackage->name} - {$bidPackage->project->name}"
                ];
            });

        return Inertia::render('ReceiptVouchers/Edit', [
            'receiptVoucher' => $receiptVoucher,
            'customers' => $customers,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'statuses' => [
                'unpaid' => 'Chưa thanh toán',
                'paid' => 'Đã thanh toán'
            ]
        ]);
    }

    /**
     * Cập nhật thông tin phiếu thu
     */
    public function update(Request $request, ReceiptVoucher $receiptVoucher)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'project_id' => 'nullable|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
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
}
