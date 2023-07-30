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

    public function testShowAmortizationAndExtraRepaymentSchedule()
    {
        $requestData = [
            'loan_amount' => 100000,
            'annual_interest_rate' => 5,
            'loan_term' => 5,
            'monthly_fixed_extra_payment' => 200,
        ];

        // Make a POST request to the loan.schedule route with the sample data
        $response = $this->post(route('loan.schedule'), $requestData);

        // Assert that the response has a successful status code
        $response->assertStatus(200);

        // Assert that the response contains the expected data in the view
        $response->assertSee('Amortization Schedule');
        $response->assertSee('Recalculated Schedule');
        $response->assertSee('Loan Amount: $100,000');
        $response->assertSee('Annual Interest Rate: 5%');
        $response->assertSee('Loan Term: 5');
        $response->assertSee('Effective Interest Rate: 4.63% (after extra repayments)');
    }
}
