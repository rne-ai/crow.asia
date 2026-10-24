<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadPdfController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Webhooks\MayarWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

// Auth Routes (Login / Logout)
Route::get('/login', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'store']);
Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

// Checkout & Payment Routes
Route::get('/checkout/{tierId}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/process', [CheckoutController::class, 'store'])->name('checkout.process');
Route::get('/checkout/mock-pay/{orderNumber}', [CheckoutController::class, 'update'])->name('checkout.mock-pay');

// Mayar Webhook (Exempt from CSRF via bootstrap/app.php)
Route::post('/api/webhooks/mayar', MayarWebhookController::class)->name('webhooks.mayar');

// Dashboard & PDF Download
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/download/pdf/{bookId}', DownloadPdfController::class)->name('download.pdf');

// Web Reader Routes
Route::get('/read/{bookId}', function (string $bookId) {
    return redirect("/read/{$bookId}/id/ch-0");
});
Route::get('/read/{bookId}/{locale}', function (string $bookId, string $locale) {
    return redirect("/read/{$bookId}/{$locale}/ch-0");
});
Route::get('/read/{bookId}/{locale}/{chapterSlug}', [ReaderController::class, 'show'])->name('reader.chapter');

// Checklist API
Route::post('/api/checklists/toggle', ChecklistController::class)->name('checklists.toggle');

// Hybrid Search API (SOS Topik Ctrl+K)
Route::get('/api/search', SearchController::class)->name('api.search');
