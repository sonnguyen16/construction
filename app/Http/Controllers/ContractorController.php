<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContractorController extends Controller
{
    /**
     * Hiển thị danh sách nhà thầu
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách nhà thầu');
        }
        
        $query = Contractor::query()->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('address', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%");
            });
        }

        $contractors = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Contractors/Index', [
            'contractors' => $contractors,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Hiển thị form tạo nhà thầu mới
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo nhà thầu');
        }
        
        return Inertia::render('Contractors/Create');
    }

    /**
     * Lưu nhà thầu mới vào database
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo nhà thầu');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Contractor::create($validated);

        return redirect()->route('contractors.index')
            ->with('success', 'Nhà thầu đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa nhà thầu
     */
    public function edit(Contractor $contractor)
    {
        // Kiểm tra quyền global để sửa nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa nhà thầu');
        }
        
        return Inertia::render('Contractors/Edit', [
            'contractor' => $contractor
        ]);
    }

    /**
     * Cập nhật thông tin nhà thầu
     */
    public function update(Request $request, Contractor $contractor)
    {
        // Kiểm tra quyền global để sửa nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa nhà thầu');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $contractor->update($validated);

        return redirect()->route('contractors.index')
            ->with('success', 'Thông tin nhà thầu đã được cập nhật.');
    }

    /**
     * Xóa nhà thầu
     */
    public function destroy(Contractor $contractor)
    {
        // Kiểm tra quyền global để xóa nhà thầu
        if (!ProjectPermission::hasGlobalPermission('contractors.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa nhà thầu');
        }
        
        $contractor->deleted_at = now();
        $contractor->save();

        return redirect()->route('contractors.index')
            ->with('success', 'Nhà thầu đã được xóa thành công.');
    }
}
