<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\BidPackage;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BidController extends Controller
{
    /**
     * Hiển thị form thêm giá dự thầu mới
     */
    public function create(BidPackage $bidPackage)
    {
        $bidPackage->load('project');
        $contractors = Contractor::orderBy('name')->get();

        // Lấy danh sách nhà thầu đã đặt giá
        $existingBidContractorIds = $bidPackage->bids()->pluck('contractor_id')->toArray();

        return Inertia::render('Bids/Create', [
            'bidPackage' => $bidPackage,
            'project' => $bidPackage->project,
            'contractors' => $contractors,
            'existingBidContractorIds' => $existingBidContractorIds,
        ]);
    }

    /**
     * Lưu giá dự thầu mới vào database
     */
    public function store(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Kiểm tra xem nhà thầu đã đặt giá cho gói thầu này chưa
        $existingBid = $bidPackage->bids()->where('contractor_id', $validated['contractor_id'])->first();

        if ($existingBid) {
            return redirect()->back()->withErrors([
                'contractor_id' => 'Nhà thầu này đã đặt giá cho gói thầu này.',
            ]);
        }

        $bidPackage->bids()->create($validated);

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Giá dự thầu đã được thêm thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa giá dự thầu
     */
    public function edit(Bid $bid)
    {
        $bid->load(['bidPackage.project', 'contractor']);

        return Inertia::render('Bids/Edit', [
            'bid' => $bid,
            'bidPackage' => $bid->bidPackage,
            'project' => $bid->bidPackage->project,
            'contractor' => $bid->contractor,
        ]);
    }

    /**
     * Cập nhật thông tin giá dự thầu
     */
    public function update(Request $request, Bid $bid)
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $bid->update($validated);

        // Nếu giá dự thầu này đã được chọn, cập nhật giá dự toán của gói thầu
        if ($bid->is_selected) {
            $bidPackage = $bid->bidPackage;
            $bidPackage->estimated_price = $validated['price'];
            $bidPackage->calculateProfit()->save();
        }

        return redirect()->route('projects.show', $bid->bidPackage->project_id)
            ->with('success', 'Thông tin giá dự thầu đã được cập nhật.');
    }

    /**
     * Xóa giá dự thầu
     */
    public function destroy(Bid $bid)
    {
        $projectId = $bid->bidPackage->project_id;

        // Nếu giá dự thầu này đã được chọn, cập nhật gói thầu
        if ($bid->is_selected) {
            $bidPackage = $bid->bidPackage;
            $bidPackage->selected_contractor_id = null;
            $bidPackage->estimated_price = null;
            $bidPackage->calculateProfit()->save();
        }

        $bid->delete();

        return redirect()->route('projects.show', $projectId)
            ->with('success', 'Giá dự thầu đã được xóa thành công.');
    }

    /**
     * Chọn nhà thầu cho gói thầu
     */
    public function selectContractor(Request $request, Bid $bid)
    {
        $bidPackage = $bid->bidPackage;

        // Bỏ chọn tất cả các giá dự thầu khác
        $bidPackage->bids()->update(['is_selected' => false]);

        // Chọn giá dự thầu này
        $bid->is_selected = true;
        $bid->save();

        // Cập nhật thông tin gói thầu
        $bidPackage->selected_contractor_id = $bid->contractor_id;
        $bidPackage->estimated_price = $bid->price;
        $bidPackage->status = 'awarded';
        $bidPackage->calculateProfit()->save();

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Đã chọn nhà thầu thành công.');
    }
}