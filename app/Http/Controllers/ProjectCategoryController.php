<?php

namespace App\Http\Controllers;

use App\Models\ProjectCategory;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProjectCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách danh mục dự án');
        }
        
        $search = $request->input('search');

        $projectCategories = ProjectCategory::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ProjectCategories/Index', [
            'projectCategories' => $projectCategories,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo danh mục dự án');
        }
        
        return Inertia::render('ProjectCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo danh mục dự án');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        ProjectCategory::create($validated);

        return Redirect::route('project-categories.index')->with('success', 'Danh mục dự án đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectCategory $projectCategory)
    {
        // Kiểm tra quyền global để sửa danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa danh mục dự án');
        }
        
        return Inertia::render('ProjectCategories/Edit', [
            'projectCategory' => $projectCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectCategory $projectCategory)
    {
        // Kiểm tra quyền global để sửa danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa danh mục dự án');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $projectCategory->update($validated);

        return Redirect::route('project-categories.index')->with('success', 'Danh mục dự án đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectCategory $projectCategory)
    {
        // Kiểm tra quyền global để xóa danh mục dự án
        if (!ProjectPermission::hasGlobalPermission('project-categories.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa danh mục dự án');
        }
        
        $projectCategory->delete();

        return Redirect::route('project-categories.index')->with('success', 'Danh mục dự án đã được xóa thành công.');
    }
}
