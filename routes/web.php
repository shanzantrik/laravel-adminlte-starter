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
use App\Http\Controllers\Admin\VasInvoiceController;
use App\Http\Controllers\Admin\JobAdvanceController;
use App\Http\Controllers\Admin\ServiceBillController;
use App\Http\Controllers\Admin\CounterSaleController;
use App\Http\Controllers\Admin\UsedCarAdvanceController;
use App\Http\Controllers\Admin\UsedCarSaleController;
use App\Http\Controllers\Admin\InsuranceAdvanceController;
use App\Http\Controllers\Admin\InsurancePolicyController;
use App\Http\Controllers\Admin\ExtendedWarrantyController;

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
        Route::get('new-vehicle-sales/{newVehicleSale}/receipt', [NewVehicleSaleController::class, 'receipt'])
            ->name('new-vehicle-sales.receipt');
    });
    Route::resource('paymentsmain', PaymentMainController::class)->parameters([
        'paymentsmain' => 'payment'
    ]);
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/admin/customers/store', [CustomerController::class, 'store'])->name('admin.customers.store');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('vas-invoices', VasInvoiceController::class);
    Route::get('vas-invoices/{vasInvoice}/receipt', [VasInvoiceController::class, 'receipt'])
        ->name('vas-invoices.receipt');
    Route::resource('job-advances', JobAdvanceController::class);
    Route::get('job-advances/{jobAdvance}/receipt', [JobAdvanceController::class, 'receipt'])
        ->name('job-advances.receipt');
    Route::resource('service-bills', ServiceBillController::class);
    Route::get('service-bills/{serviceBill}/receipt', [ServiceBillController::class, 'receipt'])
        ->name('service-bills.receipt');
    Route::resource('counter-sales', CounterSaleController::class);
    Route::get('counter-sales/{counterSale}/receipt', [CounterSaleController::class, 'receipt'])
        ->name('counter-sales.receipt');
    Route::resource('used-car-advances', UsedCarAdvanceController::class);
    Route::get('used-car-advances/{usedCarAdvance}/receipt', [UsedCarAdvanceController::class, 'receipt'])
        ->name('used-car-advances.receipt');
    Route::resource('used-car-sales', UsedCarSaleController::class);
    Route::get('used-car-sales/{usedCarSale}/receipt', [UsedCarSaleController::class, 'receipt'])
        ->name('used-car-sales.receipt');
    Route::resource('insurance-advances', InsuranceAdvanceController::class);
    Route::get('insurance-advances/{insuranceAdvance}/receipt', [InsuranceAdvanceController::class, 'receipt'])
        ->name('insurance-advances.receipt');
    Route::resource('insurance-policies', InsurancePolicyController::class);
    Route::get('insurance-policies/{insurancePolicy}/receipt', [InsurancePolicyController::class, 'receipt'])
        ->name('insurance-policies.receipt');
    Route::resource('extended-warranties', ExtendedWarrantyController::class);
    Route::get('extended-warranties/{extendedWarranty}/receipt', [ExtendedWarrantyController::class, 'receipt'])
        ->name('extended-warranties.receipt');
});

require __DIR__ . '/auth.php';
