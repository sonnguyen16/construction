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
    public function index()
    {
        // Lấy tất cả các dự án
        $projects = Project::whereNull('deleted_at')->orderBy('name')->get();

        // Mặc định chọn dự án đầu tiên nếu có
        $defaultProject = $projects->first();

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

        $task->update($validated);

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
        $task->delete();
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
}
