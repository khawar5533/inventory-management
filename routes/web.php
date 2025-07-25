<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;

Route::get('/', [AuthController::class, 'loadLogin'])->name('login'); // shows Login only
Route::post('/login-user', [AuthController::class, 'loginUser']);

Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', fn() => view('layouts.app', ['defaultComponent' => 'Content'])); 
  Route::get('/register', [AuthController::class, 'showRegister']); // shows Register
  Route::get('/role', [AuthController::class, 'showRoleForm']); // shows Add Role Form
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//Logout

});

Route::get('/get-users', [AuthController::class, 'getListUsers']);
Route::post('/create-role', [RoleController::class, 'createRole']);
Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');

