<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Helpers\ProjectPermission;

class RoleController extends Controller
{
    /**
     * Hiển thị danh sách vai trò
     */
    public function index()
    {
        // Kiểm tra quyền global để xem danh sách vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem danh sách vai trò');
        }
        
        $roles = Role::with('permissions')
            ->withCount('users')
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles
        ]);
    }

    /**
     * Hiển thị form tạo vai trò mới
     */
    public function create()
    {
        // Kiểm tra quyền global để tạo vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo vai trò');
        }
        
        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
            'modules' => array_keys($permissions->toArray())
        ]);
    }

    /**
     * Lưu vai trò mới
     */
    public function store(Request $request)
    {
        // Kiểm tra quyền global để tạo vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.create')) {
            return redirect()->back()->with('error', 'Bạn không có quyền tạo vai trò');
        }
        
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'required|array'
        ]);

        if ($request->name === 'Super Admin') {
            return redirect()->back()
                ->with('error', 'Vai trò Super Admin đã tồn tại!');
        }

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Vai trò đã được tạo thành công!');
    }

    /**
     * Hiển thị chi tiết vai trò
     */
    public function show(Role $role)
    {
        // Kiểm tra quyền global để xem chi tiết vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.view')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xem chi tiết vai trò');
        }
        
        $role->load('permissions');
        $users = User::role($role->name)->get();

        return Inertia::render('Roles/Show', [
            'role' => $role,
            'users' => $users
        ]);
    }

    /**
     * Hiển thị form chỉnh sửa vai trò
     */
    public function edit(Role $role)
    {
        // Kiểm tra quyền global để sửa vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa vai trò');
        }
        
        $role->load('permissions');

        $permissions = Permission::all()->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });

        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'modules' => array_keys($permissions->toArray()),
            'rolePermissions' => $rolePermissions
        ]);
    }

    /**
     * Cập nhật vai trò
     */
    public function update(Request $request, Role $role)
    {
        // Kiểm tra quyền global để sửa vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.edit')) {
            return redirect()->back()->with('error', 'Bạn không có quyền sửa vai trò');
        }
        
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'required|array'
        ]);

        // Không cho phép chỉnh sửa vai trò Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->back()
                ->with('error', 'Không thể chỉnh sửa vai trò Super Admin!');
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        return redirect()->route('roles.index')
            ->with('success', 'Vai trò đã được cập nhật thành công!');
    }

    /**
     * Xóa vai trò
     */
    public function destroy(Role $role)
    {
        // Kiểm tra quyền global để xóa vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.delete')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa vai trò');
        }
        
        // Không cho phép xóa vai trò Super Admin
        if ($role->name === 'Super Admin') {
            return redirect()->back()
                ->with('error', 'Không thể xóa vai trò Super Admin!');
        }

        // Kiểm tra xem vai trò có đang được gán cho người dùng nào không
        $usersCount = User::role($role->name)->count();

        if ($usersCount > 0) {
            return redirect()->back()
                ->with('error', 'Không thể xóa vai trò đang được sử dụng bởi ' . $usersCount . ' người dùng!');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Vai trò đã được xóa thành công!');
    }

    /**
     * Gán vai trò cho người dùng
     */
    public function assignUsers(Request $request, Role $role)
    {
        // Kiểm tra quyền global để gán vai trò cho người dùng
        if (!ProjectPermission::hasGlobalPermission('roles.assign')) {
            return redirect()->back()->with('error', 'Bạn không có quyền gán vai trò cho người dùng');
        }
        
        $request->validate([
            'users' => 'required|array'
        ]);

        foreach ($request->users as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->syncRoles([$role->name]);
            }
        }

        return redirect()->route('roles.show', $role)
            ->with('success', 'Vai trò đã được gán cho người dùng thành công!');
    }

    /**
     * Xóa người dùng khỏi vai trò
     */
    public function removeUser(Role $role, User $user)
    {
        // Kiểm tra quyền global để xóa người dùng khỏi vai trò
        if (!ProjectPermission::hasGlobalPermission('roles.assign')) {
            return redirect()->back()->with('error', 'Bạn không có quyền xóa người dùng khỏi vai trò');
        }
        
        // Không cho phép xóa Super Admin khỏi người dùng có ID = 1
        if ($role->name === 'Super Admin' && $user->id === 1) {
            return redirect()->route('roles.show', $role)
                ->with('error', 'Không thể xóa vai trò Super Admin khỏi người dùng chính!');
        }

        // Xóa vai trò khỏi người dùng
        $user->removeRole($role);

        return redirect()->route('roles.show', $role)
            ->with('success', 'Người dùng đã được xóa khỏi vai trò thành công!');
    }
}
