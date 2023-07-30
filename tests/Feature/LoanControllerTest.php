<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\LoanController;
use Tests\TestCase;

class LoanControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the calculate monthly payment endpoint.
     *
     * @return void
     */
    public function testCalculateMonthlyPayment()
    {
        $response = $this->json('POST', '/api/calculate-monthly-payment', [
            'loan_amount' => 200000,
            'annual_interest_rate' => 5,
            'loan_term' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'monthly_payment' => 1073.64,
            ]);
    }

    /**
     * Test the generate amortization schedule endpoint.
     *
     * @return void
     */
    public function testGenerateAmortizationSchedule()
    {
        $response = $this->json('POST', '/api/generate-amortization-schedule', [
            'loan_amount' => 200000,
            'annual_interest_rate' => 5,
            'loan_term' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'amortization_schedule' => [
                    '*' => [
                        'month_number',
                        'starting_balance',
                        'monthly_payment',
                        'principal_component',
                        'interest_component',
                        'ending_balance',
                    ],
                ],
            ]);
    }

    /**
     * Test the generate extra repayment schedule endpoint.
     *
     * @return void
     */
    public function testGenerateExtraRepaymentSchedule()
    {
        $response = $this->json('POST', '/api/generate-extra-repayment-schedule', [
            'loan_amount' => 200000,
            'annual_interest_rate' => 5,
            'loan_term' => 30,
            'monthly_fixed_extra_payment' => 100,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'extra_repayment_schedule' => [
                    '*' => [
                        'month_number',
                        'starting_balance',
                        'monthly_payment',
                        'principal_component',
                        'interest_component',
                        'extra_repayment_made',
                        'ending_balance_after_extra_repayment',
                        'remaining_loan_term_after_extra_repayment',
                    ],
                ],
            ]);
    }
}
