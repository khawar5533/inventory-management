<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

Route::get('/', [AuthController::class, 'loadLogin'])->name('login'); // shows Login only
Route::post('/login-user', [AuthController::class, 'loginUser']);

Route::middleware(['auth'])->group(function () {
  Route::get('/dashboard', fn() => view('layouts.app', ['defaultComponent' => 'Content'])); 
  Route::get('/register', [AuthController::class, 'showRegister']); // shows Register
  Route::get('/role', [AuthController::class, 'showRoleForm']); // shows Add Role Form
  Route::get('/permission', [PermissionController::class, 'loadPermission']);
  Route::get('/user-permission', [PermissionController::class, 'loadAssignPermission']);
  Route::get('userrole', [RoleController ::class, 'loadRoleUserForm']);
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');//Logout
  Route::post('/assign-role/{userId}', [RoleController::class, 'assignRole']);
  Route::get('/get-grouped-permissions', [PermissionController::class, 'getGroupedPermissions']);
  Route::post('/assign-permissions-to-role', [PermissionController::class, 'assignPermissionsToRole']);
  Route::get('/get-role-permissions/{roleId}', [PermissionController::class, 'getRolePermissions']);

});

Route::get('/get-users', [AuthController::class, 'getListUsers']);
Route::get('/get-roles', [RoleController::class, 'getListRoles']);
Route::post('/create-role', [RoleController::class, 'createRole']);
Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');
Route::post('/create-permissions', [PermissionController::class, 'storePermissions']);


