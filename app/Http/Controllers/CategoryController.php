<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh mục');
        }

        $query = Category::query();

        // Tìm kiếm theo tên
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $query->orderBy('id', 'desc')->get();

        return Inertia::render('Category/Index', [
            'categories' => $categories,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo danh mục');
        }

        return Inertia::render('Category/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo danh mục');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);
        Category::create($validated);
        return redirect()->route('categories.index')->with('success', 'Danh mục đã được tạo thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Kiểm tra quyền global để sửa danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa danh mục');
        }

        return Inertia::render('Category/Edit', [
            'category' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Kiểm tra quyền global để sửa danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa danh mục');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);
        $category->update($validated);
        return redirect()->route('categories.index')->with('success', 'Danh mục đã được cập nhật thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        // Kiểm tra quyền global để xóa danh mục
        if (!ProjectPermission::hasGlobalPermission('categories.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa danh mục');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'Danh mục đã được xóa thành công');
    }
}
