<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BidPackage;
use App\Models\Customer;
use App\Models\Contractor;
use App\Models\ProjectCategory;
use Spatie\Permission\Models\Role;
use App\Helpers\ProjectPermission;
use Illuminate\Support\Facades\Auth;
class ProjectController extends Controller
{
    /**
     * Hiển thị danh sách dự án
     */
    public function index(Request $request)
    {
        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        // Các trường cho phép sắp xếp trong cơ sở dữ liệu
        $allowedSortFields = [
            'id', 'code', 'name', 'status', 'customer_id',
            'created_at', 'updated_at', 'total_client_price',
            'total_estimated_price', 'total_additional_price',
        ];

        // Bắt đầu query
        $query = Project::query()
        ->leftJoin('bid_packages', 'projects.id', '=', 'bid_packages.project_id')
        ->select('projects.id', 'projects.code', 'projects.name', 'projects.status', 'projects.customer_id', 'projects.created_at', 'projects.updated_at')
        ->selectRaw('
            COALESCE(SUM(bid_packages.client_price), 0) as total_client_price,
            COALESCE(SUM(bid_packages.estimated_price), 0) as total_estimated_price,
            COALESCE(SUM(bid_packages.additional_price), 0) as total_additional_price
        ')
        ->groupBy([
            'projects.id',
            'projects.code',
            'projects.name',
            'projects.status',
            'projects.customer_id',
            'projects.created_at',
            'projects.updated_at',
        ])
        ->with('customer')
        ->whereNull('projects.deleted_at');


        // Lọc dự án theo quyền truy cập
        $projectIds = ProjectPermission::getAccessibleProjectIds();
        $query->whereIn('projects.id', $projectIds);

        // Tìm kiếm
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('projects.code', 'like', "%{$searchTerm}%")
                ->orWhere('projects.name', 'like', "%{$searchTerm}%");
            });
        }

        // Lọc trạng thái
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('projects.status', $request->status);
        }

        // Sắp xếp nếu hợp lệ
        if (in_array($sortField, $allowedSortFields)) {
            if (in_array($sortField, ['total_client_price', 'total_estimated_price', 'total_additional_price'])) {
                $query->orderByRaw("{$sortField} {$sortDirection}");
            } else {
                $query->orderBy("projects.{$sortField}", $sortDirection);
            }
        } else {
            $query->latest('projects.created_at');
        }


        $projects = $query->paginate(10)->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status', 'sort_field', 'sort_direction']),
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
        if (!ProjectPermission::hasGlobalPermission('projects.create')) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền tạo dự án.');
        }

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
        if (!ProjectPermission::hasGlobalPermission('projects.create')) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền tạo dự án.');
        }

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

        // Tạo dự án mới
        $project = Project::create($validated);

        // Tự động gán vai trò Super Admin cho admin@example.com
        $adminUser = User::where('email', 'admin@example.com')->first();
        $superAdminRole = Role::where('name', 'Super Admin')->first();

        if ($adminUser && $superAdminRole) {
            ProjectRole::create([
                'user_id' => $adminUser->id,
                'project_id' => $project->id,
                'role_id' => $superAdminRole->id
            ]);
        }

        $user = Auth::user();
        if ($user->email !== 'admin@example.com') {
            ProjectRole::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'role_id' => $superAdminRole->id
            ]);
        }

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết dự án
     */
    public function show(Project $project)
    {
        // Kiểm tra người dùng có quyền truy cập dự án không
        if (!ProjectPermission::hasPermissionInProject('projects.commission', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền truy cập dự án này.');
        }

        // Kiểm tra xem dự án có bị xóa không
        if ($project->deleted_at) {
            return redirect()->route('projects.index')
                ->with('error', 'Dự án không tồn tại.');
        }

        // Load tất cả relationships cùng lúc để tránh conflict
        $project->load([
            'bidPackages' => function ($query) {
                $query->with([
                        'bids.contractor',
                        'selectedContractor',
                        'payment_vouchers.contractor',
                        'children' => function ($childQuery) {
                            $childQuery->whereNull('deleted_at')
                                ->with([
                                    'bids.contractor',
                                    'selectedContractor',
                                ]);
                        }
                    ])
                    ->orderBy('order', 'asc');
            },
            'receipt_vouchers.customer',
        ]);

        $contractors = Contractor::whereNull('deleted_at')->get();

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'bidPackageStatuses' => BidPackage::STATUSES,
            'contractors' => $contractors,
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
        if (!ProjectPermission::hasPermissionInProject('projects.edit', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa dự án này.');
        }
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
        if (!ProjectPermission::hasPermissionInProject('projects.edit', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa dự án này.');
        }
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
        if (!ProjectPermission::hasPermissionInProject('projects.delete', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền xóa dự án này.');
        }
        $project->deleted_at = now();
        $project->save();

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được xóa thành công.');
    }

    public function expenses(Project $project)
    {
        if (!ProjectPermission::hasPermissionInProject('projects.expenses', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền xem chi phí dự án này.');
        }
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
        if (!ProjectPermission::hasPermissionInProject('projects.profit', $project->id)) {
            return redirect()->route('projects.index')
                ->with('error', 'Bạn không có quyền xem lợi nhuận dự án này.');
        }
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

    /**
     * Cập nhật phần trăm hoa hồng cho dự án
     */
    public function updateCommission(Request $request, Project $project)
    {
        $validated = $request->validate([
            'commission_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $project->update([
            'commission_percentage' => $validated['commission_percentage']
        ]);

        return redirect()->route('projects.profit', $project->id)
            ->with('success', 'Cập nhật phần trăm hoa hồng thành công');
    }
}
