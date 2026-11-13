<?php
use cloudgrayau\pitch\controllers\CacheController;
use Illuminate\Support\Facades\Route;

// Also mounted on the site action trigger, hence the explicit access checks
Route::middleware(['auth', 'can:accessCp'])->group(function () {
  Route::post('cache/clear', [CacheController::class, 'clear']);
});
