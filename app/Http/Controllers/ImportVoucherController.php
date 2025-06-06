<?php

namespace App\Http\Controllers;

use App\Models\ImportVoucher;
use App\Models\ImportVoucherItem;
use App\Models\Product;
use App\Models\Project;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Helpers\ProjectPermission;

class ImportVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ImportVoucher::whereNull('deleted_at')->with(['project', 'contractor', 'creator', 'updater', 'bidPackage']);

        // Áp dụng tìm kiếm nếu có
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('code', 'like', '%' . $request->search . '%')
                  ->orWhereHas('project', function($subQuery) use ($request) {
                      $subQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('contractor', function($subQuery) use ($request) {
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
            $query->whereDate('import_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && !empty($request->date_to)) {
            $query->whereDate('import_date', '<=', $request->date_to);
        }

        $projectIds = ProjectPermission::getProjectsWithPermission('import-vouchers.view');

        $query->whereIn('project_id', $projectIds);

        $importVouchers = $query->latest()->paginate(10)->withQueryString();

        // Load tổng tiền cho mỗi phiếu nhập
        $importVouchers->getCollection()->transform(function ($voucher) {
            $voucher->total_amount = $voucher->getTotalAmountAttribute();
            return $voucher;
        });

        $projects = Project::whereNull('deleted_at')->whereIn('id', $projectIds)->get();

        return Inertia::render('ImportVouchers/Index', [
            'importVouchers' => $importVouchers,
            'projects' => $projects,
            'filters' => $request->only(['search', 'project_id', 'date_from', 'date_to'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projectIds = ProjectPermission::getProjectsWithPermission('import-vouchers.create');
        $projects = Project::with(['bidPackages' => function($query) {
            $query->whereNull('deleted_at');
        }])->whereIn('id', $projectIds)->get();
        $contractors = Contractor::all();
        $products = Product::with('unit')->get();

        // Tạo mã phiếu nhập mới
        $lastVoucher = ImportVoucher::latest()->first();
        $newCode = 'NK-' . date('Ymd') . '-001';

        if ($lastVoucher) {
            $lastCode = $lastVoucher->code;
            if (strpos($lastCode, date('Ymd')) !== false) {
                $codeParts = explode('-', $lastCode);
                $lastNumber = intval(end($codeParts));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
                $newCode = 'NK-' . date('Ymd') . '-' . $newNumber;
            }
        }

        return Inertia::render('ImportVouchers/Create', [
            'projects' => $projects,
            'contractors' => $contractors,
            'products' => $products,
            'suggestedCode' => $newCode
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!ProjectPermission::hasPermissionInProject('import-vouchers.create', $request->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo phiếu nhập.');
        }
        $request->validate([
            'code' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'import_date' => 'required|date',
            'contractor_id' => 'nullable|exists:contractors,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.import_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Create import voucher
            $importVoucher = ImportVoucher::create([
                'code' => $request->code,
                'project_id' => $request->project_id,
                'bid_package_id' => $request->bid_package_id,
                'import_date' => $request->import_date,
                'contractor_id' => $request->contractor_id,
                'notes' => $request->notes,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create import voucher items
            foreach ($request->items as $item) {
                ImportVoucherItem::create([
                    'import_voucher_id' => $importVoucher->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'import_price' => str_replace(',', '', $item['import_price']),
                ]);
            }

            DB::commit();

            return redirect()->route('import-vouchers.index')
                ->with('success', 'Phiếu nhập kho đã được tạo thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ImportVoucher $importVoucher)
    {
        if (!ProjectPermission::hasPermissionInProject('import-vouchers.view', $importVoucher->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem phiếu nhập.');
        }

        $importVoucher->load([
            'project', 'contractor', 'creator', 'updater',
            'items.product.unit', 'bidPackage'
        ]);

        // Tính tổng tiền
        $importVoucher->total_amount = $importVoucher->getTotalAmountAttribute();

        return Inertia::render('ImportVouchers/Show', [
            'importVoucher' => $importVoucher
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ImportVoucher $importVoucher)
    {
        if (!ProjectPermission::hasPermissionInProject('import-vouchers.edit', $importVoucher->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa phiếu nhập.');
        }
        $importVoucher->load(['items.product.unit', 'bidPackage']);
        $projectIds = ProjectPermission::getProjectsWithPermission('import-vouchers.edit');
        $projects = Project::with(['bidPackages' => function($query) {
            $query->whereNull('deleted_at');
        }])->whereIn('id', $projectIds)->get();
        $contractors = Contractor::all();
        $products = Product::with('unit')->get();

        return Inertia::render('ImportVouchers/Edit', [
            'importVoucher' => $importVoucher,
            'projects' => $projects,
            'contractors' => $contractors,
            'products' => $products
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ImportVoucher $importVoucher)
    {
        if (!ProjectPermission::hasPermissionInProject('import-vouchers.edit', $importVoucher->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền chỉnh sửa phiếu nhập.');
        }

        // Kiểm tra nếu người dùng đang cố gắng chuyển phiếu xuất kho sang dự án khác mà họ không có quyền
        if ($request->project_id != $importVoucher->project_id &&
            !ProjectPermission::hasPermissionInProject('import-vouchers.edit', $request->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền chuyển phiếu nhập sang dự án này!');
        }

        $request->validate([
            'code' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'bid_package_id' => 'nullable|exists:bid_packages,id',
            'import_date' => 'required|date',
            'contractor_id' => 'nullable|exists:contractors,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:import_voucher_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.import_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Update import voucher
            $importVoucher->update([
                'code' => $request->code,
                'project_id' => $request->project_id,
                'bid_package_id' => $request->bid_package_id,
                'import_date' => $request->import_date,
                'contractor_id' => $request->contractor_id,
                'notes' => $request->notes,
                'updated_by' => Auth::id(),
            ]);

            // Get current item IDs
            $currentItemIds = $importVoucher->items->pluck('id')->toArray();
            $updatedItemIds = collect($request->items)->pluck('id')->filter()->toArray();

            // Delete removed items
            $itemsToDelete = array_diff($currentItemIds, $updatedItemIds);
            if (!empty($itemsToDelete)) {
                ImportVoucherItem::whereIn('id', $itemsToDelete)->delete();
            }

            // Update or create items
            foreach ($request->items as $item) {
                $itemData = [
                    'import_voucher_id' => $importVoucher->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'import_price' => str_replace(',', '', $item['import_price']),
                ];

                if (isset($item['id']) && !empty($item['id'])) {
                    ImportVoucherItem::find($item['id'])->update($itemData);
                } else {
                    ImportVoucherItem::create($itemData);
                }
            }

            DB::commit();

            return redirect()->route('import-vouchers.index')
                ->with('success', 'Phiếu nhập kho đã được cập nhật thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ImportVoucher $importVoucher)
    {
        if (!ProjectPermission::hasPermissionInProject('import-vouchers.delete', $importVoucher->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa phiếu nhập.');
        }
        try {
            // Delete import voucher (will cascade delete items due to foreign key constraint)
            $importVoucher->deleted_at = now();
            $importVoucher->save();

            return redirect()->route('import-vouchers.index')
                ->with('success', 'Phiếu nhập kho đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Không thể xóa phiếu nhập kho: ' . $e->getMessage());
        }
    }
}
