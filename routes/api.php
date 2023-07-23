<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/calculate-monthly-payment', [LoanController::class, 'calculateMonthlyPayment']);
Route::post('/generate-amortization-schedule', [LoanController::class, 'generateAmortizationSchedule']);
Route::post('/generate-extra-repayment-schedule', [LoanController::class, 'generateExtraRepaymentSchedule']);