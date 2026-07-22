<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigurationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeePaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayableInvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReceivableInvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LicenseController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    // License activation (accessible while locked)
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard.view');
    Route::get('/search', SearchController::class)->name('search');

    Route::get('support', [SupportController::class, 'index'])->name('support.index');
    Route::post('support/email', [SupportController::class, 'sendEmail'])->name('support.email');

    Route::middleware('permission:audit.view')->group(function () {
        Route::get('audit', [AuditController::class, 'index'])->name('audit.index');
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::middleware('permission:products.manage')->group(function () {
        Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:products.view')->group(function () {
        Route::resource('products', ProductController::class);
    });

    Route::middleware('permission:products.manage')->group(function () {
        Route::put('products/{product}/quick-update', [ProductController::class, 'quickUpdate'])->name('products.quick-update');
    });

    Route::middleware('permission:customers.manage')->group(function () {
        Route::resource('customers', CustomerController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::resource('suppliers', SupplierController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    Route::middleware('permission:purchases.view')->group(function () {
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('purchases/create/new', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('purchases/{purchase}/activate', [PurchaseController::class, 'activate'])->name('purchases.activate');
    });

    Route::middleware('permission:sales.view')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
        Route::get('sales/{sale}', [SaleController::class, 'show'])->name('sales.show');
        Route::get('sales/{sale}/invoice', [SaleController::class, 'invoice'])->name('sales.invoice');
        Route::get('sales/{sale}/invoice-fiscal', [SaleController::class, 'fiscalInvoice'])->name('sales.invoice.fiscal');
    });

    Route::middleware('permission:sales.create')->group(function () {
        Route::get('sales/create/new', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
        Route::post('sales/{sale}/activate', [SaleController::class, 'activate'])->name('sales.activate');
        Route::post('sales/{sale}/credits/{installment}/pay', [SaleController::class, 'payInstallment'])->name('sales.credits.installments.pay');
        Route::post('sales/{sale}/credits/{installment}/whatsapp', [SaleController::class, 'sendInstallmentReminder'])->name('sales.credits.installments.whatsapp');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/general', [ReportController::class, 'general'])->name('reports.general');
    });

    Route::middleware('permission:reports.export')->group(function () {
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports/general/export', [ReportController::class, 'generalExport'])->name('reports.general.export');
    });

    Route::middleware('permission:configuration.manage')->group(function () {
        Route::get('configuration', [ConfigurationController::class, 'index'])->name('configuration.index');
        Route::put('configuration', [ConfigurationController::class, 'update'])->name('configuration.update');
        Route::get('backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('backups', [BackupController::class, 'store'])->name('backups.store');
    });

    Route::middleware('permission:employee-payments.manage')->group(function () {
        Route::get('employee-payments', [EmployeePaymentController::class, 'index'])->name('employee-payments.index');
        Route::post('employee-payments', [EmployeePaymentController::class, 'store'])->name('employee-payments.store');
        Route::delete('employee-payments/{employeePayment}', [EmployeePaymentController::class, 'destroy'])->name('employee-payments.destroy');
    });

    Route::middleware('permission:accounts-receivable.manage')->group(function () {
        Route::get('accounts-receivable', [ReceivableInvoiceController::class, 'index'])->name('accounts-receivable.index');
        Route::post('accounts-receivable', [ReceivableInvoiceController::class, 'store'])->name('accounts-receivable.store');
        Route::post('accounts-receivable/{receivableInvoice}/payments', [ReceivableInvoiceController::class, 'addPayment'])->name('accounts-receivable.payments.store');
    });

    Route::middleware('permission:accounts-payable.manage')->group(function () {
        Route::get('accounts-payable', [PayableInvoiceController::class, 'index'])->name('accounts-payable.index');
        Route::post('accounts-payable', [PayableInvoiceController::class, 'store'])->name('accounts-payable.store');
        Route::post('accounts-payable/{payableInvoice}/payments', [PayableInvoiceController::class, 'addPayment'])->name('accounts-payable.payments.store');
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:users.edit')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
