<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardControl;
use App\Http\Controllers\PresensiController;
use Illuminate\Support\Facades\Route;      

Route::middleware(['guest:karyawan'])->group(function (){
    Route::get('/', function () {
        return view('auth.login');
    })->name('login');
    Route::post('/proseslogin',[AuthController::class, 'proseslogin']);
});

Route::middleware(['auth:karyawan'])->group(function (){
    Route::get('/dashboard', [DashboardControl::class, 'index']);
    Route::get('/proseslogout',[AuthController::class,'proseslogout']);

    //presensi
    Route::get('/presensi/create',[PresensiController::class, 'create']); 
    Route::post('/presensi/store',[PresensiController::class, 'store']);
    
    // Edit Profile
    Route::get('/editprofile', [PresensiController::class, 'editprofile']);
    Route::post('/presensi/{email}/updateprofile', [PresensiController::class, 'updateprofile']);
});