<?php

namespace App\Http\Controllers;

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
            'code' => 'required|string|max:50|unique:bid_packages',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
        ]);

        $project->bidPackages()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Gói thầu đã được tạo thành công.');
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
            'status' => 'required|in:open,awarded,completed,cancelled'
        ]);

        $bidPackage->profit = $bidPackage->client_price - $bidPackage->estimated_price;
        $bidPackage->update($validated);

        if ($bidPackage->status === 'open') {
            $bidPackage->bids()->update(['is_selected' => false]);
            $bidPackage->selected_contractor_id = null;
            $bidPackage->save();
        }

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Gói thầu đã được cập nhật thành công.');
    }

    public function destroy(BidPackage $bidPackage)
    {
        $projectId = $bidPackage->project_id;

        try {
            $bidPackage->deleted_at = now();
            $bidPackage->save();
            return redirect()->route('projects.show', $projectId)
                ->with('success', 'Gói thầu đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $projectId)
                ->with('error', 'Không thể xóa gói thầu. Vui lòng thử lại sau.');
        }
    }

    public function updateAdditionalPrice(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'additional_price' => 'required|numeric|min:0',
        ]);

        $bidPackage->additional_price = $validated['additional_price'];
        $bidPackage->client_price = $bidPackage->estimated_price + $validated['additional_price'];
        $bidPackage->save();
        return redirect()->back()->with('success', 'Giá phát sinh đã được cập nhật thành công.');
    }

    public function updateProfitPercentage(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'profit_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $bidPackage->profit_percentage = $validated['profit_percentage'];
        $bidPackage->profit = $bidPackage->client_price * $bidPackage->profit_percentage / 100;
        $bidPackage->save();

        return redirect()->back()->with('success', 'Phần trăm lợi nhuận đã được cập nhật thành công.');
    }
}
