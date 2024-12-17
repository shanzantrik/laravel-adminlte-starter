<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BookingAdvanceController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\NewVehicleSaleController;
use App\Http\Controllers\Admin\PaymentMainController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard.index');
});
Route::get('/admin/customers/search', [CustomerController::class, 'search'])
    ->name('customers.search')
    ->middleware(['web', 'auth']);


Route::group(['middleware' => ['auth']], function () {

    Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::resource('roles', RoleController::class)->except(['show']);
        Route::resource('permissions', PermissionController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::resource('booking-advances', BookingAdvanceController::class);
        Route::resource('customers', CustomerController::class);
        Route::get('booking-advances/{bookingAdvance}/receipt', [BookingAdvanceController::class, 'receipt'])
            ->name('booking-advances.receipt');
        Route::resource('new-vehicle-sales', NewVehicleSaleController::class);
    });
    Route::resource('paymentsmain', PaymentMainController::class);
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/admin/customers/store', [CustomerController::class, 'store'])->name('admin.customers.store');
});

require __DIR__ . '/auth.php';
