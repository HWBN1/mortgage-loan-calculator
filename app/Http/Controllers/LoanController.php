<?php

namespace App\Http\Controllers;

use App\Models\ExtraRepaymentSchedule;
use App\Models\LoanAmortizationSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    public function calculateMonthlyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:0',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate the monthly payment amount using the formula
        $loanAmount = $request->input('loan_amount');
        $annualInterestRate = $request->input('annual_interest_rate');
        $loanTerm = $request->input('loan_term');

        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));

        return response()->json(['monthly_payment' => $monthlyPayment], 200);
    }

    public function generateAmortizationSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:0',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate the monthly payment amount using the formula
        $loanAmount = $request->input('loan_amount');
        $annualInterestRate = $request->input('annual_interest_rate');
        $loanTerm = $request->input('loan_term');

        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));

        // Generate the amortization schedule data for each month
        $startingBalance = $loanAmount;
        $amortizationSchedule = [];

        for ($month = 1; $month <= $numberOfMonths; $month++) {
            $monthlyInterest = $startingBalance * $monthlyInterestRate;
            $principalComponent = $monthlyPayment - $monthlyInterest;
            $endingBalance = $startingBalance - $principalComponent;

            $amortizationSchedule[] = [
                'month_number' => $month,
                'starting_balance' => $startingBalance,
                'monthly_payment' => $monthlyPayment,
                'principal_component' => $principalComponent,
                'interest_component' => $monthlyInterest,
                'ending_balance' => $endingBalance,
            ];

            $startingBalance = $endingBalance;
        }

        // Store the amortization schedule data in the database
        LoanAmortizationSchedule::insert($amortizationSchedule);

        return response()->json(['amortization_schedule' => $amortizationSchedule], 200);
    }

    public function generateExtraRepaymentSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|numeric|min:1',
            'monthly_fixed_extra_payment' => 'optional|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate the monthly payment amount using the formula
        $loanAmount = $request->input('loan_amount');
        $annualInterestRate = $request->input('annual_interest_rate');
        $loanTerm = $request->input('loan_term');

        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $monthlyPayment = ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));

        // Generate the extra repayment schedule data for each month
        $startingBalance = $loanAmount;
        $remainingLoanTerm = $numberOfMonths;
        $extraRepaymentSchedule = [];

        for ($month = 1; $month <= $numberOfMonths; $month++) {
            $monthlyInterest = $startingBalance * $monthlyInterestRate;
            $principalComponent = $monthlyPayment - $monthlyInterest;

            // Check if there is an extra repayment for this month
            $extraRepaymentMade = $request->input('monthly_fixed_extra_payment', 0);
            $remainingLoanTermAfterExtraRepayment = $remainingLoanTerm;

            if ($extraRepaymentMade > 0) {
                $endingBalanceAfterExtraRepayment = $startingBalance - $extraRepaymentMade;
                $remainingLoanTermAfterExtraRepayment = $remainingLoanTerm - 1;
            } else {
                $endingBalanceAfterExtraRepayment = $startingBalance;
            }

            $extraRepaymentSchedule[] = [
                'month_number' => $month,
                'starting_balance' => $startingBalance,
                'monthly_payment' => $monthlyPayment,
                'principal_component' => $principalComponent,
                'interest_component' => $monthlyInterest,
                'extra_repayment_made' => $extraRepaymentMade,
                'ending_balance_after_extra_repayment' => $endingBalanceAfterExtraRepayment,
                'remaining_loan_term_after_extra_repayment' => $remainingLoanTermAfterExtraRepayment,
            ];

            $startingBalance = $endingBalanceAfterExtraRepayment;
            $remainingLoanTerm--;
        }

        // Store the extra repayment schedule data in the database
        ExtraRepaymentSchedule::insert($extraRepaymentSchedule);

        // Calculate the effective interest rate after each extra repayment
        foreach ($extraRepaymentSchedule as $key => $entry) {
            $remainingBalance = $entry['ending_balance_after_extra_repayment'];
            $effectiveInterestRate = ($monthlyInterestRate * 12) / ($remainingBalance / $loanAmount) * 100;

            $extraRepaymentSchedule[$key]['effective_interest_rate'] = $effectiveInterestRate;
        }

        // Update the extra repayment schedule data with the effective interest rate
        ExtraRepaymentSchedule::upsert($extraRepaymentSchedule, ['id'], ['effective_interest_rate']);


        return response()->json(['extra_repayment_schedule' => $extraRepaymentSchedule], 200);
    }

    public function showLoanInputForm()
    {
        return view('loan_input_form');
    }

    public function showAmortizationSchedule()
    {
        // Get the amortization schedule data from the session (previously stored after form submission)
        $amortizationSchedule = session('amortizationSchedule');

        return view('amortization_schedule', ['amortizationSchedule' => $amortizationSchedule]);
    }


    public function calculateAmortizationSchedule(Request $request)
    {
        // Validate the input data (similar to the previous method)
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return redirect()->route('loan.input.form')->withErrors($validator)->withInput();
        }

        // Assuming you have a method to generate the amortization schedule (like the previous example)
        $response = $this->generateAmortizationSchedule($request);

        // Decode the JSON response to access the amortization schedule array
        $data = $response->getData(true);
        $amortizationSchedule = $data['amortization_schedule'];

        // Store the amortization schedule data in the session
        session()->put('amortizationSchedule', $amortizationSchedule);

        // Redirect to the amortization schedule view
        return redirect()->route('amortization.schedule');
    }

}
