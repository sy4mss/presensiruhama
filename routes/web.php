<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardControl;
use Illuminate\Support\Facades\Route;      

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', [DashboardControl::class, 'index']);

Route::post('/proseslogin', [AuthController::class, 'proseslogin']);