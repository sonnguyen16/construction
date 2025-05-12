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

Route::get('/projects', function () {
    return App\Models\Project::select('id', 'name')->whereNull('deleted_at')->orderBy('name')->get();
});

Route::get('/users', function () {
    return App\Models\User::select('id', 'name', 'email', 'avatar')->orderBy('name')->get();
});

Route::get('/products', function () {
    return App\Models\Product::with(['unit', 'category'])->orderBy('name')->get();
});
