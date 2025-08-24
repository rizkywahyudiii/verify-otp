<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by theRouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication Routes
Route::get('/register', [AuthController::class, 'showRegister'])->name('register.show');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/verify-otp', [AuthController::class, 'showVerifyOTP'])->name('verify-otp.show');
Route::post('/verify-otp', [AuthController::class, 'verifyOTP'])->name('verify-otp');
Route::post('/resend-otp', [AuthController::class, 'resendOTP'])->name('resend-otp');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->name('delete-account')->middleware('auth');

// Dashboard Route (Protected)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login.show');
});
