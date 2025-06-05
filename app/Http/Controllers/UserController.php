<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng
     */
    public function index(Request $request)
    {
        $query = User::query()->whereNull('deleted_at');

        // Tìm kiếm
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        $users = $query->latest()->paginate(8)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => $request->only('search')
        ]);
    }

    /**
     * Hiển thị form tạo người dùng mới
     */
    public function create()
    {
        return Inertia::render('Users/Create');
    }

    /**
     * Lưu người dùng mới vào database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = new User();
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->password = Hash::make($validated['password']);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = Storage::url($avatarPath);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Người dùng đã được tạo thành công!');
    }

    /**
     * Hiển thị form chỉnh sửa người dùng
     */
    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
            'avatar' => $user->avatar,
        ]);
    }

    /**
     * Cập nhật thông tin người dùng
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists(str_replace('/storage', '', $user->avatar))) {
                Storage::disk('public')->delete(str_replace('/storage', '', $user->avatar));
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = Storage::url($avatarPath);
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Thông tin người dùng đã được cập nhật.');
    }

    /**
     * Xóa người dùng
     */
    public function destroy(User $user)
    {
        // Xóa avatar nếu có
        if ($user->avatar && Storage::disk('public')->exists(str_replace('/storage', '', $user->avatar))) {
            Storage::disk('public')->delete(str_replace('/storage', '', $user->avatar));
        }

        $user->deleted_at = now();
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Người dùng đã được xóa thành công.');
    }
}
