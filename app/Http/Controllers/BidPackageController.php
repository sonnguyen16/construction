<?php

namespace App\Http\Controllers;


use App\Models\BidPackage;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Helpers\ProjectPermission;
use App\Http\Controllers\TaskController;

class BidPackageController extends Controller
{
    /**
     * Lưu gói thầu mới vào database
     */
    public function store(Request $request, Project $project)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.create',$project->id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo gói thầu.');
        }
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
            'parent_id' => 'nullable|exists:bid_packages,id',
            'is_work_item' => 'nullable|boolean',
            'auto_calculate' => 'nullable|boolean',
        ]);

        // Tạo gói thầu mới
        $bidPackage = $project->bidPackages()->create($validated);

        // Tạo task tương ứng với gói thầu
        $taskData = [
            'name' => $bidPackage->name,
            'start_date' => now(),
            'duration' => 7, // Mặc định 30 ngày
            'progress' => 0,
            'project_id' => $project->id,
            'bid_package_id' => $bidPackage->id,
            'description' => $bidPackage->description,
            'priority' => 1, // Trung bình
            'status' => 0, // Chưa bắt đầu
        ];

        // Nếu là hạng mục con (có parent_id), tìm task của gói thầu cha để gán parent_id
        if ($bidPackage->parent_id) {
            $parentTask = \App\Models\Task::where('bid_package_id', $bidPackage->parent_id)->first();
            if ($parentTask) {
                $taskData['parent_id'] = $parentTask->id;
            }
        }

        // Tạo task
        \App\Models\Task::create($taskData);

        return redirect()->route('projects.show', $project)
            ->with('success', $validated['is_work_item'] ? 'Hạng mục đã được tạo thành công.' : 'Gói thầu đã được tạo thành công.');
    }

    /**
     * Cập nhật gói thầu
     */
    public function update(Request $request, BidPackage $bidPackage)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.edit',$bidPackage->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật gói thầu.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'estimated_price' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,awarded,completed,cancelled',
            'parent_id' => 'nullable|exists:bid_packages,id',
            'is_work_item' => 'nullable|boolean',
            'auto_calculate' => 'nullable|boolean',
        ]);

        $bidPackage->update($validated);

        if ($bidPackage->status === 'open') {
            $bidPackage->bids()->update(['is_selected' => false]);
            $bidPackage->selected_contractor_id = null;
            $bidPackage->client_price = null;
            $bidPackage->estimated_price = null;
            $bidPackage->save();
        }

        // Cập nhật task liên quan
        $task = \App\Models\Task::where('bid_package_id', $bidPackage->id)->first();

        if ($task) {
            // Chỉ cập nhật tên của task
            $taskData = [
                'name' => $bidPackage->name,
            ];

            // Nếu hạng mục đã hoàn thành, cập nhật progress = 100%
            if ($bidPackage->status === 'completed') {
                $taskData['progress'] = 1; // 100%
            }

            $task->update($taskData);

            // Nếu task có parent_id, cập nhật duration của task cha
            if ($task->parent_id) {
                $taskController = new TaskController();
                $taskController->updateParentTaskDuration($task->parent_id);
            }
        } else {
            // Nếu không tìm thấy task, tạo mới
            $taskData = [
                'name' => $bidPackage->name,
                'start_date' => now(),
                'duration' => 7, // Mặc định 7 ngày
                'progress' => ($bidPackage->status === 'completed') ? 1 : 0,
                'project_id' => $bidPackage->project_id,
                'bid_package_id' => $bidPackage->id,
                'description' => $bidPackage->description,
                'priority' => 1, // Trung bình
                'status' => 0, // Mặc định: Chưa bắt đầu
            ];

            // Nếu là hạng mục con (có parent_id), tìm task của gói thầu cha để gán parent_id
            if ($bidPackage->parent_id) {
                $parentTask = \App\Models\Task::where('bid_package_id', $bidPackage->parent_id)->first();
                if ($parentTask) {
                    $taskData['parent_id'] = $parentTask->id;
                }
            }

            $task = \App\Models\Task::create($taskData);

            // Nếu task có parent_id, cập nhật duration của task cha
            if ($task->parent_id) {
                app(\App\Http\Controllers\TaskController::class)->updateParentTaskDuration($task->parent_id);
            }
        }

        return redirect()->route('projects.show', $bidPackage->project_id)
            ->with('success', $bidPackage->is_work_item ? 'Hạng mục đã được cập nhật thành công.' : 'Gói thầu đã được cập nhật thành công.');
    }

    public function destroy(BidPackage $bidPackage)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.delete',$bidPackage->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa gói thầu.');
        }
        $projectId = $bidPackage->project_id;

        try {
            // Xóa tất cả hạng mục con nếu là gói thầu cha
            if (!$bidPackage->parent_id) {
                $bidPackage->children()->update(['deleted_at' => now()]);
            }

            $bidPackage->deleted_at = now();
            $bidPackage->save();

            return redirect()->route('projects.show', $projectId)
                ->with('success', $bidPackage->is_work_item ? 'Hạng mục đã được xóa thành công.' : 'Gói thầu đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $projectId)
                ->with('error', 'Không thể xóa. Vui lòng thử lại sau.');
        }
    }

    public function updateAdditionalPrice(Request $request, BidPackage $bidPackage)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.edit',$bidPackage->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật gói thầu.');
        }
        $validated = $request->validate([
            'additional_price' => 'required|numeric|min:0',
        ]);

        // Chỉ cập nhật giá phát sinh gốc
        $bidPackage->additional_price = $validated['additional_price'];

        // Tính giá giao thầu = giá dự thầu + giá phát sinh
        $bidPriceSelected = $bidPackage->bidPriceSelected;
        $additionalPrice = (int)$validated['additional_price'];
        $bidPackage->client_price = $bidPriceSelected ? $bidPriceSelected->price + $additionalPrice : $additionalPrice;
        $bidPackage->estimated_price = $bidPriceSelected ? $bidPriceSelected->price + $additionalPrice : $additionalPrice;

        $bidPackage->save();

        return redirect()->back()->with('success', 'Giá phát sinh đã được cập nhật thành công.');
    }



    /**
     * Cập nhật thứ tự gói thầu
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateOrder(Request $request)
    {
        if (!ProjectPermission::hasPermissionInProject('bid-packages.edit',$request->input('packages')[0]['project_id'])) {
            return redirect()->back()->with('error', 'Bạn không có quyền cập nhật gói thầu.');
        }
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
