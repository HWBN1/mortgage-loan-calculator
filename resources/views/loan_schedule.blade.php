<!DOCTYPE html>
<html>

<head>
    <title>Loan Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-2/3">
        <h1 class="text-3xl font-bold text-center bg-indigo-600 text-white p-4">Loan Schedule</h1>



        <div class="p-4">
            <h2 class="text-xl font-bold mb-2">Loan Setup Details:</h2>
            <p><strong>Loan Amount:</strong> ${{ $loanAmount }}</p>
            <p><strong>Annual Interest Rate:</strong> {{ $annualInterestRate }}%</p>
            <p><strong>Loan Term (years):</strong> {{ $loanTerm }}</p>
            <p><strong>Effective Interest Rate:</strong> {{ number_format($effectiveInterestRate, 2) }}% (after extra
                repayments)</p>
        </div>

        <h2 class="text-2xl font-bold mb-4 mt-4 text-center">Amortization Schedule</h2>
        <table class="table-auto w-full p-4">
            <thead>
                <tr>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Month</th>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Starting Balance</th>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Monthly Payment</th>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Principal</th>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Interest</th>
                    <th class="px-4 py-2 bg-indigo-600 text-white">Ending Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($amortizationSchedule as $entry)
                    <tr>
                        <td class="border px-4 py-2">{{ number_format($entry['month_number'], 2) }}</td>
                        <td class="border px-4 py-2">{{ number_format($entry['starting_balance'], 2) }}</td>
                        <td class="border px-4 py-2">{{ number_format($entry['monthly_payment'], 2) }}</td>
                        <td class="border px-4 py-2">{{ number_format($entry['principal_component'], 2) }}</td>
                        <td class="border px-4 py-2">{{ number_format($entry['interest_component'], 2) }}</td>
                        <td class="border px-4 py-2">{{ number_format($entry['ending_balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if ($extraPayment > 0)
            <h2 class="text-2xl font-bold mb-4 mt-8 text-center">Recalculated Schedule</h2>
            <table class="table-auto w-full p-4">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Month</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Starting Balance</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Monthly Payment</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Principal</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Interest</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Extra Repayment</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Ending Balance</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Remaining Loan Term</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($extraRepaymentSchedule as $entry)
                        <tr>
                            <td class="border px-4 py-2">{{ number_format($entry['month_number'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['starting_balance'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['monthly_payment'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['principal_component'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['interest_component'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['extra_repayment_made'], 2) }}</td>
                            <td class="border px-4 py-2">
                                {{ number_format($entry['ending_balance_after_extra_repayment'], 2) }}</td>
                            <td class="border px-4 py-2">
                                {{ number_format($entry['remaining_loan_term_after_extra_repayment'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</body>

</html>
