<?php
use cloudgrayau\pitch\controllers\CacheController;
use Illuminate\Support\Facades\Route;

Route::post('pitch/refresh', [CacheController::class, 'refresh']);