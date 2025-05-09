<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TaskFileController extends Controller
{
    /**
     * Hiển thị trình quản lý file cho công việc
     *
     * @param Task $task
     * @return \Inertia\Response
     */
    public function index(Task $task)
    {
        // Đảm bảo công việc tồn tại
        if (!$task) {
            abort(404, 'Công việc không tồn tại');
        }

        // Load dự án để hiển thị thông tin
        $task->load('project');

        // Tạo đường dẫn thư mục của công việc
        $taskFolder = $task->folder_path;
        $fullPath = "files/1/$taskFolder";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->makeDirectory($fullPath);
        }

        // Tạo URL với working_dir để mở đúng thư mục công việc
        $lfm_url = route('unisharp.lfm.show');

        return Inertia::render('Tasks/Files', [
            'task' => $task,
            'project' => $task->project,
            'fileManagerUrl' => $lfm_url,
            'taskFolder' => $taskFolder,
        ]);
    }
}
