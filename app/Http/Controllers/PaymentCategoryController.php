<?php

namespace App\Http\Controllers;

use App\Models\PaymentCategory;
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
        return Inertia::render('PaymentCategories/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
        return Inertia::render('PaymentCategories/Edit', [
            'paymentCategory' => $paymentCategory
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PaymentCategory $paymentCategory)
    {
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
        $paymentCategory->delete();

        return Redirect::route('payment-categories.index')->with('success', 'Loại chi đã được xóa thành công.');
    }
}
