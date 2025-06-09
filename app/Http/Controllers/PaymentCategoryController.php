<?php

namespace App\Http\Controllers;

use App\Models\PaymentCategory;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách loại chi');
        }
        
        $search = $request->input('search');

        $paymentCategories = PaymentCategory::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('PaymentCategories/Index', [
            'paymentCategories' => $paymentCategories,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo loại chi');
        }
        
        return Inertia::render('PaymentCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo loại chi');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        PaymentCategory::create($validated);

        return Redirect::route('payment-categories.index')->with('success', 'Loại chi đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PaymentCategory $paymentCategory)
    {
        // Kiểm tra quyền global để sửa loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa loại chi');
        }
        
        return Inertia::render('PaymentCategories/Edit', [
            'paymentCategory' => $paymentCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentCategory $paymentCategory)
    {
        // Kiểm tra quyền global để sửa loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa loại chi');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $paymentCategory->update($validated);

        return Redirect::route('payment-categories.index')->with('success', 'Loại chi đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PaymentCategory $paymentCategory)
    {
        // Kiểm tra quyền global để xóa loại chi
        if (!ProjectPermission::hasGlobalPermission('payment-categories.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa loại chi');
        }
        
        $paymentCategory->delete();

        return Redirect::route('payment-categories.index')->with('success', 'Loại chi đã được xóa thành công.');
    }
}
