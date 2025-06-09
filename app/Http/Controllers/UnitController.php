<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Kiểm tra quyền global để xem danh sách đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách đơn vị');
        }
        
        $search = $request->input('search');

        $units = Unit::query()
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%");
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Units/Index', [
            'units' => $units,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo đơn vị');
        }
        
        return Inertia::render('Units/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo đơn vị');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        Unit::create($validated);

        return Redirect::route('units.index')->with('success', 'Đơn vị đã được tạo thành công.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        // Kiểm tra quyền global để sửa đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa đơn vị');
        }
        
        return Inertia::render('Units/Edit', [
            'unit' => $unit
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        // Kiểm tra quyền global để sửa đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa đơn vị');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'note' => 'nullable|string',
        ]);

        $unit->update($validated);

        return Redirect::route('units.index')->with('success', 'Đơn vị đã được cập nhật thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        // Kiểm tra quyền global để xóa đơn vị
        if (!ProjectPermission::hasGlobalPermission('units.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa đơn vị');
        }
        
        $unit->deleted_at = now();
        $unit->save();
        return Redirect::route('units.index')->with('success', 'Đơn vị đã được xóa thành công.');
    }
}
