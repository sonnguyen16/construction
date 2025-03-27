<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BidPackageController;
use App\Http\Controllers\BidController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReceiptVoucherController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
// Đăng nhập
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// group with middleware auth
Route::middleware('auth')->group(function () {
    // Trang chủ
    Route::get('/', [HomeController::class, 'index'])->name('home');
    // Quản lý người dùng
    Route::resource('users', UserController::class);
    // Quản lý nhà thầu
    Route::resource('contractors', ContractorController::class);
    // Quản lý dự án
    Route::resource('projects', ProjectController::class);
    // Quản lý gói thầu
    Route::post('projects/{project}/bid-packages', [BidPackageController::class, 'store'])->name('bid-packages.store');
    Route::put('bid-packages/{bidPackage}', [BidPackageController::class, 'update'])->name('bid-packages.update');
    Route::delete('bid-packages/{bidPackage}', [BidPackageController::class, 'destroy'])->name('bid-packages.destroy');
    // Quản lý giá dự thầu
    Route::post('bid-packages/{bidPackage}/bids', [BidController::class, 'store'])->name('bids.store');
    Route::put('bids/{bid}', [BidController::class, 'update'])->name('bids.update');
    Route::delete('bids/{bid}', [BidController::class, 'destroy'])->name('bids.destroy');
    Route::post('bids/{bid}/select-contractor', [BidController::class, 'selectContractor'])->name('bids.select-contractor');
    // Routes cho quản lý khách hàng
    Route::resource('customers', CustomerController::class);
    // Quản lý phiếu chi
    Route::resource('payment-vouchers', PaymentVoucherController::class);
    // Routes cho quản lý phiếu thu
    Route::resource('receipt-vouchers', ReceiptVoucherController::class);
    // Route cập nhật trạng thái phiếu thu
    Route::patch('/receipt-vouchers/{receipt_voucher}/update-status', [ReceiptVoucherController::class, 'updateStatus'])->name('receipt-vouchers.update-status');
    // Route cập nhật trạng thái phiếu chi
    Route::patch('/payment-vouchers/{payment_voucher}/update-status', [PaymentVoucherController::class, 'updateStatus'])->name('payment-vouchers.update-status');
    // Thêm route cho các trang chi phí và lợi nhuận
    Route::get('/projects/{project}/expenses', [ProjectController::class, 'expenses'])->name('projects.expenses');
    Route::get('/projects/{project}/profit', [ProjectController::class, 'profit'])->name('projects.profit');
    // Thêm route cập nhật giá trị phát sinh
    Route::patch('/bid-packages/{bidPackage}/update-additional-price', [BidPackageController::class, 'updateAdditionalPrice'])
        ->name('bid-packages.update-additional-price');
    // Thêm route cập nhật tỷ lệ lợi nhuận
    Route::patch('/bid-packages/{bidPackage}/update-profit-percentage', [BidPackageController::class, 'updateProfitPercentage'])
        ->name('bid-packages.update-profit-percentage');
});

// File Manager Routes
Route::group(['prefix' => 'laravel-filemanager', 'middleware' => ['web', 'auth']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// Project File Routes
Route::get('/projects/{project}/files', [App\Http\Controllers\ProjectFileController::class, 'index'])->name('projects.files');

// Bid Package File Routes
Route::get('/bid-packages/{bidPackage}/files', [App\Http\Controllers\BidPackageFileController::class, 'index'])->name('bid-packages.files');






