<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BidPackage;
use App\Models\Customer;
use App\Models\Contractor;
use App\Models\ProjectCategory;
class ProjectController extends Controller
{
    /**
     * Hiển thị danh sách dự án
     */
    public function index(Request $request)
    {
        $query = Project::query()->with('bidPackages')->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('code', 'like', "%{$searchTerm}%")
                  ->orWhere('name', 'like', "%{$searchTerm}%");
            });
        }

        // Lọc theo trạng thái
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $projects = $query->with('customer')->latest()->paginate(10)->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status']),
            'statuses' => [
                'all' => 'Tất cả',
                'active' => 'Đang hoạt động',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
            ],
        ]);
    }

    /**
     * Hiển thị form tạo dự án mới
     */
    public function create()
    {
        $customers = Customer::whereNull('deleted_at')->get();
        $projectCategories = ProjectCategory::orderBy('name')->get();
        return Inertia::render('Projects/Create', [
            'customers' => $customers,
            'projectCategories' => $projectCategories
        ]);
    }

    /**
     * Lưu dự án mới vào database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
            'customer_id' => 'required|exists:customers,id',
            'project_category_id' => 'nullable|exists:project_categories,id',
        ]);

        // Nếu có chọn danh mục dự án, thêm tiền tố vào mã dự án
        if (!empty($validated['project_category_id'])) {
            $category = ProjectCategory::find($validated['project_category_id']);
            if ($category) {
                // Thêm tiền tố vào mã dự án nếu chưa có
                if (!str_starts_with($validated['code'], $category->name . '_')) {
                    $validated['code'] = $category->name . '_' . $validated['code'];
                }
            }
        }

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết dự án
     */
    public function show(Project $project)
    {
        // Kiểm tra xem dự án có bị xóa không
        if ($project->deleted_at) {
            return redirect()->route('projects.index')
                ->with('error', 'Dự án không tồn tại.');
        }

        // Lấy các gói thầu gốc (không phải hạng mục con)
        $bidPackages = $project->bidPackages()
            ->whereNull('deleted_at')
            ->whereNull('parent_id')  // Chỉ lấy gói thầu gốc
            ->with([
                'bids.contractor',
                'selectedContractor',
                'payment_vouchers.contractor',
                'children' => function ($query) {  // Load hạng mục con
                    $query->whereNull('deleted_at')
                        ->with([
                            'bids.contractor',
                            'selectedContractor',
                        ]);
                }
            ])
            ->orderBy('order', 'asc')
            ->get();

        // Đặt các gói thầu đã lấy vào project
        $project->setRelation('bid_packages', $bidPackages);

        // Load các mối quan hệ khác
        $project->load([
            'receipt_vouchers.customer',
        ]);

        $contractors = Contractor::whereNull('deleted_at')->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'bidPackageStatuses' => BidPackage::STATUSES,
            'contractors' => $contractors
        ]);
    }

    public function getBidPackageStatuses()
    {
        return [
            'open' => 'Đang mở',
            'awarded' => 'Đã chọn nhà thầu',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
        ];
    }
    /**
     * Hiển thị form chỉnh sửa dự án
     */
    public function edit(Project $project)
    {
        $customers = Customer::whereNull('deleted_at')->get();
        $projectCategories = ProjectCategory::orderBy('name')->get();
        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'customers' => $customers,
            'projectCategories' => $projectCategories
        ]);
    }

    /**
     * Cập nhật thông tin dự án
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
            'customer_id' => 'required|exists:customers,id',
            'project_category_id' => 'nullable|exists:project_categories,id',
        ]);

        // Nếu có chọn danh mục dự án và khác với danh mục hiện tại
        if (!empty($validated['project_category_id']) && $project->project_category_id != $validated['project_category_id']) {
            $category = ProjectCategory::find($validated['project_category_id']);
            if ($category) {
                // Xóa tiền tố cũ nếu có
                if ($project->projectCategory) {
                    $oldPrefix = $project->projectCategory->name . '_';
                    if (str_starts_with($validated['code'], $oldPrefix)) {
                        $validated['code'] = substr($validated['code'], strlen($oldPrefix));
                    }
                }

                // Thêm tiền tố mới vào mã dự án nếu chưa có
                if (!str_starts_with($validated['code'], $category->name . '_')) {
                    $validated['code'] = $category->name . '_' . $validated['code'];
                }
            }
        }

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Thông tin dự án đã được cập nhật.');
    }

    /**
     * Xóa dự án
     */
    public function destroy(Project $project)
    {
        $project->deleted_at = now();
        $project->save();

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được xóa thành công.');
    }

    public function expenses(Project $project)
    {
        $project->load([
            'bidPackages.payment_vouchers.contractor',
            'bidPackages.payment_vouchers.project',
            'bidPackages.payment_vouchers.bidPackage',
            'bidPackages.selectedContractor',
            'customer'
        ]);

        return Inertia::render('Projects/Expenses', [
            'project' => $project
        ]);
    }

    public function profit(Project $project)
    {
        $project->load([
            'bidPackages.selectedContractor',
            'bidPackages.payment_vouchers',
            'receipt_vouchers',
            'customer'
        ]);

        return Inertia::render('Projects/Profit', [
            'project' => $project
        ]);
    }

    /**
     * API để lấy danh sách gói thầu cho một dự án
     */
    public function getBidPackages(Project $project)
    {
        // Lấy các gói thầu gốc (không phải hạng mục con)
        $bidPackages = $project->bidPackages()
            ->whereNull('deleted_at')
            ->whereNull('parent_id')  // Chỉ lấy gói thầu gốc
            ->with([
                'bids.contractor',
                'selectedContractor',
                'payment_vouchers.contractor',
                'children' => function ($query) {  // Load hạng mục con
                    $query->whereNull('deleted_at')
                        ->with([
                            'bids.contractor',
                            'selectedContractor',
                        ]);
                }
            ])
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'bid_packages' => $bidPackages
        ]);
    }
}
