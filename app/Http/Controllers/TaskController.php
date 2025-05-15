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
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'text' => $task->name,
                    'start_date' => $task->start_date->format('d-m-Y'),
                    'duration' => $task->duration,
                    'progress' => $task->progress,
                    'parent' => $task->parent_id,
                    'order' => $task->order,
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

        // Xác định order cho task mới
        $maxOrder = Task::where('project_id', $validated['project_id'])
            ->where('parent_id', $validated['parent_id'])
            ->whereNull('deleted_at')
            ->max('order');

        // Nếu không có task cùng cấp, đặt order = 0, ngược lại lấy max + 1
        $validated['order'] = $maxOrder !== null ? $maxOrder + 1 : 0;

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
        $task->deleted_at = now();
        $task->save();

        // Nếu task có parent_id, cập nhật duration của task cha
        if ($task->parent_id) {
            $this->updateParentTaskDuration($task->parent_id);
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
     * Tính toán dựa trên mốc thời gian bắt đầu sớm nhất và kết thúc muộn nhất
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

        // Tìm ngày bắt đầu sớm nhất và ngày kết thúc muộn nhất
        $earliestStartDate = null;
        $latestEndDate = null;

        foreach ($childrenTasks as $task) {
            $startDate = $task->start_date;
            $endDate = (clone $startDate)->addDays($task->duration - 1); // Trừ 1 vì ngày bắt đầu đã tính là 1 ngày

            if ($earliestStartDate === null || $startDate < $earliestStartDate) {
                $earliestStartDate = $startDate;
            }

            if ($latestEndDate === null || $endDate > $latestEndDate) {
                $latestEndDate = $endDate;
            }
        }

        // Tính số ngày giữa ngày bắt đầu sớm nhất và ngày kết thúc muộn nhất
        $duration = $earliestStartDate->diffInDays($latestEndDate) + 1; // Cộng 1 vì tính cả ngày cuối

        // Cập nhật ngày bắt đầu và duration của task cha
        $parentTask->start_date = $earliestStartDate;
        $parentTask->duration = $duration;
        $parentTask->save();

        // Nếu task cha cũng là task con của một task khác, cập nhật tiếp task cha cấp cao hơn
        if ($parentTask->parent_id) {
            $this->updateParentTaskDuration($parentTask->parent_id);
        }
    }

    /**
     * Xử lý việc di chuyển task (kéo thả)
     */
    public function moveTask(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:tasks,id',
            'parent_id' => 'nullable|exists:tasks,id',
            'order' => 'required|integer|min:0',
        ]);

        $task = Task::findOrFail($validated['id']);
        $oldParentId = $task->parent_id;
        $oldOrder = $task->order;
        $newParentId = $validated['parent_id'];
        $newOrder = $validated['order'];

        // Kiểm tra các trường hợp không hợp lệ
        if ($newParentId == $task->id) {
            return response()->json(['message' => 'Công việc không thể là công việc cha của chính nó'], 422);
        }

        // Kiểm tra nếu parent_id mới là con hoặc cháu của task hiện tại
        if ($newParentId) {
            $currentParentId = $newParentId;
            while ($currentParentId) {
                if ($currentParentId == $task->id) {
                    return response()->json(['message' => 'Không thể chuyển công việc thành con của công việc con'], 422);
                }
                
                $parentTask = Task::find($currentParentId);
                if (!$parentTask) break;
                $currentParentId = $parentTask->parent_id;
            }
        }

        // Xử lý xung đột order nếu có
        if ($oldParentId == $newParentId && $oldOrder == $newOrder) {
            // Không có gì thay đổi
            return response()->json(['success' => true]);
        }

        // Tìm task có thể xung đột order
        $conflictTask = Task::where('parent_id', $newParentId)
            ->where('project_id', $task->project_id)
            ->where('id', '!=', $task->id)
            ->where('order', $newOrder)
            ->whereNull('deleted_at')
            ->first();

        // Cập nhật task
        $task->parent_id = $newParentId;
        $task->order = $newOrder;
        $task->save();

        // Nếu có task xung đột, hoán đổi order
        if ($conflictTask) {
            $conflictTask->order = $oldOrder;
            $conflictTask->save();
        }

        // Cập nhật duration của các task cha
        if ($newParentId) {
            $this->updateParentTaskDuration($newParentId);
        }
        
        if ($oldParentId && $oldParentId != $newParentId) {
            $this->updateParentTaskDuration($oldParentId);
        }

        return response()->json(['success' => true]);
    }
}
