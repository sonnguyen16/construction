<?php

namespace App\Http\Controllers;

use App\Models\ReceiptCategory;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ReceiptCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách loại thu');
        }
        
        $search = $request->input('search');

        $receiptCategories = ReceiptCategory::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('ReceiptCategories/Index', [
            'receiptCategories' => $receiptCategories,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo loại thu');
        }
        
        return Inertia::render('ReceiptCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo loại thu');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        ReceiptCategory::create($validated);

        return Redirect::route('receipt-categories.index')->with('success', 'Loại thu đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReceiptCategory $receiptCategory)
    {
        // Kiểm tra quyền global để sửa loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa loại thu');
        }
        
        return Inertia::render('ReceiptCategories/Edit', [
            'receiptCategory' => $receiptCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReceiptCategory $receiptCategory)
    {
        // Kiểm tra quyền global để sửa loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa loại thu');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $receiptCategory->update($validated);

        return Redirect::route('receipt-categories.index')->with('success', 'Loại thu đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceiptCategory $receiptCategory)
    {
        // Kiểm tra quyền global để xóa loại thu
        if (!ProjectPermission::hasGlobalPermission('receipt-categories.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa loại thu');
        }
        
        $receiptCategory->delete();

        return Redirect::route('receipt-categories.index')->with('success', 'Loại thu đã được xóa thành công.');
    }
}
