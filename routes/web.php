<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'loadLogin']); // shows Login only
Route::post('/login-user', [AuthController::class, 'loginUser']);
Route::get('/dashboard', fn() => view('layouts.app', ['defaultComponent' => 'Content']));
Route::get('/register', [AuthController::class, 'showRegister']); // shows Register
Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');

