<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use App\Models\ProjectRole;
use App\Models\Project;
use Spatie\Permission\Models\Role;

class UserProjectRoleController extends Controller
{
    /**
     * Thay đổi dự án và vai trò hiện tại của người dùng
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function changeProjectRole(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Lưu dự án và vai trò hiện tại vào session
        Session::put('current_project_id', $request->project_id);
        Session::put('current_role_id', $request->role_id);
        
        // Kiểm tra xem người dùng có vai trò này trong dự án không
        $projectRole = ProjectRole::where('user_id', $request->user()->id)
            ->where('project_id', $request->project_id)
            ->where('role_id', $request->role_id)
            ->first();
            
        if (!$projectRole) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập vào dự án này với vai trò đã chọn.'
                ], 403);
            }
            
            return redirect()->back()->with('error', 'Bạn không có quyền truy cập vào dự án này với vai trò đã chọn.');
        }

        // Nếu là request API, trả về JSON
        if ($request->wantsJson() || $request->ajax()) {
            // Lấy thông tin dự án và vai trò
            $project = Project::find($request->project_id);
            $role = Role::find($request->role_id);
            
            // Lấy danh sách quyền của vai trò
            $permissions = $role->permissions->pluck('name')->toArray();
            
            return response()->json([
                'success' => true,
                'message' => 'Thay đổi dự án và vai trò thành công',
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                ],
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
                'permissions' => $permissions
            ]);
        }
        
        // Nếu là request thông thường, trả về redirect
        return redirect()->back()->with('success', 'Thay đổi dự án và vai trò thành công');
    }
}
