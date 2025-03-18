<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\BidPackage;

class ProjectController extends Controller
{
    /**
     * Hiển thị danh sách dự án
     */
    public function index(Request $request)
    {
        $query = Project::query();

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

        $projects = $query->latest()->paginate(10)->withQueryString();

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
        return Inertia::render('Projects/Create');
    }

    /**
     * Lưu dự án mới vào database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:projects',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết dự án
     */
    public function show(Project $project)
    {
        $project->load([
            'bidPackages.bids.contractor',
            'bidPackages.selectedContractor',
            'bidPackages.payment_vouchers.contractor',
            'bidPackages.receipt_vouchers.customer',
        ]);

        // Tính toán profit cho mỗi gói thầu
        foreach ($project->bidPackages as $bidPackage) {
            if ($bidPackage->client_price && $bidPackage->estimated_price) {
                $bidPackage->profit = $bidPackage->client_price - $bidPackage->estimated_price;
            } else {
                $bidPackage->profit = null;
            }
        }

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'bidPackageStatuses' => BidPackage::STATUSES
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
        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    /**
     * Cập nhật thông tin dự án
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:projects,code,' . $project->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,cancelled',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Thông tin dự án đã được cập nhật.');
    }

    /**
     * Xóa dự án
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Dự án đã được xóa thành công.');
    }
}
