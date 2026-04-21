<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GithubController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use Illuminate\Support\Facades\Route;

Route::post('verify', [AuthController::class, 'sendVerifyEmailOtp']);
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::get('github', [GithubController::class, 'redirect']);
Route::get('github/callback', [GithubController::class, 'callback']);

Route::get('google', [GoogleController::class, 'redirect']);
Route::get('google/callback', [GoogleController::class, 'callback']);

Route::get('facebook', [FacebookController::class, 'redirect']);
Route::get('facebook/callback', [FacebookController::class, 'callback']);

Route::post('forgot', [AuthController::class, 'sendPasswordResetOtp']);
Route::post('verify-otp', [AuthController::class, 'verifyPasswordResetOtp']);
Route::post('reset', [AuthController::class, 'resetPassword']);


Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);
  Route::get('get-user', [AuthController::class, 'getUser']);

  Route::post('change-password', [AuthController::class, 'changePassword']);

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
