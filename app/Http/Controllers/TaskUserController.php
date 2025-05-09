<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskUserController extends Controller
{
    /**
     * Lấy danh sách người dùng của công việc
     *
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Task $task)
    {
        $users = $task->users()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'role' => $user->pivot->role,
                    'duration' => $user->pivot->duration,
                    'role_name' => $this->getRoleName($user->pivot->role),
                ];
            });

        return response()->json($users);
    }

    /**
     * Thêm người dùng vào công việc
     *
     * @param Request $request
     * @param Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'duration' => 'required|integer|min:1',
            'role' => 'required|integer|min:0|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Kiểm tra xem người dùng đã được thêm vào công việc với vai trò này chưa
        $existingUser = $task->users()
            ->wherePivot('user_id', $request->user_id)
            ->wherePivot('role', $request->role)
            ->first();

        if ($existingUser) {
            return response()->json(['message' => 'Người dùng đã được thêm vào công việc với vai trò này'], 422);
        }

        // Thêm người dùng vào công việc
        $task->users()->attach($request->user_id, [
            'duration' => $request->duration,
            'role' => $request->role,
            'created_by' => auth()->id(),
        ]);

        // Lấy thông tin người dùng vừa thêm
        $user = User::find($request->user_id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'role' => $request->role,
            'duration' => $request->duration,
            'role_name' => $this->getRoleName($request->role),
        ]);
    }

    /**
     * Xóa người dùng khỏi công việc
     *
     * @param Task $task
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task, User $user, Request $request)
    {
        $role = $request->query('role', null);

        if ($role !== null) {
            // Xóa người dùng khỏi công việc với vai trò cụ thể
            $task->users()->wherePivot('user_id', $user->id)->wherePivot('role', $role)->detach();
        } else {
            // Xóa người dùng khỏi công việc với tất cả vai trò
            $task->users()->detach($user->id);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Lấy tên vai trò từ mã vai trò
     *
     * @param int $role
     * @return string
     */
    private function getRoleName($role)
    {
        switch ($role) {
            case 0:
                return 'Người thực hiện';
            case 1:
                return 'Người phụ trách';
            case 2:
                return 'Người giám sát';
            default:
                return 'Không xác định';
        }
    }
}
