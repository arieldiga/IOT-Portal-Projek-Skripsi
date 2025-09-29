<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SensorDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login-ui');
});

// Login routes
Route::get('/login-ui', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login-ui', [LoginController::class, 'login'])->name('login.manual.submit');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Main Dashboard
    Route::get('/dashboard', [UserController::class, 'index'])->name('dashboard');

    // AJAX API Routes untuk Dashboard
    Route::prefix('api')->group(function () {
        Route::get('/sensor-data/initial', [UserController::class, 'getInitialSensorData'])
            ->name('api.sensor.initial');
        
        Route::post('/sensor-data/filtered', [UserController::class, 'getFilteredSensorData'])
            ->name('api.sensor.filtered');
        
        Route::get('/sensor-data/table', [UserController::class, 'getTableData'])
            ->name('api.sensor.table');

        Route::get('/users', [UserController::class, 'getApiUsers'])
            ->name('api.users');
        
        Route::get('/users/{id}', [UserController::class, 'getUserById'])
            ->name('api.users.show');
        
        // Sensor Column Configuration Routes (untuk Super Admin)
        Route::get('/users/{userId}/sensor-columns', [UserController::class, 'getSensorColumns'])
            ->name('api.users.sensor-columns');
        Route::post('/users/{userId}/sensor-columns', [UserController::class, 'saveSensorColumns'])
            ->name('api.users.sensor-columns.save');
    });

    // User Management
    Route::post('/users/store', [UserController::class, 'store'])->name('users.store');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password Change
    Route::get('/change-password', [App\Http\Controllers\PasswordController::class, 'edit'])
        ->name('password.edit');
    Route::post('/change-password', [App\Http\Controllers\PasswordController::class, 'update'])
        ->name('password.update');

    // Sensor Export Routes - Excel Only
    Route::prefix('sensor')->group(function () {
        // Excel Export - Main export route
        Route::get('/export-excel', [UserController::class, 'exportExcel'])
            ->name('sensor.export.excel');
            
        // Legacy route redirect (if any old links exist)
        Route::get('/export', [UserController::class, 'exportExcel'])
            ->name('sensor.export');
            
        // View routes (optional - can keep or remove based on needs)
        Route::get('/{id}', [SensorDataController::class, 'index'])
            ->name('sensor.index');
            
        Route::get('/{id}/chart', [SensorDataController::class, 'chartData'])
            ->name('sensor.chart');
    });
});