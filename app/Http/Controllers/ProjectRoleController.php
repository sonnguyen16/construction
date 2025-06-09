<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use App\Helpers\ProjectPermission;

class ProjectRoleController extends Controller
{
    /**
     * Hiển thị trang quản lý phân quyền theo dự án
     *
     * @param Project $project
     * @return \Inertia\Response
     */
    public function index(Project $project)
    {
        if (!ProjectPermission::hasPermissionInProject('permissions.view', $project->id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem phân quyền dự án.');
        }
        // Lấy danh sách vai trò với quyền và thông tin scope
        $roles = Role::with(['permissions' => function($query) {
            $query->select('permissions.id', 'permissions.name', 'permissions.guard_name', 'permissions.scope');
        }])->get();

        // Lấy danh sách người dùng
        $users = User::all();

        // Lấy danh sách phân quyền hiện tại trong dự án
        $projectRoles = ProjectRole::with(['user', 'role'])
            ->where('project_id', $project->id)
            ->get()
            ->map(function ($projectRole) {
                return [
                    'id' => $projectRole->id,
                    'user_id' => $projectRole->user_id,
                    'user_name' => $projectRole->user->name,
                    'role_id' => $projectRole->role_id,
                    'role_name' => $projectRole->role->name,
                ];
            });

        return Inertia::render('Projects/Roles', [
            'project' => $project,
            'roles' => $roles,
            'users' => $users,
            'projectRoles' => $projectRoles,
        ]);
    }

    /**
     * Gán vai trò cho người dùng trong dự án
     *
     * @param Request $request
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Project $project)
    {
        if (!ProjectPermission::hasPermissionInProject('permissions.assign', $project->id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền gán vai trò.');
        }
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        // Kiểm tra xem người dùng đã có vai trò trong dự án chưa
        $existingRole = ProjectRole::where('user_id', $validated['user_id'])
            ->where('project_id', $project->id)
            ->first();

        if ($existingRole) {
            // Nếu đã có vai trò, cập nhật vai trò mới
            $existingRole->role_id = $validated['role_id'];
            $existingRole->save();
        } else {
            // Nếu chưa có, tạo mới
            ProjectRole::create([
                'user_id' => $validated['user_id'],
                'project_id' => $project->id,
                'role_id' => $validated['role_id'],
            ]);
        }

        return redirect()->route('projects.roles.index', $project->id)
            ->with('success', 'Phân quyền cho người dùng trong dự án đã được cập nhật!');
    }

    /**
     * Xóa phân quyền của người dùng trong dự án
     *
     * @param Project $project
     * @param ProjectRole $projectRole
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Project $project, ProjectRole $projectRole)
    {
        if (!ProjectPermission::hasPermissionInProject('permissions.assign', $project->id)) {
            return redirect()->back()->with('error', 'Bạn không có quyền gán vai trò.');
        }
        // Kiểm tra xem projectRole có thuộc về dự án này không
        if ($projectRole->project_id !== $project->id) {
            abort(404);
        }

        // Xóa phân quyền
        $projectRole->delete();

        return redirect()->route('projects.roles.index', $project->id)
            ->with('success', 'Phân quyền đã được xóa!');
    }
}
