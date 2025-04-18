<?php

namespace App\Http\Controllers;

use App\Models\Bid;
use App\Models\BidPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BidPackageController extends Controller
{
    /**
     * Lưu gói thầu mới vào database
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
            'parent_id' => 'nullable|exists:bid_packages,id',
            'is_work_item' => 'nullable|boolean',
        ]);

        $project->bidPackages()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', $validated['is_work_item'] ? 'Hạng mục đã được tạo thành công.' : 'Gói thầu đã được tạo thành công.');
    }

    /**
     * Lưu hạng mục con cho gói thầu
     */
    public function storeWorkItem(Request $request, BidPackage $parent)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
        ]);

        $validated['parent_id'] = $parent->id;
        $validated['is_work_item'] = true;
        $validated['project_id'] = $parent->project_id;

        $parent->children()->create($validated);

        return redirect()->route('projects.show', $parent->project_id)
            ->with('success', 'Hạng mục đã được tạo thành công.');
    }

    /**
     * Cập nhật gói thầu
     */
    public function update(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
            'parent_id' => 'nullable|exists:bid_packages,id',
            'is_work_item' => 'nullable|boolean',
        ]);

        // Lấy giá dự thầu và giá giao thầu hiện tại
        $estimatedPrice = (int)$validated['estimated_price'];

        $bidPackage->update($validated);


        if ($bidPackage->status === 'open') {
            $bidPackage->bids()->update(['is_selected' => false]);
            $bidPackage->selected_contractor_id = null;
            $bidPackage->save();
        }

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', $bidPackage->is_work_item ? 'Hạng mục đã được cập nhật thành công.' : 'Gói thầu đã được cập nhật thành công.');
    }

    public function destroy(BidPackage $bidPackage)
    {
        $projectId = $bidPackage->project_id;

        try {
            // Xóa tất cả hạng mục con nếu là gói thầu cha
            if (!$bidPackage->parent_id) {
                $bidPackage->children()->update(['deleted_at' => now()]);
            }

            $bidPackage->deleted_at = now();
            $bidPackage->save();

            if ($bidPackage->is_work_item) {
                $parent = $bidPackage->parent;
                $parent->client_price -= $bidPackage->client_price;
                $parent->save();
            }

            return redirect()->route('projects.show', $projectId)
                ->with('success', $bidPackage->is_work_item ? 'Hạng mục đã được xóa thành công.' : 'Gói thầu đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $projectId)
                ->with('error', 'Không thể xóa. Vui lòng thử lại sau.');
        }
    }

    public function updateAdditionalPrice(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'additional_price' => 'required|numeric|min:0',
        ]);

        // Lưu giá phát sinh
        $bidPackage->additional_price = $validated['additional_price'];
        // Tính giá giao thầu = giá dự thầu + giá phát sinh
        $bidPriceSelected = $bidPackage->bidPriceSelected;
        $additionalPrice = (int)$validated['additional_price'];
        $bidPackage->client_price = $bidPriceSelected->price + $additionalPrice;
        // Cập nhật lợi nhuận (sử dụng định nghĩa mới: lợi nhuận = giá dự thầu - giá giao thầu)
        $bidPackage->profit = $bidPriceSelected->price - $bidPackage->client_price;
        $bidPackage->save();

        if ($bidPackage->is_work_item) {
            $parent = $bidPackage->parent;
            $bidParent = $parent->bidPriceSelected;
            $parent->client_price = $bidParent->price + $parent->additional_price + $bidPackage->client_price;
            $parent->save();
        }

        return redirect()->back()->with('success', 'Giá phát sinh đã được cập nhật thành công.');
    }

    public function updateProfitPercentage(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'profit_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $bidPackage->profit_percentage = $validated['profit_percentage'];

        // Cập nhật lợi nhuận theo định nghĩa mới: Lợi nhuận = giá dự thầu - giá giao thầu
        $estimatedPrice = (int)$bidPackage->estimated_price;
        $clientPrice = (int)$bidPackage->client_price;
        $bidPackage->profit = $estimatedPrice - $clientPrice;

        $bidPackage->save();

        return redirect()->back()->with('success', 'Phần trăm lợi nhuận đã được cập nhật thành công.');
    }

    /**
     * Cập nhật thứ tự gói thầu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request)
    {
        try {
            $packages = $request->input('packages', []);

            foreach ($packages as $package) {
                if (isset($package['id']) && isset($package['order'])) {
                    BidPackage::where('id', $package['id'])->update(['order' => $package['order']]);
                }
            }


            $validated = $request->validate([
                'packages' => 'required|array',
                'packages.*.id' => 'required|exists:bid_packages,id',
                'packages.*.order' => 'required|integer|min:0',
            ]);

            foreach ($validated['packages'] as $package) {
                BidPackage::where('id', $package['id'])->update(['order' => $package['order']]);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
