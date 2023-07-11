<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logout', [LoginController::class, 'logout']);
Route::get('/export', [App\Http\Controllers\ReportController::class, 'export']);



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/daily_report', [App\Http\Controllers\ReportController::class, 'index']);
Route::post('/get_locaion_segment', [App\Http\Controllers\ReportController::class, 'get_locaion_segment']);
Route::post('/submit_daily_report', [App\Http\Controllers\ReportController::class, 'submit_daily_report']);
Route::get('/download_excel', [App\Http\Controllers\ReportController::class, 'download_excel']);

