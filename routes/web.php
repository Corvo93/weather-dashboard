<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;

Route::get('/', [WeatherController::class, 'dashboard'])->name('dashboard');

Route::post('/dashboard', [WeatherController::class, 'showDashboard'])->name('show.dashboard');

Route::post('/add-city', [WeatherController::class, 'addCity'])->name('add.city');
