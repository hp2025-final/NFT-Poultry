<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/settings/company', [\App\Http\Controllers\CompanyController::class, 'edit'])->name('settings.company');
    Route::post('/settings/company', [\App\Http\Controllers\CompanyController::class, 'update'])->name('settings.company.update');

    Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['destroy']);
    Route::post('/products/{product}/toggle', [\App\Http\Controllers\ProductController::class, 'toggleActive'])->name('products.toggle');

    Route::resource('customers', \App\Http\Controllers\CustomerController::class)->except(['destroy']);
    Route::post('/customers/{customer}/toggle', [\App\Http\Controllers\CustomerController::class, 'toggleActive'])->name('customers.toggle');

    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class)->except(['destroy']);
    Route::post('/suppliers/{supplier}/toggle', [\App\Http\Controllers\SupplierController::class, 'toggleActive'])->name('suppliers.toggle');

    Route::resource('accounts', \App\Http\Controllers\AccountController::class)->except(['destroy']);
    Route::post('/accounts/{account}/toggle', [\App\Http\Controllers\AccountController::class, 'toggleActive'])->name('accounts.toggle');

    Route::resource('expense_categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['show', 'destroy']);
    Route::post('/expense_categories/{expenseCategory}/toggle', [\App\Http\Controllers\ExpenseCategoryController::class, 'toggleActive'])->name('expense_categories.toggle');

    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class)->except(['show']);

    Route::get('/purchases/bulk', [\App\Http\Controllers\PurchaseController::class, 'bulk'])->name('purchases.bulk');
    Route::post('/purchases/bulk', [\App\Http\Controllers\PurchaseController::class, 'storeBulk'])->name('purchases.storeBulk');
    Route::resource('purchases', \App\Http\Controllers\PurchaseController::class);
    
    Route::get('/sales/bulk', [\App\Http\Controllers\SaleController::class, 'bulk'])->name('sales.bulk');
    Route::post('/sales/bulk', [\App\Http\Controllers\SaleController::class, 'storeBulk'])->name('sales.storeBulk');
    Route::resource('sales', \App\Http\Controllers\SaleController::class);

    Route::get('/receipts/bulk', [\App\Http\Controllers\ReceiptController::class, 'bulk'])->name('receipts.bulk');
    Route::post('/receipts/bulk', [\App\Http\Controllers\ReceiptController::class, 'storeBulk'])->name('receipts.storeBulk');
    Route::resource('receipts', \App\Http\Controllers\ReceiptController::class);

    Route::get('/payments/bulk', [\App\Http\Controllers\PaymentController::class, 'bulk'])->name('payments.bulk');
    Route::post('/payments/bulk', [\App\Http\Controllers\PaymentController::class, 'storeBulk'])->name('payments.storeBulk');
    Route::resource('payments', \App\Http\Controllers\PaymentController::class);

    /* ===== Report PDFs (Date range) ===== */
    Route::get('/reports/sales', [\App\Http\Controllers\ReportController::class, 'salesPdf'])->name('reports.sales');
    Route::get('/reports/purchases', [\App\Http\Controllers\ReportController::class, 'purchasesPdf'])->name('reports.purchases');
    Route::get('/reports/accounts', [\App\Http\Controllers\ReportController::class, 'accountsPdf'])->name('reports.accounts');
    Route::get('/reports/suppliers', [\App\Http\Controllers\ReportController::class, 'suppliersPdf'])->name('reports.suppliers');
    
    Route::get('/reports/customers/{id}/a4', [\App\Http\Controllers\ReportController::class, 'customersA4'])->name('reports.customers_a4');
    Route::get('/reports/customers/{id}/thermal', [\App\Http\Controllers\ReportController::class, 'customersThermal'])->name('reports.customers_thermal');
    Route::get('/reports/customers_closing_thermal', [\App\Http\Controllers\ReportController::class, 'customersClosingThermal'])->name('reports.customers_closing_thermal');
    Route::get('/reports/equity', [\App\Http\Controllers\ReportController::class, 'equityPdf'])->name('reports.equity');

    /* ===== Individual Invoice PDFs (A4 + Thermal) ===== */
    Route::get('/reports/sales/{id}/pdf', [\App\Http\Controllers\ReportController::class, 'saleInvoicePdf'])->name('reports.sale_invoice_pdf');
    Route::get('/reports/sales/{id}/thermal', [\App\Http\Controllers\ReportController::class, 'saleInvoiceThermal'])->name('reports.sale_invoice_thermal');
    Route::get('/reports/purchases/{id}/pdf', [\App\Http\Controllers\ReportController::class, 'purchaseInvoicePdf'])->name('reports.purchase_invoice_pdf');
    Route::get('/reports/purchases/{id}/thermal', [\App\Http\Controllers\ReportController::class, 'purchaseInvoiceThermal'])->name('reports.purchase_invoice_thermal');

    /* ===== Daily Customer Report ===== */
    Route::get('/reports/daily-customer', [\App\Http\Controllers\ReportController::class, 'dailyCustomer'])->name('reports.daily_customer');
    Route::get('/reports/daily-customer/pdf', [\App\Http\Controllers\ReportController::class, 'dailyCustomerPdf'])->name('reports.daily_customer_pdf');
    Route::get('/reports/daily-customer/thermal', [\App\Http\Controllers\ReportController::class, 'dailyCustomerThermal'])->name('reports.daily_customer_thermal');

    /* ===== Customers & Suppliers List Reports ===== */
    Route::get('/reports/customers-list/a4', [\App\Http\Controllers\ReportController::class, 'customersListA4'])->name('reports.customers_list_a4');
    Route::get('/reports/customers-list/thermal', [\App\Http\Controllers\ReportController::class, 'customersListThermal'])->name('reports.customers_list_thermal');
    Route::get('/reports/suppliers-list/a4', [\App\Http\Controllers\ReportController::class, 'suppliersListA4'])->name('reports.suppliers_list_a4');
    Route::get('/reports/suppliers-list/thermal', [\App\Http\Controllers\ReportController::class, 'suppliersListThermal'])->name('reports.suppliers_list_thermal');

    Route::resource('stock_adjustments', \App\Http\Controllers\StockAdjustmentController::class);
    
    Route::resource('equity', \App\Http\Controllers\EquityController::class)->except(['show', 'edit', 'update', 'destroy']);
    Route::get('/reports/daily', [\App\Http\Controllers\DailyReportController::class, 'index'])->name('reports.daily');
    Route::get('/reports/daily/pdf', [\App\Http\Controllers\DailyReportController::class, 'pdf'])->name('reports.daily_pdf');
    Route::get('/reports/daily/thermal', [\App\Http\Controllers\DailyReportController::class, 'thermal'])->name('reports.daily_thermal');
    Route::get('/reports/trial-balance', [\App\Http\Controllers\FinancialReportController::class, 'trialBalance'])->name('reports.trial_balance');
    Route::get('/reports/profit-loss', [\App\Http\Controllers\FinancialReportController::class, 'profitLoss'])->name('reports.profit_loss');
    Route::get('/reports/profit-loss/thermal', [\App\Http\Controllers\FinancialReportController::class, 'profitLossThermal'])->name('reports.profit_loss_thermal');
    Route::get('/reports/balance-sheet', [\App\Http\Controllers\FinancialReportController::class, 'balanceSheet'])->name('reports.balance_sheet');
    
    Route::get('/backup', [\App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
    Route::get('/backup/download', [\App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::post('/backup/restore', [\App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');
    Route::post('/backup/restore-sqlite', [\App\Http\Controllers\BackupController::class, 'restoreSqlite'])->name('backup.restore_sqlite');

    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::post('/backup/remove-all', [\App\Http\Controllers\BackupController::class, 'removeAllData'])->name('backup.remove_all');
    });
});

