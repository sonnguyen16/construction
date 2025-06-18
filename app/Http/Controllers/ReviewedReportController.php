<?php

namespace App\Http\Controllers;

use App\Models\TaskReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReviewedReportController extends Controller
{
    /**
     * Hiển thị danh sách các báo cáo đã được duyệt bởi người dùng hiện tại
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Lấy task_id từ URL nếu có
        $taskId = $request->input('task_id');
        $task = null;
        
        // Nếu có task_id, lấy thông tin task
        if ($taskId) {
            $task = \App\Models\Task::find($taskId);
        }

        $query = TaskReport::with(['task', 'user', 'files'])
            ->where('user_id', $user->id)
            ->orderBy('reviewed_at', 'desc');

        // Lọc theo task_id nếu có
        if ($taskId) {
            $query->where('task_id', $taskId);
        }

        $reports = $query->paginate(10)
            ->through(function ($report) {
                return [
                    'id' => $report->id,
                    'task_id' => $report->task_id,
                    'task_name' => $report->task->name,
                    'user_id' => $report->user_id,
                    'user_name' => $report->user->name,
                    'progress' => $report->progress,
                    'message' => $report->message,
                    'status' => $report->status,
                    'review_message' => $report->review_message,
                    'reviewed_at' => $report->reviewed_at,
                    'files' => $report->files->map(function ($file) {
                        return [
                            'id' => $file->id,
                            'file_name' => $file->file_name,
                            'file_path' => $file->file_path,
                            'file_size' => $file->file_size,
                            'file_type' => $file->file_type,
                        ];
                    }),
                ];
            });

        return Inertia::render('TaskReports/Reviewed', [
            'reports' => $reports,
            'task' => $task,
        ]);
    }
}
