<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách sản phẩm');
        }
        
        $query = Product::whereNull('deleted_at')->with(['category', 'unit', 'creator', 'updater']);

        // Áp dụng tìm kiếm nếu có
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo danh mục
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo sản phẩm');
        }
        
        $categories = Category::all();
        $units = Unit::all();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
            'units' => $units
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo sản phẩm');
        }
        
        $validated = $request->validate([
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'import_price' => 'required|numeric|min:0',
            'export_price' => 'required|numeric|min:0',
            'initial_stock' => 'required|integer|min:0',
            'warning_threshold' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'notes' => 'nullable|string',
        ]);

        // Parse currency input if needed
        $validated['import_price'] = str_replace(',', '', $validated['import_price']);
        $validated['export_price'] = str_replace(',', '', $validated['export_price']);

        // Add user tracking
        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // Kiểm tra quyền global để xem chi tiết sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem chi tiết sản phẩm');
        }
        
        $product->load(['category', 'unit', 'creator', 'updater']);

        return Inertia::render('Products/Show', [
            'product' => $product
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        // Kiểm tra quyền global để sửa sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa sản phẩm');
        }
        
        $product->load(['category', 'unit']);
        $categories = Category::all();
        $units = Unit::all();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
            'units' => $units
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Kiểm tra quyền global để sửa sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa sản phẩm');
        }
        
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255'],
            'name' => 'required|string|max:255',
            'import_price' => 'required|numeric|min:0',
            'export_price' => 'required|numeric|min:0',
            'initial_stock' => 'required|integer|min:0',
            'warning_threshold' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'notes' => 'nullable|string',
        ]);

        // Parse currency input if needed
        $validated['import_price'] = str_replace(',', '', $validated['import_price']);
        $validated['export_price'] = str_replace(',', '', $validated['export_price']);

        // Update user tracking
        $validated['updated_by'] = Auth::id();

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Kiểm tra quyền global để xóa sản phẩm
        if (!ProjectPermission::hasGlobalPermission('products.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa sản phẩm');
        }
        
        $product->deleted_at = now();
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
    }
}
