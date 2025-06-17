<?php

namespace App\Http\Controllers;

use App\Models\TaskLink;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Helpers\ProjectPermission;

class TaskLinkController extends Controller
{
    /**
     * Tạo mới liên kết giữa các công việc
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_id' => 'required|exists:tasks,id',
            'target_id' => 'required|exists:tasks,id',
            'type' => 'required|integer|min:0|max:3',
        ]);

        $sourceTask = Task::find($validated['source_id']);
        if (!ProjectPermission::hasPermissionInProject('tasks.edit', $sourceTask->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền tạo liên kết giữa các công việc'], 403);
        }

        // Kiểm tra source_id và target_id không được trùng nhau
        if ($validated['source_id'] == $validated['target_id']) {
            return response()->json(['error' => 'Không thể tạo liên kết đến chính công việc'], 422);
        }

        // Kiểm tra liên kết đã tồn tại chưa
        $existingLink = TaskLink::where('source_id', $validated['source_id'])
            ->where('target_id', $validated['target_id'])
            ->where('type', $validated['type'])
            ->first();

        if ($existingLink) {
            // Nếu liên kết đã tồn tại, trả về ID của liên kết đó thay vì báo lỗi
            return response()->json([
                'id' => $existingLink->id,
                'source' => $existingLink->source_id,
                'target' => $existingLink->target_id,
                'type' => $existingLink->type
            ]);
        }

        $link = TaskLink::create($validated);

        // Nếu là constrain Finish to Start (type = 0), tự động cập nhật ngày bắt đầu của công việc kế tiếp
        if ($validated['type'] == 0) {
            $sourceTask = Task::find($validated['source_id']);
            $targetTask = Task::find($validated['target_id']);

            if ($sourceTask && $targetTask) {
                // Tính ngày kết thúc của công việc nguồn
                $sourceEndDate = date('Y-m-d', strtotime($sourceTask->start_date . ' + ' . $sourceTask->duration . ' days'));

                // Cập nhật ngày bắt đầu của công việc đích là ngày sau ngày kết thúc của công việc nguồn
                $newStartDate = date('Y-m-d', strtotime($sourceEndDate . ' + 1 day'));
                $targetTask->start_date = $newStartDate;
                $targetTask->save();
            }
        }

        return response()->json([
            'id' => $link->id,
            'source' => $link->source_id,
            'target' => $link->target_id,
            'type' => $link->type
        ]);
    }

    /**
     * Cập nhật liên kết
     */
    public function update(Request $request, TaskLink $taskLink)
    {
        $validated = $request->validate([
            'source_id' => 'sometimes|required|exists:tasks,id',
            'target_id' => 'sometimes|required|exists:tasks,id',
            'type' => 'sometimes|required|integer|min:0|max:3',
        ]);

        $task = Task::find($validated['source_id']);
        if (!ProjectPermission::hasPermissionInProject('tasks.edit', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền cập nhật liên kết giữa các công việc'], 403);
        }

        // Kiểm tra source_id và target_id không được trùng nhau
        if (
            (isset($validated['source_id']) && isset($validated['target_id']) && $validated['source_id'] == $validated['target_id']) ||
            (isset($validated['source_id']) && !isset($validated['target_id']) && $validated['source_id'] == $taskLink->target_id) ||
            (isset($validated['target_id']) && !isset($validated['source_id']) && $validated['target_id'] == $taskLink->source_id)
        ) {
            return response()->json(['error' => 'Không thể tạo liên kết đến chính công việc'], 422);
        }

        $taskLink->update($validated);

        return response()->json([
            'id' => $taskLink->id,
            'source' => $taskLink->source_id,
            'target' => $taskLink->target_id,
            'type' => $taskLink->type
        ]);
    }

    /**
     * Xóa liên kết
     */
    public function destroy($id)
    {
        try {
            $taskLink = TaskLink::findOrFail($id);

            $task = Task::find($taskLink->source_id);
            if (!$task || !ProjectPermission::hasPermissionInProject('tasks.edit', $task->project_id)) {
                return response()->json(['error' => 'Bạn không có quyền xóa liên kết giữa các công việc'], 403);
            }

            $taskLink->delete();
            return response()->json(['success' => true]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Không tìm thấy liên kết'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi xóa liên kết: ' . $e->getMessage()], 500);
        }
    }
}
