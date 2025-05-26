<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Lấy danh sách người dùng cho API
     */
    public function index()
    {
        $users = User::with('roles')->get();
        
        return response()->json($users);
    }
}
