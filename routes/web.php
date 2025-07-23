<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [AuthController::class, 'loadLogin']); // shows Login only
Route::get('/dashboard', fn() => view('layouts.app', ['defaultComponent' => 'Content']));
Route::get('/register', [AuthController::class, 'showRegister']); // shows Register

