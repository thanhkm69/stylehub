<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use Illuminate\Support\Facades\Route;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('github', [GithubController::class, 'redirect']);
Route::get('github/callback', [GithubController::class, 'callback']);

Route::get('google', [GoogleController::class, 'redirect']);
Route::get('google/callback', [GoogleController::class, 'callback']);

Route::get('facebook', [FacebookController::class, 'redirect']);
Route::get('facebook/callback', [FacebookController::class, 'callback']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('getUser', [AuthController::class, 'getUser']);

  Route::middleware('abilities:Admin')->group(function () {

    Route::controller(CategoryController::class)->group(function () {
      Route::get('categories', 'index');
      Route::post('categories', 'store');
      Route::get('categories/{id}', 'show');
      Route::post('categories/{id}', 'update');
      Route::delete('categories/{id}', 'destroy');
    });
  });
});
