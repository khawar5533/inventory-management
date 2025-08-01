<?php
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\FloorController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\BoxController;

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
  Route::get('/show-permission', [RoleController ::class, 'loadShowPermission']);
  Route::get('/get-grouped-permissions', [PermissionController::class, 'getGroupedPermissions']);
  Route::post('/assign-permissions-to-role', [PermissionController::class, 'assignPermissionsToRole']);
  Route::get('/get-role-permissions/{roleId}', [PermissionController::class, 'getRolePermissions']);
  Route::get('/get-user-role-ids', [RoleController::class, 'getUserRoleIds']);
  Route::post('/soft-delete-permission/{id}', [PermissionController::class, 'softDelete']);
  //Add Location
  Route::get('/add-location', [LocationController::class, 'loadLocationForm']);
  Route::get('/floor', [FloorController::class, 'loadFooterForm']);
//
  // Location
  Route::get('/locations', [LocationController::class, 'index']);
  Route::post('/locations', [LocationController::class, 'store']);
  Route::post('/locations/update/{id}', [LocationController::class, 'update']);
  Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
  //Floor
  Route::get('/floors', [FloorController::class, 'index'])->name('floors.index');
  Route::post('/add-floor', [FloorController::class, 'store'])->name('floors.store');
  Route::post('/floors/update/{id}', [FloorController::class, 'update'])->name('floors.update'); // Update (POST instead of PUT)
  Route::delete('/floors/{id}', [FloorController::class, 'destroy'])->name('floors.destroy'); 
  //Room
  Route::get('/room', [RoomController::class, 'loadRoomForm']); 
  Route::get('/floor-list', [RoomController::class, 'list']);
  Route::get('/room-list', [RoomController::class, 'getRoomList']);
  Route::post('/add-room', [RoomController::class, 'store']);
  Route::match(['put', 'post'], '/update-room/{room}', [RoomController::class, 'update']);
  Route::delete('/delete-room/{room}', [RoomController::class, 'destroy']);
  //rack
  Route::get('/get-rack', [RackController::class, 'index']);
  Route::get('/rack', [RackController::class, 'loadRackForm']); 
  Route::get('/rooms', [RackController::class, 'getRooms']);
  Route::post('/add-rack', [RackController::class, 'store']);
  Route::post('/racks/{id}', [RackController::class, 'update']);
  Route::post('/racks/{id}/delete', [RackController::class, 'destroy']);
  //box 
   Route::get('/box', [BoxController::class, 'loadBoxForm']);

  Route::post('/add-box', [BoxController::class, 'store']);
  Route::post('/update-box/{box}', [BoxController::class, 'update']);
  Route::post('/delete-box/{id}', [BoxController::class, 'destroy']);
  Route::get('/box-list', [BoxController::class, 'boxList']);
  Route::get('/rack-list', [RackController::class, 'index']); 
});

Route::get('/get-users', [AuthController::class, 'getListUsers']);
Route::get('/get-roles', [RoleController::class, 'getListRoles']);
Route::post('/create-role', [RoleController::class, 'createRole']);
Route::post('/register-user', [AuthController::class, 'registerUser'])->name('register.user');
Route::post('/create-permissions', [PermissionController::class, 'storePermissions']);


