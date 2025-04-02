<?php

namespace App\Http\Controllers;

use App\Models\WorkItem;
use App\Models\BidPackage;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WorkItemController extends Controller
{
    /**
     * Store a newly created work item in storage.
     */
    public function store(Request $request, BidPackage $bidPackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contractor_id' => 'nullable|exists:contractors,id',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $workItem = $bidPackage->work_items()->create($validated);

        return redirect()->back()
            ->with('success', 'Hạng mục đã được tạo thành công.');
    }

    /**
     * Update the specified work item in storage.
     */
    public function update(Request $request, WorkItem $workItem)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contractor_id' => 'nullable|exists:contractors,id',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $workItem->update($validated);

        return redirect()->back()
            ->with('success', 'Hạng mục đã được cập nhật thành công.');
    }

    /**
     * Remove the specified work item from storage.
     */
    public function destroy(WorkItem $workItem)
    {
        $workItem->deleted_at = now();
        $workItem->save();

        return redirect()->back()
            ->with('success', 'Hạng mục đã được xóa thành công.');
    }
}
