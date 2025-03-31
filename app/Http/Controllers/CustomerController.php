<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
        return Inertia::render('Customers/Create');
    }

    /**
     * Lưu khách hàng mới
     */
    public function store(Request $request)
    {
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
        return Inertia::render('Customers/Edit', [
            'customer' => $customer
        ]);
    }

    /**
     * Cập nhật thông tin khách hàng
     */
    public function update(Request $request, Customer $customer)
    {
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
