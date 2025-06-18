<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ProjectPermission;

class TaskController extends Controller
{
    /**
     * Lấy danh sách công việc được giao cho người dùng đăng nhập
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAssignedTasks()
    {
        $user = Auth::user();

        // Lấy danh sách công việc được giao cho người dùng
        $tasks = Task::whereHas('assignees', function($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['project', 'latestReport', 'latestReport.user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($tasks);
    }
    
    /**
     * Lấy thông tin chi tiết của một task
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {
        $user = Auth::user();
        $projectPermission = new ProjectPermission();
        
        // Kiểm tra quyền truy cập task
        if (!$task->assignees->contains($user->id) && 
            !$projectPermission->hasPermissionInProject('task.view', $task->project_id, $user->id)) {
            return response()->json(['message' => 'Bạn không có quyền truy cập công việc này'], 403);
        }
        
        // Lấy thông tin chi tiết của task
        $task->load(['project', 'assignees', 'latestReport']);
        
        return response()->json($task);
    }
}
