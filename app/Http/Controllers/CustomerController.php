<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Hiển thị danh sách khách hàng
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách khách hàng');
        }
        
        $query = Customer::query()->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        // Sắp xếp
        $query->orderBy('name');

        $customers = $query->paginate(10)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Hiển thị form tạo khách hàng mới
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo khách hàng');
        }
        
        return Inertia::render('Customers/Create');
    }

    /**
     * Lưu khách hàng mới
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo khách hàng');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được tạo thành công.');
    }

    /**
     * Hiển thị chi tiết khách hàng
     */
    public function show(Customer $customer)
    {
        // Kiểm tra quyền global để xem chi tiết khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem chi tiết khách hàng');
        }
        
        $customer->load(['creator', 'updater', 'receiptVouchers' => function ($query) {
            $query->with('project', 'bidPackage', 'creator')->orderBy('created_at', 'desc');
        }]);

        return Inertia::render('Customers/Show', [
            'customer' => $customer
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa khách hàng
     */
    public function edit(Customer $customer)
    {
        // Kiểm tra quyền global để sửa khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa khách hàng');
        }
        
        return Inertia::render('Customers/Edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Cập nhật thông tin khách hàng
     */
    public function update(Request $request, Customer $customer)
    {
        // Kiểm tra quyền global để sửa khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa khách hàng');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Thông tin khách hàng đã được cập nhật thành công.');
    }

    /**
     * Xóa khách hàng
     */
    public function destroy(Customer $customer)
    {
        // Kiểm tra quyền global để xóa khách hàng
        if (!ProjectPermission::hasGlobalPermission('customers.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa khách hàng');
        }
        
        // Kiểm tra xem khách hàng có phiếu thu không
        if ($customer->receiptVouchers()->count() > 0) {
            return back()->with('error', 'Không thể xóa khách hàng này vì đã có phiếu thu liên quan.');
        }

        $customer->deleted_at = now();
        $customer->save();

        return redirect()->route('customers.index')
            ->with('success', 'Khách hàng đã được xóa thành công.');
    }
}
