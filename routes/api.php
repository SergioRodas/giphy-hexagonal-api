<?php

use Illuminate\Support\Facades\Route;
use Infrastructure\Http\Controllers\AuthController;
use Infrastructure\Http\Controllers\FavoriteController;
use Infrastructure\Http\Controllers\GifController;

/*
|--------------------------------------------------------------------------
| API routes
|--------------------------------------------------------------------------
|
| All four services live here. Login is public; the remaining services
| require a valid OAuth2 access token (Passport "api" guard). Every request
| is recorded by the LogInteraction middleware registered on the api group.
|
*/

Route::post('/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:api')->group(function () {
    // Order matters: the literal /gifs/search must win over /gifs/{id}.
    Route::get('/gifs/search', [GifController::class, 'search'])->name('gifs.search');
    Route::get('/gifs/{id}', [GifController::class, 'show'])->name('gifs.show');

    Route::post('/favorites', [FavoriteController::class, 'store'])->name('favorites.store');
});
