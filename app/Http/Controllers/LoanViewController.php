<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoanViewController extends Controller
{
    public function showAmortizationSchedule()
    {
        return view('amortization_schedule');
    }

    public function showExtraRepaymentSchedule()
    {
        return view('extra_repayment_schedule');
    }
}
