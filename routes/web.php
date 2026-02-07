<?php

use App\Http\Controllers\InvoiceController;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\ExpenseCategories\Index as ExpenseCategoriesIndex;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Purchases\Index as PurchasesIndex;
use App\Livewire\Reports\Cashflow as CashflowReport;
use App\Livewire\Reports\Sales as SalesReport;
use App\Livewire\Sales\Index as SalesIndex;
use App\Livewire\Sales\History as SalesHistory;
use App\Livewire\Stocks\Alerts as StocksAlerts;
use App\Livewire\Stocks\Index as StocksIndex;
use App\Livewire\Suppliers\Index as SuppliersIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {
    Route::get('categories', CategoriesIndex::class)
        ->name('categories.index');
    Route::get('expense-categories', ExpenseCategoriesIndex::class)
        ->name('expense-categories.index');
    Route::get('products', ProductsIndex::class)
        ->name('products.index');
    Route::get('stocks', StocksIndex::class)
        ->name('stocks.index');
    Route::get('stocks/alerts', StocksAlerts::class)
        ->name('stocks.alerts');
    Route::get('sales', SalesIndex::class)
        ->name('sales.index');
    Route::get('sales/history', SalesHistory::class)
        ->name('sales.history');
    Route::get('reports/sales', SalesReport::class)
        ->name('reports.sales');
    Route::get('reports/cashflow', CashflowReport::class)
        ->name('reports.cashflow');
    Route::get('users', UsersIndex::class)
        ->name('users.index');
    Route::get('suppliers', SuppliersIndex::class)
        ->name('suppliers.index');
    Route::get('purchases', PurchasesIndex::class)
        ->name('purchases.index');
    Route::get('expenses', ExpensesIndex::class)
        ->name('expenses.index');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download');
    Route::get('invoices/{invoice}/receipt', [InvoiceController::class, 'receipt'])
        ->name('invoices.receipt');
});

require __DIR__.'/auth.php';
