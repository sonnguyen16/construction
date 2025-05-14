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
        $existingBid = $bidPackage->bids()->where('contractor_id', $validated['contractor_id'])
                        ->whereNull('deleted_at')
                        ->first();

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
     * Cập nhật thông tin dự thầu
     */
    public function update(Request $request, Bid $bid)
    {
        $validated = $request->validate([
            'contractor_id' => 'sometimes|required|exists:contractors,id',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if($bid->is_selected){
            $bidPackage = $bid->bidPackage;
            $bidPackage->selected_contractor_id = $validated['contractor_id'];
            $bidPackage->client_price = $validated['price'] + $bidPackage->additional_price;
            $bidPackage->estimated_price = $validated['price'] + $bidPackage->additional_price;
            $bidPackage->status = 'awarded';
            $bidPackage->save();
        }

        $bid->update($validated);

        return redirect()->back()->with('success', 'Giá dự thầu đã được cập nhật thành công.');
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
            $bidPackage->client_price = null;
            $bidPackage->status = 'open';
            $bidPackage->save();
        }

        $bid->deleted_at = now();
        $bid->save();

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
        $bidPackage->client_price = $bid->price + $bidPackage->additional_price;
        $bidPackage->estimated_price = $bid->price + $bidPackage->additional_price;
        $bidPackage->status = 'awarded';
        $bidPackage->save();

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Đã chọn nhà thầu thành công.');
    }
}
