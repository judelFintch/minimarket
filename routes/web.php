<?php

use App\Http\Controllers\InvoiceController;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Purchases\Index as PurchasesIndex;
use App\Livewire\Sales\Index as SalesIndex;
use App\Livewire\Stocks\Index as StocksIndex;
use App\Livewire\Suppliers\Index as SuppliersIndex;
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
    Route::get('products', ProductsIndex::class)
        ->name('products.index');
    Route::get('stocks', StocksIndex::class)
        ->name('stocks.index');
    Route::get('sales', SalesIndex::class)
        ->name('sales.index');
    Route::get('suppliers', SuppliersIndex::class)
        ->name('suppliers.index');
    Route::get('purchases', PurchasesIndex::class)
        ->name('purchases.index');
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download');
});

require __DIR__.'/auth.php';
