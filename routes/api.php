<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TaskController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Lấy danh sách người dùng cho chức năng gán vai trò
    Route::get('/users', [UserController::class, 'index']);

    // Lấy danh sách công việc được giao cho người dùng đăng nhập
    Route::get('/tasks/assigned', [TaskController::class, 'getAssignedTasks']);
    
    // Lấy thông tin chi tiết của một task
    Route::get('/tasks/{task}', [\App\Http\Controllers\Api\TaskController::class, 'show']);
    
    // Nộp báo cáo công việc
    Route::post('/projects/{project}/tasks/{task}/reports', [\App\Http\Controllers\TaskReportController::class, 'store']);

    Route::get('/products', function () {
        return Product::with(['unit', 'category'])->orderBy('name')->get();
    });
});


