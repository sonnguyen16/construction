<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Helpers\ProjectPermission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Hiển thị trang quản lý công việc
     */
    public function index(Request $request)
    {
        // Lấy danh sách các dự án mà người dùng có quyền xem công việc
        $projectIds = ProjectPermission::getProjectsWithPermission('tasks.view');

        // Lấy tất cả các dự án mà người dùng có quyền
        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Kiểm tra nếu có project_id trong query param
        $projectId = $request->query('project_id');
        $defaultProject = null;

        if ($projectId) {
            // Kiểm tra nếu người dùng có quyền xem dự án được chọn
            if (!in_array($projectId, $projectIds)) {
                return redirect()->route('tasks.index')->with('error', 'Bạn không có quyền xem công việc của dự án này!');
            }

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
        // Kiểm tra nếu người dùng có quyền xem công việc của dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.view', $project->id)) {
            return response()->json(['error' => 'Bạn không có quyền xem công việc của dự án này!'], 403);
        }

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
                    'users' => $task->users()->get(),
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

        // Kiểm tra nếu người dùng có quyền tạo công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.create', $validated['project_id'])) {
            return response()->json(['error' => 'Bạn không có quyền tạo công việc trong dự án này!'], 403);
        }

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
        // Kiểm tra nếu người dùng có quyền cập nhật công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.edit', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền cập nhật công việc trong dự án này!'], 403);
        }

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
     * Xóa công việc (soft delete)
     */
    public function destroy(Task $task)
    {
        // Kiểm tra nếu người dùng có quyền xóa công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.delete', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền xóa công việc trong dự án này!'], 403);
        }

        $task->deleted_at = now();
        $task->save();

        // Nếu task có parent_id, cập nhật duration của task cha
        if ($task->parent_id) {
            $this->updateParentTaskDuration($task->parent_id);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Hiển thị trang thùng rác công việc
     */
    public function trash(Request $request)
    {
        // Lấy danh sách các dự án mà người dùng có quyền xem công việc
        $projectIds = ProjectPermission::getProjectsWithPermission('tasks.trash');

        // Lấy tất cả các dự án mà người dùng có quyền
        $projects = Project::query()
            ->whereIn('id', $projectIds)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Kiểm tra nếu có project_id trong query param
        $projectId = $request->query('project_id');
        $defaultProject = null;

        if ($projectId) {
            // Kiểm tra nếu người dùng có quyền xem dự án được chọn
            if (!in_array($projectId, $projectIds)) {
                return redirect()->route('tasks.trash')->with('error', 'Bạn không có quyền xem công việc đã xóa của dự án này!');
            }

            // Tìm dự án theo ID
            $defaultProject = $projects->firstWhere('id', $projectId);
        }

        // Nếu không có project_id hoặc không tìm thấy dự án, chọn dự án đầu tiên
        if (!$defaultProject && $projects->isNotEmpty()) {
            $defaultProject = $projects->first();
        }

        return Inertia::render('Tasks/Trash', [
            'projects' => $projects,
            'defaultProject' => $defaultProject
        ]);
    }

    /**
     * Lấy danh sách công việc đã xóa theo dự án
     */
    public function getDeletedTasksByProject(Project $project)
    {
        // Kiểm tra nếu người dùng có quyền xem công việc đã xóa của dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.trash', $project->id)) {
            return redirect()->route('tasks.trash')->with('error', 'Bạn không có quyền xem công việc đã xóa của dự án này!');
        }

        // Lấy tất cả các công việc đã xóa của dự án
        $tasks = Task::withTrashed()
            ->where('project_id', $project->id)
            ->whereNotNull('deleted_at')
            ->orderBy('deleted_at', 'desc')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'text' => $task->name,
                    'start_date' => $task->start_date->format('d-m-Y'),
                    'duration' => $task->duration,
                    'progress' => $task->progress,
                    'parent' => $task->parent_id,
                    'deleted_at' => $task->deleted_at->format('d-m-Y H:i:s'),
                    'order' => $task->order
                ];
            });

        return response()->json([
            'data' => $tasks
        ]);
    }

    /**
     * Khôi phục công việc đã xóa
     */
    public function restore($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        // Kiểm tra nếu người dùng có quyền khôi phục công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.trash', $task->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền khôi phục công việc trong dự án này!');
        }

        $task->deleted_at = null;
        $task->save();

        // Nếu task có parent_id, cập nhật duration của task cha
        if ($task->parent_id) {
            $this->updateParentTaskDuration($task->parent_id);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Xóa vĩnh viễn công việc
     */
    public function forceDelete($id)
    {
        $task = Task::withTrashed()->findOrFail($id);

        // Kiểm tra nếu người dùng có quyền xóa vĩnh viễn công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.trash', $task->project_id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa vĩnh viễn công việc trong dự án này!');
        }

        $parentId = $task->parent_id;

        // Xóa vĩnh viễn task
        $task->forceDelete();

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
        // Kiểm tra nếu người dùng có quyền xem chi tiết công việc trong dự án
        if (!ProjectPermission::hasPermissionInProject('tasks.view', $task->project_id)) {
            return response()->json(['error' => 'Bạn không có quyền xem chi tiết công việc trong dự án này!'], 403);
        }

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
     * Cập nhật vị trí của tất cả các task
     */
    public function updateAllPositions(Request $request)
    {
        $validated = $request->validate([
            'tasks' => 'required|array',
            'tasks.*.id' => 'required|exists:tasks,id',
            'tasks.*.parent_id' => 'nullable|exists:tasks,id',
            'tasks.*.order' => 'required|integer',
        ]);

        // Lấy task đầu tiên để kiểm tra quyền
        if (count($validated['tasks']) > 0) {
            $firstTask = Task::find($validated['tasks'][0]['id']);

            // Kiểm tra nếu người dùng có quyền di chuyển công việc trong dự án
            if (!ProjectPermission::hasPermissionInProject('tasks.edit', $firstTask->project_id)) {
                return response()->json(['error' => 'Bạn không có quyền di chuyển công việc trong dự án này!'], 403);
            }

            // Lưu project_id để kiểm tra các task khác
            $projectId = $firstTask->project_id;
        } else {
            return response()->json(['success' => true]);
        }

        // Bắt đầu transaction để đảm bảo tính toàn vẹn dữ liệu
        DB::beginTransaction();

        try {
            // Mảng lưu các parent_id cần cập nhật duration
            $parentsToUpdate = [];

            foreach ($validated['tasks'] as $taskData) {
                $task = Task::find($taskData['id']);

                // Kiểm tra xem task có thuộc cùng dự án không
                if ($task->project_id != $projectId) {
                    continue;
                }

                // Lưu parent_id cũ để cập nhật duration sau
                if ($task->parent_id != $taskData['parent_id'] && $task->parent_id) {
                    $parentsToUpdate[$task->parent_id] = true;
                }

                // Lưu parent_id mới để cập nhật duration sau
                if ($taskData['parent_id']) {
                    $parentsToUpdate[$taskData['parent_id']] = true;
                }

                // Cập nhật task
                $task->parent_id = $taskData['parent_id'];
                $task->order = $taskData['order'];
                $task->save();
            }

            // Cập nhật duration cho tất cả các task cha bị ảnh hưởng
            foreach (array_keys($parentsToUpdate) as $parentId) {
                $this->updateParentTaskDuration($parentId);
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi khi cập nhật vị trí công việc: ' . $e->getMessage()], 500);
        }
    }
}
