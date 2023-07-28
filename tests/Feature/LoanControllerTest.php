<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Http\Controllers\LoanController;
use Tests\TestCase;

class LoanControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    use RefreshDatabase;

    public function testCalculateMonthlyPayment()
    {
        $response = $this->postJson('/api/calculate-monthly-payment', [
            'loan_amount' => 100000,
            'annual_interest_rate' => 5,
            'loan_term' => 30,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'monthly_payment' => 536.82,
            ]);
    }

    public function testGenerateAmortizationSchedule()
    {
        $response = $this->postJson('/api/generate-amortization-schedule', [
            'loan_amount' => 100000,
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

    public function testGenerateExtraRepaymentSchedule()
    {
        $response = $this->postJson('/api/generate-extra-repayment-schedule', [
            'loan_amount' => 100000,
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

    public function testValidationForInvalidInput()
    {
        $invalidData = [
            'loan_amount' => -100000, // Negative loan amount
            'annual_interest_rate' => 'invalid', // Invalid interest rate
            'loan_term' => 'invalid', // Invalid loan term
        ];

        $response = $this->postJson('/api/calculate-monthly-payment', $invalidData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loan_amount', 'annual_interest_rate', 'loan_term']);

        $response = $this->postJson('/api/generate-amortization-schedule', $invalidData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loan_amount', 'annual_interest_rate', 'loan_term']);

        $response = $this->postJson('/api/generate-extra-repayment-schedule', $invalidData);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['loan_amount', 'annual_interest_rate', 'loan_term']);
    }
}
