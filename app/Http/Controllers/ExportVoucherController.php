<?php

namespace App\Http\Controllers;

use App\Models\ExportVoucher;
use App\Models\ExportVoucherItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ExportVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ExportVoucher::whereNull('deleted_at')->with(['project', 'customer', 'creator', 'updater']);

        // Áp dụng tìm kiếm nếu có
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('project', function($subQuery) use ($request) {
                      $subQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('customer', function($subQuery) use ($request) {
                      $subQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        // Lọc theo dự án
        if ($request->has('project_id') && !empty($request->project_id)) {
            $query->where('project_id', $request->project_id);
        }

        // Lọc theo ngày
        if ($request->has('date_from') && !empty($request->date_from)) {
            $query->whereDate('export_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('export_date', '<=', $request->date_to);
        }

        $exportVouchers = $query->latest()->paginate(10)->withQueryString();

        // Load tổng tiền cho mỗi phiếu xuất
        $exportVouchers->getCollection()->transform(function ($voucher) {
            $voucher->total_amount = $voucher->getTotalAmountAttribute();
            return $voucher;
        });

        $projects = Project::whereNull('deleted_at')->get();

        return Inertia::render('ExportVouchers/Index', [
            'exportVouchers' => $exportVouchers,
            'projects' => $projects,
            'filters' => $request->only(['search', 'project_id', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $customers = Customer::all();
        $products = Product::with('unit')->get();

        // Tạo mã phiếu xuất mới
        $lastVoucher = ExportVoucher::latest()->first();
        $newCode = 'XK-' . date('Ymd') . '-001';

        if ($lastVoucher) {
            $lastCode = $lastVoucher->code;
            if (strpos($lastCode, date('Ymd')) !== false) {
                $codeParts = explode('-', $lastCode);
                $lastNumber = intval(end($codeParts));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                $newCode = 'XK-' . date('Ymd') . '-' . $newNumber;
            }
        }

        return Inertia::render('ExportVouchers/Create', [
            'projects' => $projects,
            'customers' => $customers,
            'products' => $products,
            'suggestedCode' => $newCode
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'export_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.export_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create export voucher
            $exportVoucher = ExportVoucher::create([
                'code' => $request->code,
                'project_id' => $request->project_id,
                'export_date' => $request->export_date,
                'customer_id' => $request->customer_id,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create export voucher items
            foreach ($request->items as $item) {
                ExportVoucherItem::create([
                    'export_voucher_id' => $exportVoucher->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'export_price' => str_replace(',', '', $item['export_price']),
                ]);
            }

            DB::commit();

            return redirect()->route('export-vouchers.index')
                ->with('success', 'Phiếu xuất kho đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ExportVoucher $exportVoucher)
    {
        $exportVoucher->load([
            'project', 'customer', 'creator', 'updater',
            'items.product.unit'
        ]);

        // Tính tổng tiền
        $exportVoucher->total_amount = $exportVoucher->getTotalAmountAttribute();

        return Inertia::render('ExportVouchers/Show', [
            'exportVoucher' => $exportVoucher
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExportVoucher $exportVoucher)
    {
        $exportVoucher->load(['items.product.unit']);

        $projects = Project::all();
        $customers = Customer::all();
        $products = Product::with('unit')->get();

        return Inertia::render('ExportVouchers/Edit', [
            'exportVoucher' => $exportVoucher,
            'projects' => $projects,
            'customers' => $customers,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExportVoucher $exportVoucher)
    {
        $request->validate([
            'code' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'export_date' => 'required|date',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:export_voucher_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.export_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Update export voucher
            $exportVoucher->update([
                'code' => $request->code,
                'project_id' => $request->project_id,
                'export_date' => $request->export_date,
                'customer_id' => $request->customer_id,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Get current item IDs
            $currentItemIds = $exportVoucher->items->pluck('id')->toArray();
            $updatedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            // Delete removed items
            $itemsToDelete = array_diff($currentItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                ExportVoucherItem::whereIn('id', $itemsToDelete)->delete();
            }

            // Update or create items
            foreach ($request->items as $item) {
                $itemData = [
                    'export_voucher_id' => $exportVoucher->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'export_price' => str_replace(',', '', $item['export_price']),
                ];

                if (isset($item['id']) && !empty($item['id'])) {
                    ExportVoucherItem::find($item['id'])->update($itemData);
                } else {
                    ExportVoucherItem::create($itemData);
                }
            }

            DB::commit();

            return redirect()->route('export-vouchers.index')
                ->with('success', 'Phiếu xuất kho đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExportVoucher $exportVoucher)
    {
        try {
            // Delete export voucher (will cascade delete items due to foreign key constraint)
            $exportVoucher->deleted_at = now();
            $exportVoucher->save();

            return redirect()->route('export-vouchers.index')
                ->with('success', 'Phiếu xuất kho đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Không thể xóa phiếu xuất kho: ' . $e->getMessage());
        }
    }
}
