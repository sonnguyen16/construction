<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Lấy danh sách dự án cho dropdown
     */
    public function list()
    {
        $user = Auth::user();
        
        // Lấy danh sách dự án mà người dùng có quyền truy cập
        $projects = Project::whereHas('projectRoles', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->select('id', 'name')
        ->orderBy('name')
        ->get();
        
        return response()->json($projects);
    }
}
