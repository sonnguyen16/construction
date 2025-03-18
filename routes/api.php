<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Contractor;
use App\Models\Project;
use App\Http\Controllers\Api\ProjectBidPackageController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API để lấy danh sách nhà thầu
Route::get('/contractors', function () {
    return Contractor::orderBy('name')->get();
});

Route::get('/projects/{project}/bid-packages', [ProjectBidPackageController::class, 'index'])
    ->name('api.project.bid-packages');
