<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class ProjectFileController extends Controller
{
    /**
     * Display file manager for project
     *
     * @param Project $project
     * @return \Inertia\Response
     */
    public function index(Project $project)
    {
        // Đảm bảo dự án tồn tại
        if (!$project) {
            abort(404, 'Dự án không tồn tại');
        }

        // Tạo đường dẫn thư mục của dự án
        $projectFolder = "projects/{$project->code}_" . $project->name;
        $fullPath = "files/1/$projectFolder";

        // Kiểm tra và tạo thư mục nếu chưa tồn tại
        if (!Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->makeDirectory($fullPath);
        }

        // Tạo URL với working_dir để mở đúng thư mục dự án
        $lfm_url = route('unisharp.lfm.show');

        return Inertia::render('Projects/Files', [
            'project' => $project,
            'fileManagerUrl' => $lfm_url,
            'projectFolder' => $projectFolder,
        ]);
    }
}
