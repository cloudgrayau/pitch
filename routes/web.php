<?php
use cloudgrayau\pitch\controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::get('css/{path}.css', [AssetController::class, 'css'])->where('path', '.+');
Route::get('scss/{path}.css', [AssetController::class, 'scss'])->where('path', '.+');
Route::get('scss/{path}.scss', [AssetController::class, 'scss'])->where('path', '.+');
Route::get('js/{path}.js', [AssetController::class, 'js'])->where('path', '.+');