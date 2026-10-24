<?php

use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Landing');
});

// Web Reader Routes
Route::get('/read/{bookId}', function (string $bookId) {
    return redirect("/read/{$bookId}/id/ch-0");
});
Route::get('/read/{bookId}/{locale}', function (string $bookId, string $locale) {
    return redirect("/read/{$bookId}/{$locale}/ch-0");
});
Route::get('/read/{bookId}/{locale}/{chapterSlug}', [ReaderController::class, 'show'])->name('reader.chapter');

// Checklist API
Route::post('/api/checklists/toggle', [ChecklistController::class, 'toggle'])->name('checklists.toggle');

// Hybrid Search API (SOS Topik Ctrl+K)
Route::get('/api/search', [SearchController::class, 'search'])->name('api.search');
