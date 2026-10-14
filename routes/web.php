<?php
use cloudgrayau\pitch\controllers\AssetController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::withoutMiddleware([
    StartSession::class,
    ShareErrorsFromSession::class,
    AddQueuedCookiesToResponse::class,
    EncryptCookies::class,
    PreventRequestForgery::class,
])->group(function () {
    Route::get('css/{path}.css', [AssetController::class, 'css'])->where('path', '.+');
    Route::get('scss/{path}.css', [AssetController::class, 'scss'])->where('path', '.+');
    Route::get('scss/{path}.scss', [AssetController::class, 'scss'])->where('path', '.+');
    Route::get('js/{path}.js', [AssetController::class, 'js'])->where('path', '.+');
});