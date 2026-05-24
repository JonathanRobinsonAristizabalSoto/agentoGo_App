<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ReservationController;

// Rutas de autenticación públicas
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:login');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

// Rutas protegidas con autenticación Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de autenticación autenticadas
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Crear y listar negocios (no requieren tenant existente)
    Route::post('/businesses', [BusinessController::class, 'store']);
    Route::get('/businesses', [BusinessController::class, 'index']);

    // Rutas que requieren un business existente (tenant)
    Route::middleware([\App\Http\Middleware\EnsureTenant::class, 'throttle:business'])->group(function () {
        Route::get('/businesses/{business}', [BusinessController::class, 'show']);
        Route::put('/businesses/{business}', [BusinessController::class, 'update']);
        Route::delete('/businesses/{business}', [BusinessController::class, 'destroy']);

        Route::get('/businesses/{business}/departments', [DepartmentController::class, 'index']);
        Route::post('/businesses/{business}/departments', [DepartmentController::class, 'store']);
        Route::get('/businesses/{business}/departments/{department}', [DepartmentController::class, 'show']);
        Route::put('/businesses/{business}/departments/{department}', [DepartmentController::class, 'update']);
        Route::delete('/businesses/{business}/departments/{department}', [DepartmentController::class, 'destroy']);

        Route::get('/businesses/{business}/employees', [EmployeeController::class, 'index']);
        Route::post('/businesses/{business}/employees', [EmployeeController::class, 'store']);
        Route::get('/businesses/{business}/employees/{employee}', [EmployeeController::class, 'show']);
        Route::put('/businesses/{business}/employees/{employee}', [EmployeeController::class, 'update']);
        Route::delete('/businesses/{business}/employees/{employee}', [EmployeeController::class, 'destroy']);

        Route::get('/businesses/{business}/clients', [ClientController::class, 'index']);
        Route::post('/businesses/{business}/clients', [ClientController::class, 'store']);
        Route::get('/businesses/{business}/clients/{client}', [ClientController::class, 'show']);
        Route::put('/businesses/{business}/clients/{client}', [ClientController::class, 'update']);
        Route::delete('/businesses/{business}/clients/{client}', [ClientController::class, 'destroy']);

        Route::get('/businesses/{business}/reservations', [ReservationController::class, 'index']);
        Route::post('/businesses/{business}/reservations', [ReservationController::class, 'store']);
        Route::get('/businesses/{business}/reservations/{reservation}', [ReservationController::class, 'show']);
        Route::put('/businesses/{business}/reservations/{reservation}', [ReservationController::class, 'update']);
        Route::delete('/businesses/{business}/reservations/{reservation}', [ReservationController::class, 'destroy']);
    });

    // Rutas de auditoría
    Route::get('/audit-logs', [AuditLogController::class, 'index']);

    // Ruta que retorna el usuario autenticado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});