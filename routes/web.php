<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/users/by-office-and-departemen', [DashboardController::class, 'getByOfficeAndDepartemen'])->name('users.byOfficeAndDepartemen');
Route::get('/export-excel', [DashboardController::class, 'exportExcel'])->name('export.excel');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'postLogin'])->name('postLogin');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');