<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    public const AMORTIZATION_TABLE = 'loan_amortization_schedule';
    public const EXTRA_REPAYMENT_TABLE = 'extra_repayment_schedule';

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

        $monthlyPayment = $this->calculateMonthlyPaymentAmount(
            $request->input('loan_amount'),
            $request->input('annual_interest_rate'),
            $request->input('loan_term')
        );

        return response()->json(['monthly_payment' => $monthlyPayment], 200);
    }

    private function calculateMonthlyPaymentAmount($loanAmount, $annualInterestRate, $loanTerm)
    {
        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        return ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));
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

        $monthlyPayment = $this->calculateMonthlyPaymentAmount(
            $request->input('loan_amount'),
            $request->input('annual_interest_rate'),
            $request->input('loan_term')
        );

        $amortizationSchedule = $this->generateScheduleData(
            $request->input('loan_amount'),
            $monthlyPayment,
            $request->input('annual_interest_rate'),
            $request->input('loan_term')
        );

        $this->storeSchedule(self::AMORTIZATION_TABLE, $amortizationSchedule);

        return response()->json(['amortization_schedule' => $amortizationSchedule], 200);
    }

    private function generateScheduleData($loanAmount, $monthlyPayment, $annualInterestRate, $loanTerm)
    {
        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $startingBalance = $loanAmount;
        $schedule = [];

        for ($month = 1; $month <= $numberOfMonths; $month++) {
            $monthlyInterest = $startingBalance * $monthlyInterestRate;
            $principalComponent = $monthlyPayment - $monthlyInterest;
            $endingBalance = $startingBalance - $principalComponent;

            $schedule[] = [
                'month_number' => $month,
                'starting_balance' => $startingBalance,
                'monthly_payment' => $monthlyPayment,
                'principal_component' => $principalComponent,
                'interest_component' => $monthlyInterest,
                'ending_balance' => $endingBalance,
            ];

            $startingBalance = $endingBalance;
        }

        return $schedule;
    }

    public function generateExtraRepaymentSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|numeric|min:1',
            'monthly_fixed_extra_payment' => 'nullable|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $monthlyPayment = $this->calculateMonthlyPaymentAmount(
            $request->input('loan_amount'),
            $request->input('annual_interest_rate'),
            $request->input('loan_term')
        );

        $extraRepaymentSchedule = $this->generateExtraRepaymentScheduleData(
            $request->input('loan_amount'),
            $monthlyPayment,
            $request->input('annual_interest_rate'),
            $request->input('loan_term'),
            $request->input('monthly_fixed_extra_payment', 0)
        );

        $this->storeSchedule(self::EXTRA_REPAYMENT_TABLE, $extraRepaymentSchedule);

        return response()->json(['extra_repayment_schedule' => $extraRepaymentSchedule], 200);
    }

    private function generateExtraRepaymentScheduleData($loanAmount, $monthlyPayment, $annualInterestRate, $loanTerm, $extraRepaymentAmount)
    {
        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $startingBalance = $loanAmount;
        $remainingLoanTerm = $numberOfMonths;
        $extraRepaymentSchedule = [];

        

        for ($month = 1; $month <= $numberOfMonths; $month++) {
            // for the last month
            $monthlyPayment = min($monthlyPayment, $startingBalance);

            $monthlyInterest = $startingBalance * $monthlyInterestRate;
            $principalComponent = $monthlyPayment - $monthlyInterest;
            // dd($extraRepaymentAmount);
            if ($extraRepaymentAmount > 0) {
                $remainingLoanTermAfterExtraRepayment = $remainingLoanTerm - 1;
                // $principalComponent += $extraRepaymentAmount;
                $endingBalanceAfterExtraRepayment = max($startingBalance - $principalComponent - $extraRepaymentAmount, 0);

            } else {
                $endingBalanceAfterExtraRepayment = $startingBalance;
                $remainingLoanTermAfterExtraRepayment = $remainingLoanTerm;
            }

            $extraRepaymentSchedule[] = [
                'month_number' => $month,
                'starting_balance' => $startingBalance,
                'monthly_payment' => $monthlyPayment,
                'principal_component' => $principalComponent,
                'interest_component' => $monthlyInterest,
                'extra_repayment_made' => $extraRepaymentAmount,
                'ending_balance_after_extra_repayment' => $endingBalanceAfterExtraRepayment,
                'remaining_loan_term_after_extra_repayment' => $remainingLoanTermAfterExtraRepayment,
            ];

            $startingBalance = $endingBalanceAfterExtraRepayment;
            if ($endingBalanceAfterExtraRepayment <= 1) {
                break;
            }
            $remainingLoanTerm--;
        }

        return $extraRepaymentSchedule;
    }

    private function storeSchedule($tableName, $schedule)
    {
        // Store the schedule data in the database
        // Using a transaction to ensure data integrity
        try {
            DB::beginTransaction();
            DB::table($tableName)->insert($schedule);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // Handle the error here or throw an exception
        }
    }

    public function showLoanInputForm()
    {
        return view('loan_input_form');
    }

    private function calculateEffectiveInterestRate($annualInterestRate, $numberOfCompoundingPeriodsPerYear) {
        $monthlyInterestRate = $annualInterestRate / $numberOfCompoundingPeriodsPerYear;
        $effectiveInterestRate = pow(1 + $monthlyInterestRate, $numberOfCompoundingPeriodsPerYear) - 1;
        return $effectiveInterestRate * 100; // Convert to percentage
    }

    public function showAmortizationAndExtraRepaymentSchedule(Request $request)
    {
        // Validate the input data (similar to the previous methods)
        $validator = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1',
            'annual_interest_rate' => 'required|numeric|min:0',
            'loan_term' => 'required|integer|min:1',
            'monthly_fixed_extra_payment' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->route('loan.input.form')->withErrors($validator)->withInput();
        }

        // Calculate the Amortization Schedule
        $response = $this->generateAmortizationSchedule($request);

        // Decode the JSON response to access the amortization schedule array
        $data = $response->getData(true);
        $amortizationSchedule = $data['amortization_schedule'];

        // Calculate the Effective Interest Rate
        $effectiveInterestRate = $this->calculateEffectiveInterestRate(
            $request->input('annual_interest_rate') / 100,
            $request->input('loan_term') * 12
        );


        $monthlyPayment = $this->calculateMonthlyPaymentAmount(
            $request->input('loan_amount'),
            $request->input('annual_interest_rate'),
            $request->input('loan_term')
        );

        // Calculate the Extra Repayment Schedule
        $extraRepaymentSchedule = $this->generateExtraRepaymentScheduleData(
            $request->input('loan_amount'),
            $monthlyPayment,
            $request->input('annual_interest_rate'),
            $request->input('loan_term'),
            $request->input('monthly_fixed_extra_payment', 0)
        );

        // Pass all the required data to the view
        return view('loan_schedule', [
            'amortizationSchedule' => $amortizationSchedule,
            'extraRepaymentSchedule' => $extraRepaymentSchedule,
            'loanAmount' => $request->input('loan_amount'),
            'annualInterestRate' => $request->input('annual_interest_rate'),
            'loanTerm' => $request->input('loan_term'),
            'effectiveInterestRate' => $effectiveInterestRate
        ]);
    }
}
