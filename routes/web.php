<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuperAdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Route::get('sadmin/dashboard',[SuperAdminController::class,'SuperAdminDashboard'])->name('sadmin.dashboard');
Route::middleware(['auth', 'role:superadmin'])->group(function(){
    Route::get('sadmin/dashboard',[SuperAdminController::class,'SuperAdminDashboard'])->name('sadmin.dashboard');
});

// Route::get('admin/dashboard',[AdminController::class,'AdminDashboard'])->name('admin.dashboard');
Route::middleware(['auth', 'role:admin'])->group(function(){
    Route::get('admin/dashboard',[AdminController::class,'AdminDashboard'])->name('admin.dashboard');
});
