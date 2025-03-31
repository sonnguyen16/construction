<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\Contractor;
use App\Models\BidPackage;
use App\Models\Project;
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
            ->with(['contractor', 'bidPackage.project', 'creator']);

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
        }

        // Lọc theo nhà thầu
        if ($request->has('contractor_id') && $request->contractor_id) {
            $query->where('contractor_id', $request->contractor_id);
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

        $paymentVouchers = $query->paginate(10)->withQueryString();

        // Lấy danh sách nhà thầu và gói thầu cho bộ lọc
        $contractors = Contractor::orderBy('name')->get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get()
            ->map(function ($bidPackage) {
                return [
                    'id' => $bidPackage->id,
                    'code' => $bidPackage->code,
                    'name' => $bidPackage->name,
                    'project_name' => $bidPackage->project->name ?? 'Không có dự án'
                ];
            });

        return Inertia::render('PaymentVouchers/Index', [
            'paymentVouchers' => $paymentVouchers,
            'contractors' => $contractors,
            'bidPackages' => $bidPackages,
            'statuses' => $this->getStatuses(),
            'filters' => $request->only(['search', 'contractor_id', 'bid_package_id', 'status', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Hiển thị form tạo phiếu chi mới
     */
    public function create()
    {
        $contractors = Contractor::select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();

        $projects = Project::select('id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $bidPackages = BidPackage::select('id', 'project_id', 'name', 'code')
            ->with('project:id,name')
            ->get()
            ->map(function ($bidPackage) {
                $bidPackage->display_name = "{$bidPackage->code} - {$bidPackage->name}";
                return $bidPackage;
            });

        return Inertia::render('PaymentVouchers/Create', [
            'contractors' => $contractors,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'statuses' => $this->getStatuses(),
            'preselectedContractorId' => request('contractor_id'),
            'preselectedProjectId' => request('project_id'),
            'preselectedBidPackageId' => request('bid_package_id')
        ]);
    }

    /**
     * Lưu phiếu chi mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'project_id' => 'required|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'completed' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $paymentVoucher = PaymentVoucher::create($validated);

        return redirect()->route('projects.expenses', $paymentVoucher->project_id)
            ->with('success', 'Phiếu chi đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết phiếu chi
     */
    public function show(PaymentVoucher $paymentVoucher)
    {
        $paymentVoucher->load(['contractor', 'project', 'bidPackage', 'creator', 'updater']);

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
        $paymentVoucher->load(['contractor', 'bidPackage', 'creator', 'updater']);

        $contractors = Contractor::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $bidPackages = BidPackage::with('project')->orderBy('created_at', 'desc')->get()
            ->map(function ($bidPackage) {
                return [
                    'id' => $bidPackage->id,
                    'name' => $bidPackage->name,
                    'code' => $bidPackage->code,
                    'project_id' => $bidPackage->project_id,
                    'project_name' => $bidPackage->project->name,
                    'display_name' => "[{$bidPackage->code}] {$bidPackage->name} - {$bidPackage->project->name}"
                ];
            });

        return Inertia::render('PaymentVouchers/Edit', [
            'paymentVoucher' => $paymentVoucher,
            'contractors' => $contractors,
            'projects' => $projects,
            'bidPackages' => $bidPackages,
            'statuses' => $this->getStatuses()
        ]);
    }

    /**
     * Cập nhật thông tin phiếu chi
     */
    public function update(Request $request, PaymentVoucher $paymentVoucher)
    {
        $validated = $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'project_id' => 'required|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required',
            'payment_date' => 'nullable|date'
        ]);

        // Kiểm tra logic payment_date
        if ($validated['status'] === 'paid' && empty($validated['payment_date'])) {
            $validated['payment_date'] = now()->toDateString();
        }

        $paymentVoucher->update($validated);

        return redirect()->route('projects.expenses', $paymentVoucher->project_id)
            ->with('success', 'Phiếu chi đã được cập nhật thành công.');
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
            'paid' => 'Đã chi'
        ];
    }
}