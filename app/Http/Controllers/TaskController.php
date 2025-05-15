<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Hiển thị trang quản lý công việc
     */
    public function index(Request $request)
    {
        // Lấy tất cả các dự án
        $projects = Project::query()->whereNull('deleted_at')->orderBy('name')->get();

        // Kiểm tra nếu có project_id trong query param
        $projectId = $request->query('project_id');
        $defaultProject = null;

        if ($projectId) {
            // Tìm dự án theo ID
            $defaultProject = $projects->firstWhere('id', $projectId);
        }

        // Nếu không có project_id hoặc không tìm thấy dự án, chọn dự án đầu tiên
        if (!$defaultProject && $projects->isNotEmpty()) {
            $defaultProject = $projects->first();
        }

        return Inertia::render('Tasks/Index', [
            'projects' => $projects,
            'defaultProject' => $defaultProject
        ]);
    }

    /**
     * Lấy danh sách công việc theo dự án
     */
    public function getTasksByProject(Project $project)
    {
        // Lấy tất cả các công việc của dự án
        $tasks = Task::where('project_id', $project->id)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'text' => $task->name,
                    'start_date' => $task->start_date->format('d-m-Y'),
                    'duration' => $task->duration,
                    'progress' => $task->progress,
                    'parent' => $task->parent_id,
                    'open' => true
                ];
            });

        // Lấy tất cả các liên kết giữa các công việc
        $links = $project->tasks()
            ->with('successors')
            ->get()
            ->flatMap(function ($task) {
                return $task->successors->map(function ($link) {
                    return [
                        'id' => $link->id,
                        'source' => $link->source_id,
                        'target' => $link->target_id,
                        'type' => $link->type
                    ];
                });
            });

        return response()->json([
            'data' => $tasks,
            'links' => $links
        ]);
    }

    /**
     * Tạo mới công việc
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required',
            'duration' => 'required|integer|min:1',
            'progress' => 'required|numeric|min:0|max:1',
            'project_id' => 'required|exists:projects,id',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        $task = Task::create($validated);

        // Nếu task có parent_id, cập nhật duration của task cha
        if ($task->parent_id) {
            $this->updateParentTaskDuration($task->parent_id);
        }

        return response()->json([
            'id' => $task->id,
            'text' => $task->name,
            'start_date' => $task->start_date,
            'duration' => $task->duration,
            'progress' => $task->progress,
            'parent' => $task->parent_id,
            'open' => true
        ]);
    }

    /**
     * Cập nhật công việc
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'start_date' => 'sometimes|required',
            'duration' => 'sometimes|required|integer|min:1',
            'progress' => 'sometimes|required|numeric|min:0|max:1',
            'parent_id' => 'nullable|exists:tasks,id',
        ]);

        // Kiểm tra nếu parent_id trùng với id của task hiện tại
        if (isset($validated['parent_id']) && $validated['parent_id'] == $task->id) {
            return response()->json(['message' => 'Công việc không thể là công việc cha của chính nó'], 422);
        }

        // Lưu parent_id cũ trước khi cập nhật
        $oldParentId = $task->parent_id;

        $task->update($validated);

        // Nếu task có parent_id mới, cập nhật duration của task cha mới
        if ($task->parent_id) {
            $this->updateParentTaskDuration($task->parent_id);
        }

        // Nếu parent_id đã thay đổi và parent_id cũ không rỗng, cập nhật duration của task cha cũ
        if ($oldParentId && $oldParentId != $task->parent_id) {
            $this->updateParentTaskDuration($oldParentId);
        }

        return response()->json([
            'id' => $task->id,
            'text' => $task->name,
            'start_date' => $task->start_date,
            'duration' => $task->duration,
            'progress' => $task->progress,
            'parent' => $task->parent_id,
            'open' => true
        ]);
    }

    /**
     * Xóa công việc
     */
    public function destroy(Task $task)
    {
        // Lưu parent_id trước khi xóa
        $parentId = $task->parent_id;

        $task->delete();

        // Nếu task có parent_id, cập nhật duration của task cha
        if ($parentId) {
            $this->updateParentTaskDuration($parentId);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Hiển thị chi tiết công việc
     */
    public function show(Task $task)
    {
        // Load dự án và các quan hệ liên quan
        $task->load(['project', 'parent', 'creator', 'updater']);

        return Inertia::render('Tasks/TaskDetail', [
            'task' => $task,
            'project' => $task->project
        ]);
    }

    /**
     * Cập nhật tổng số ngày của task cha dựa trên các task con
     */
    private function updateParentTaskDuration($parentId)
    {
        $parentTask = Task::findOrFail($parentId);

        // Lấy tất cả các task con chưa bị xóa
        $childrenTasks = Task::where('parent_id', $parentId)
            ->whereNull('deleted_at')
            ->get();

        if ($childrenTasks->isEmpty()) {
            return; // Không có task con, giữ nguyên duration của task cha
        }

        // Tính tổng duration của các task con
        $totalDuration = $childrenTasks->sum('duration');

        // Cập nhật duration của task cha
        $parentTask->duration = $totalDuration;
        $parentTask->save();

        // Nếu task cha cũng là task con của một task khác, cập nhật tiếp task cha cấp cao hơn
        if ($parentTask->parent_id) {
            $this->updateParentTaskDuration($parentTask->parent_id);
        }
    }
}
