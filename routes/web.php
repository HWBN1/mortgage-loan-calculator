<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/loan-input-form', [LoanController::class, 'showLoanInputForm'])->name('loan.input.form');

Route::post('/calculate-amortization-schedule', [LoanController::class, 'calculateAmortizationSchedule'])
    ->name('calculate.amortization.schedule');

Route::post('/calculate-extra-repayment-schedule', [LoanController::class, 'calculateAmortizationSchedule'])
    ->name('calculate.extra.repayment.schedule');


Route::get('/amortization-schedule', [LoanController::class, 'showAmortizationSchedule'])
    ->name('amortization.schedule');

