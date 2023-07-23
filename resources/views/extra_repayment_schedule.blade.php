@extends('layouts.app')

@section('content')
    <h1>Extra Repayment Schedule</h1>
    <table>
        <thead>
            <tr>
                <th>Month</th>
                <th>Starting Balance</th>
                <th>Monthly Payment</th>
                <th>Principal Component</th>
                <th>Interest Component</th>
                <th>Extra Repayment Made</th>
                <th>Ending Balance After Extra Repayment</th>
                <th>Remaining Loan Term After Extra Repayment</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($extraRepaymentSchedule as $entry)
                <tr>
                    <td>{{ $entry['month_number'] }}</td>
                    <td>{{ $entry['starting_balance'] }}</td>
                    <td>{{ $entry['monthly_payment'] }}</td>
                    <td>{{ $entry['principal_component'] }}</td>
                    <td>{{ $entry['interest_component'] }}</td>
                    <td>{{ $entry['extra_repayment_made'] }}</td>
                    <td>{{ $entry['ending_balance_after_extra_repayment'] }}</td>
                    <td>{{ $entry['remaining_loan_term_after_extra_repayment'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
