<?php

namespace App\Http\Controllers;

use App\Models\BidPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BidPackageController extends Controller
{
    /**
     * Hiển thị form tạo gói thầu mới
     */
    public function create(Project $project)
    {
        return Inertia::render('BidPackages/Create', [
            'project' => $project,
        ]);
    }

    /**
     * Lưu gói thầu mới vào database
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:bid_packages',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'client_price' => 'nullable|numeric|min:0',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
        ]);

        $project->bidPackages()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Gói thầu đã được tạo thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa gói thầu
     */
    public function edit(BidPackage $bidPackage)
    {
        $bidPackage->load('project');

        return Inertia::render('BidPackages/Edit', [
            'bidPackage' => $bidPackage,
            'project' => $bidPackage->project,
        ]);
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
            'client_price' => 'nullable|numeric|min:0',
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

    /**
     * Cập nhật giá giao thầu
     */
    public function updateClientPrice(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'client_price' => 'required|numeric|min:0'
        ]);

        $bidPackage->update($validated);
        $bidPackage->save();

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Giá giao thầu đã được cập nhật thành công.');
    }

    /**
     * Xóa gói thầu
     */
    public function destroy(BidPackage $bidPackage)
    {
        $projectId = $bidPackage->project_id;

        try {
            $bidPackage->delete();
            return redirect()->route('projects.show', $projectId)
                ->with('success', 'Gói thầu đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $projectId)
                ->with('error', 'Không thể xóa gói thầu. Vui lòng thử lại sau.');
        }
    }

    /**
     * Chọn nhà thầu
     */
    public function selectContractor(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'bid_id' => 'required|exists:bids,id'
        ]);

        $bid = $bidPackage->bids()->findOrFail($validated['bid_id']);

        // Cập nhật gói thầu
        $bidPackage->update([
            'selected_contractor_id' => $bid->contractor_id,
            'status' => 'awarded'
        ]);

        // Cập nhật trạng thái của các giá dự thầu
        $bidPackage->bids()->update(['is_selected' => false]);
        $bid->update(['is_selected' => true]);

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', 'Đã chọn nhà thầu thành công.');
    }

    public function updateAdditionalPrice(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'additional_price' => 'required|numeric|min:0',
        ]);

        $bidPackage->additional_price = $validated['additional_price'];
        $bidPackage->client_price = $bidPackage->client_price + $validated['additional_price'];
        $bidPackage->save();
        return redirect()->back()->with('success', 'Giá phát sinh đã được cập nhật thành công.');
    }

    public function updateProfitPercentage(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'profit_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $bidPackage->update([
            'profit_percentage' => $validated['profit_percentage'],
        ]);

        return redirect()->back()->with('success', 'Phần trăm lợi nhuận đã được cập nhật thành công.');
    }
}
