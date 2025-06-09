<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectPermission
{
    /**
     * Kiểm tra xem người dùng có quyền global trong bất kỳ dự án nào không
     * 
     * @param string|array $permissions Tên quyền hoặc mảng tên quyền cần kiểm tra
     * @return bool
     */
    public static function hasGlobalPermission($permissions)
    {
        $user = Auth::user();
        
        // Lấy tất cả vai trò của người dùng trong các dự án
        $roleIds = DB::table('project_roles')
            ->where('user_id', $user->id)
            ->pluck('role_id')
            ->toArray();
            
        if (empty($roleIds)) {
            return false;
        }
        
        // Tạo query để lấy quyền global của các vai trò
        $query = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds)
            ->where('permissions.scope', 'global');
            
        if (is_array($permissions)) {
            $query->whereIn('permissions.name', $permissions);
        } else {
            $query->where('permissions.name', $permissions);
        }
        
        // Kiểm tra xem có quyền nào thỏa mãn không
        return $query->exists();
    }
    /**
     * Kiểm tra xem người dùng có vai trò Super Admin trong dự án không
     *
     * @param int $projectId ID của dự án
     * @return bool
     */
    public static function isSuperAdminInProject($projectId)
    {
        $user = Auth::user();

        return DB::table('project_roles')
            ->join('roles', 'project_roles.role_id', '=', 'roles.id')
            ->where('project_roles.user_id', $user->id)
            ->where('project_roles.project_id', $projectId)
            ->where('roles.name', 'Super Admin')
            ->exists();
    }

    /**
     * Kiểm tra xem người dùng có quyền trong dự án không
     *
     * @param int $projectId ID của dự án
     * @return bool
     */
    public static function hasAccessToProject($projectId)
    {
        $user = Auth::user();

        return DB::table('project_roles')
            ->where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Kiểm tra xem người dùng có vai trò cụ thể trong dự án không
     *
     * @param string|array $roles Tên vai trò hoặc mảng tên vai trò
     * @param int $projectId ID của dự án
     * @return bool
     */
    public static function hasRoleInProject($roles, $projectId)
    {
        $user = Auth::user();

        $query = DB::table('project_roles')
            ->join('roles', 'project_roles.role_id', '=', 'roles.id')
            ->where('project_roles.user_id', $user->id)
            ->where('project_roles.project_id', $projectId);

        if (is_array($roles)) {
            $query->whereIn('roles.name', $roles);
        } else {
            $query->where('roles.name', $roles);
        }

        return $query->exists();
    }

    /**
     * Kiểm tra xem người dùng có quyền cụ thể trong dự án không
     *
     * @param string|array $permissions Tên quyền hoặc mảng tên quyền
     * @param int $projectId ID của dự án
     * @return bool
     */
    public static function hasPermissionInProject($permissions, $projectId)
    {
        $user = Auth::user();

        // Lấy vai trò của người dùng trong dự án
        $roleIds = DB::table('project_roles')
            ->where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->pluck('role_id')
            ->toArray();

        if (empty($roleIds)) {
            return false;
        }

        // Lấy quyền của các vai trò
        $rolePermissions = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds)
            ->pluck('permissions.name')
            ->toArray();

        // Kiểm tra quyền
        if (is_array($permissions)) {
            foreach ($permissions as $permission) {
                if (in_array($permission, $rolePermissions)) {
                    return true;
                }
            }
            return false;
        }

        return in_array($permissions, $rolePermissions);
    }

    /**
     * Lấy danh sách ID dự án mà người dùng có quyền truy cập
     *
     * @return array
     */
    public static function getAccessibleProjectIds()
    {
        $user = Auth::user();

        return DB::table('project_roles')
            ->where('user_id', $user->id)
            ->pluck('project_id')
            ->toArray();
    }

    /**
     * Lấy danh sách quyền của người dùng trong dự án
     *
     * @param int $projectId ID của dự án
     * @return array
     */
    public static function getUserPermissionsInProject($projectId)
    {
        $user = Auth::user();

        // Lấy vai trò của người dùng trong dự án
        $roleIds = DB::table('project_roles')
            ->where('user_id', $user->id)
            ->where('project_id', $projectId)
            ->pluck('role_id')
            ->toArray();

        if (empty($roleIds)) {
            return [];
        }

        // Lấy quyền của các vai trò
        return DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds)
            ->pluck('permissions.name')
            ->toArray();
    }

    /**
     * Lấy danh sách các dự án mà người dùng có quyền cụ thể
     *
     * @param string|array $permissions Tên quyền hoặc mảng tên quyền
     * @return array Mảng chứa thông tin dự án và quyền của người dùng trong dự án
     */
    public static function getProjectsWithPermission($permissions)
    {
        $user = Auth::user();

        // Lấy danh sách các vai trò và dự án mà người dùng có quyền truy cập
        $userProjectRoles = DB::table('project_roles')
            ->where('user_id', $user->id)
            ->select('role_id', 'project_id')
            ->get();

        if ($userProjectRoles->isEmpty()) {
            return [];
        }

        // Tạo mảng chứa các role_id và mảng ánh xạ role_id với project_id
        $roleIds = [];
        $roleProjectMap = [];
        
        foreach ($userProjectRoles as $projectRole) {
            $roleIds[] = $projectRole->role_id;
            
            if (!isset($roleProjectMap[$projectRole->role_id])) {
                $roleProjectMap[$projectRole->role_id] = [];
            }
            
            $roleProjectMap[$projectRole->role_id][] = $projectRole->project_id;
        }

        // Lấy danh sách các vai trò có quyền cụ thể
        $query = DB::table('role_has_permissions')
            ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('role_has_permissions.role_id', $roleIds);

        if (is_array($permissions)) {
            $query->whereIn('permissions.name', $permissions);
        } else {
            $query->where('permissions.name', $permissions);
        }

        $rolesWithPermission = $query->pluck('role_has_permissions.role_id')->unique()->toArray();

        // Lấy danh sách ID các dự án dựa trên vai trò có quyền
        $projectIds = [];
        foreach ($rolesWithPermission as $roleId) {
            if (isset($roleProjectMap[$roleId])) {
                $projectIds = array_merge($projectIds, $roleProjectMap[$roleId]);
            }
        }

        return array_unique($projectIds);
    }
}
