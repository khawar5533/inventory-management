<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'loadLogin'])->name('login'); // shows Login only
Route::post('/login-user', [AuthController::class, 'loginUser']);

Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', fn() => view('layouts.app', ['defaultComponent' => 'Content'])); 
  Route::get('/register', [AuthController::class, 'showRegister']); // shows Register
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//Logout

});


Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');

