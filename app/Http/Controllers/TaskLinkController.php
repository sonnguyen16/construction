<?php

namespace App\Http\Controllers;

use App\Models\TaskLink;
use App\Models\Task;
use Illuminate\Http\Request;

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

        // Kiểm tra source_id và target_id không được trùng nhau
        if ($validated['source_id'] == $validated['target_id']) {
            return response()->json(['message' => 'Không thể tạo liên kết đến chính công việc'], 422);
        }

        // Kiểm tra liên kết đã tồn tại chưa
        $existingLink = TaskLink::where('source_id', $validated['source_id'])
            ->where('target_id', $validated['target_id'])
            ->first();

        if ($existingLink) {
            return response()->json(['message' => 'Liên kết đã tồn tại'], 422);
        }

        $link = TaskLink::create($validated);

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

        // Kiểm tra source_id và target_id không được trùng nhau
        if (
            (isset($validated['source_id']) && isset($validated['target_id']) && $validated['source_id'] == $validated['target_id']) ||
            (isset($validated['source_id']) && !isset($validated['target_id']) && $validated['source_id'] == $taskLink->target_id) ||
            (isset($validated['target_id']) && !isset($validated['source_id']) && $validated['target_id'] == $taskLink->source_id)
        ) {
            return response()->json(['message' => 'Không thể tạo liên kết đến chính công việc'], 422);
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
    public function destroy(TaskLink $taskLink)
    {
        $taskLink->delete();
        return response()->json(['success' => true]);
    }
}
