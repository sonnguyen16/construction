<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Contractor;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Helpers\ProjectPermission;

class LoanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lấy danh sách dự án mà người dùng có quyền xem khoản vay
        $projectIds = ProjectPermission::getProjectsWithPermission('loans.view');

        // Xử lý bộ lọc
        $query = Loan::with(['contractor', 'project', 'creator'])

            ->when($request->input('search'), function($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->input('contractor_id'), function($query, $contractor_id) {
                $query->where('contractor_id', $contractor_id);
            })
            ->when($request->input('project_id'), function($query, $project_id) {
                // Kiểm tra xem người dùng có quyền xem khoản vay trong dự án được chọn không
                if (ProjectPermission::hasPermissionInProject('loans.view', $project_id)) {
                    $query->where('project_id', $project_id);
                }
            })
            ->when($request->input('status'), function($query, $status) {
                $query->where('status', $status);
            })
            ->whereIn('project_id', $projectIds)
            ->orderBy('created_at', 'desc');

        // Phân trang kết quả
        $loans = $query->paginate(10)->withQueryString();

        // Lấy danh sách dự án mà người dùng có quyền xem
        $projects = Project::whereIn('id', $projectIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('Loans/Index', [
            'loans' => $loans,
            'contractors' => Contractor::orderBy('name')->get(),
            'projects' => $projects,
            'statuses' => [
                ['value' => 'active', 'label' => 'Đang vay'],
                ['value' => 'completed', 'label' => 'Đã hoàn thành']
            ],
            'filters' => $request->only(['search', 'contractor_id', 'project_id', 'status']),
            'defaultProject' => $projects->first() ? $projects->first()->id : null
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Lấy danh sách dự án mà người dùng có quyền tạo khoản vay
        $projectIds = ProjectPermission::getProjectsWithPermission('loans.create');
        $contractors = Contractor::orderBy('name')->get();
        $projects = Project::whereIn('id', $projectIds)->orderBy('name')->get();

        return Inertia::render('Loans/Create', [
            'contractors' => $contractors,
            'projects' => $projects,
            'statuses' => [
                ['value' => 'active', 'label' => 'Đang vay'],
                ['value' => 'completed', 'label' => 'Đã hoàn thành']
            ],
            'defaultProject' => $projects->first() ? $projects->first()->id : null
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'contractor_id' => 'required|exists:contractors,id',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,completed',
            'notes' => 'nullable|string',
            'contract_file' => 'nullable|file|max:5120' // Tối đa 5MB
        ]);

        // Kiểm tra quyền tạo khoản vay trong dự án
        if (!ProjectPermission::hasPermissionInProject('loans.create', $validated['project_id'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền tạo khoản vay trong dự án này.');
        }

        // Xử lý file hợp đồng
        if ($request->hasFile('contract_file')) {
            $path = $request->file('contract_file')->store('loan_contracts', 'public');
            $validated['contract_file'] = $path;
        }

        // Thêm thông tin người tạo
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $loan = Loan::create($validated);

        return redirect()->route('loans.index')
            ->with('success', 'Tạo khoản vay thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Loan $loan)
    {
        // Kiểm tra quyền xem khoản vay trong dự án
        if (!ProjectPermission::hasPermissionInProject('loans.view', $loan->project_id)) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền xem khoản vay trong dự án này.');
        }

        $loan->load('contractor', 'creator', 'updater');

        return Inertia::render('Loans/Show', [
            'loan' => $loan
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Loan $loan)
    {
        // Kiểm tra quyền chỉnh sửa khoản vay trong dự án
        if (!ProjectPermission::hasPermissionInProject('loans.edit', $loan->project_id)) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa khoản vay trong dự án này.');
        }

        $contractors = Contractor::orderBy('name')->get();

        // Lấy danh sách dự án mà người dùng có quyền chỉnh sửa khoản vay
        $projectIds = ProjectPermission::getProjectsWithPermission('loans.edit');
        $projects = Project::whereIn('id', $projectIds)->orderBy('name')->get();

        return Inertia::render('Loans/Edit', [
            'loan' => $loan->load('contractor', 'project', 'creator', 'updater'),
            'contractors' => $contractors,
            'projects' => $projects,
            'statuses' => [
                ['value' => 'active', 'label' => 'Đang vay'],
                ['value' => 'completed', 'label' => 'Đã hoàn thành']
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Loan $loan)
    {
        // Kiểm tra quyền chỉnh sửa khoản vay trong dự án hiện tại
        if (!ProjectPermission::hasPermissionInProject('loans.edit', $loan->project_id)) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền chỉnh sửa khoản vay trong dự án này.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'contractor_id' => 'required|exists:contractors,id',
            'amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,completed',
            'notes' => 'nullable|string',
            'contract_file' => 'nullable|file|max:5120' // Tối đa 5MB
        ]);

        // Nếu dự án được thay đổi, kiểm tra quyền trong dự án mới
        if ($loan->project_id != $validated['project_id'] &&
            !ProjectPermission::hasPermissionInProject('loans.edit', $validated['project_id'])) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền chuyển khoản vay sang dự án này.');
        }

        // Xử lý file hợp đồng
        if ($request->hasFile('contract_file')) {
            // Xóa file cũ nếu có
            if ($loan->contract_file) {
                Storage::disk('public')->delete($loan->contract_file);
            }

            $path = $request->file('contract_file')->store('loan_contracts', 'public');
            $validated['contract_file'] = $path;
        }

        // Cập nhật thông tin người chỉnh sửa
        $validated['updated_by'] = Auth::id();

        $loan->update($validated);

        return redirect()->route('loans.index')
            ->with('success', 'Cập nhật khoản vay thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Loan $loan)
    {
        // Kiểm tra quyền xóa khoản vay trong dự án
        if (!ProjectPermission::hasPermissionInProject('loans.delete', $loan->project_id)) {
            return redirect()->route('loans.index')
                ->with('error', 'Bạn không có quyền xóa khoản vay trong dự án này.');
        }

        // Xóa file hợp đồng nếu có
        if ($loan->contract_file) {
            Storage::disk('public')->delete($loan->contract_file);
        }

        $loan->delete();

        return redirect()->route('loans.index')
            ->with('success', 'Xóa khoản vay thành công!');
    }

    /**
     * Cập nhật trạng thái khoản vay
     */
    public function updateStatus(Request $request, Loan $loan)
    {
        // Kiểm tra quyền cập nhật khoản vay trong dự án
        if (!ProjectPermission::hasPermissionInProject('loans.edit', $loan->project_id)) {
            return back()->with('error', 'Bạn không có quyền cập nhật trạng thái khoản vay trong dự án này.');
        }

        $validated = $request->validate([
            'status' => 'required|in:active,completed'
        ]);

        $loan->update([
            'status' => $validated['status'],
            'updated_by' => Auth::id()
        ]);

        return back()->with('success', 'Cập nhật trạng thái khoản vay thành công!');
    }
}
