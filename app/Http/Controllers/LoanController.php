<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    public const AMORTIZATION_TABLE = 'loan_amortization_schedule';
    public const EXTRA_REPAYMENT_TABLE = 'extra_repayment_schedule';

    /**
     * Calculate the monthly payment for a mortgage loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Calculate the monthly payment amount for a mortgage loan.
     *
     * @param  float  $loanAmount
     * @param  float  $annualInterestRate
     * @param  int  $loanTerm
     * @return float
     */
    private function calculateMonthlyPaymentAmount($loanAmount, $annualInterestRate, $loanTerm)
    {
        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        return ($loanAmount * $monthlyInterestRate) / (1 - pow(1 + $monthlyInterestRate, -$numberOfMonths));
    }

    /**
     * Generate the amortization schedule for a mortgage loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Generate the amortization schedule data for a mortgage loan.
     *
     * @param  float  $loanAmount
     * @param  float  $monthlyPayment
     * @param  float  $annualInterestRate
     * @param  int  $loanTerm
     * @return array
     */
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

    /**
     * Generate the extra repayment schedule for a mortgage loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Generate the extra repayment schedule data for a mortgage loan.
     *
     * @param  float  $loanAmount
     * @param  float  $monthlyPayment
     * @param  float  $annualInterestRate
     * @param  int  $loanTerm
     * @param  float  $extraRepaymentAmount
     * @return array
     */
    private function generateExtraRepaymentScheduleData($loanAmount, $monthlyPayment, $annualInterestRate, $loanTerm, $extraRepaymentAmount)
    {
        $monthlyInterestRate = ($annualInterestRate / 12) / 100;
        $numberOfMonths = $loanTerm * 12;
        $startingBalance = $loanAmount;
        $remainingLoanTerm = $numberOfMonths;
        $extraRepaymentSchedule = [];

        for ($month = 1; $month <= $numberOfMonths; $month++) {
            // For the last month, adjust the monthly payment to ensure the loan is fully paid off.
            $monthlyPayment = min($monthlyPayment, $startingBalance);

            $monthlyInterest = $startingBalance * $monthlyInterestRate;
            $principalComponent = $monthlyPayment - $monthlyInterest;

            if ($extraRepaymentAmount > 0) {
                $remainingLoanTermAfterExtraRepayment = $remainingLoanTerm - 1;
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

            // If the loan is paid off, break the loop to avoid unnecessary iterations.
            if ($endingBalanceAfterExtraRepayment <= 0) {
                break;
            }

            $remainingLoanTerm--;
        }

        return $extraRepaymentSchedule;
    }

    /**
     * Store the schedule data in the database.
     *
     * @param  string  $tableName
     * @param  array  $schedule
     * @return void
     */
    private function storeSchedule($tableName, $schedule)
    {
        try {
            // Using a transaction to ensure data integrity
            DB::beginTransaction();
            DB::table($tableName)->insert($schedule);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // Handle the error here or throw an exception
        }
    }

    /**
     * Show the loan input form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showLoanInputForm()
    {
        return view('loan_input_form');
    }

    /**
     * Calculate the effective interest rate for a mortgage loan.
     *
     * @param  float  $annualInterestRate
     * @param  int  $numberOfCompoundingPeriodsPerYear
     * @return float
     */
    private function calculateEffectiveInterestRate($annualInterestRate, $numberOfCompoundingPeriodsPerYear)
    {
        $monthlyInterestRate = $annualInterestRate / $numberOfCompoundingPeriodsPerYear;
        $effectiveInterestRate = pow(1 + $monthlyInterestRate, $numberOfCompoundingPeriodsPerYear) - 1;
        return $effectiveInterestRate * 100; // Convert to percentage
    }

    /**
     * Show the amortization and extra repayment schedule for a mortgage loan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
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

        // Calculate the Monthly Payment
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
            'effectiveInterestRate' => $effectiveInterestRate,
            'extraPayment' => $request->input('monthly_fixed_extra_payment'),
        ]);
    }
}
