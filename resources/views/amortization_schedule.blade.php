<!DOCTYPE html>
<html>
<head>
    <title>Amortization Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-2/3">
        <h1 class="text-3xl font-bold text-center bg-indigo-600 text-white p-4">Amortization Schedule</h1>
        @if(session('amortizationSchedule'))
            <div class="p-4">
                <h2 class="text-xl font-bold mb-2">Loan Setup Details:</h2>
                <p><strong>Loan Amount:</strong> ${{ session('loan_amount') }}</p>
                <p><strong>Annual Interest Rate:</strong> {{ session('annual_interest_rate') }}%</p>
                <p><strong>Loan Term (years):</strong> {{ session('loan_term') }}</p>
                <p><strong>Effective Interest Rate:</strong>
                    @foreach (session('effective_interest_rate') as $interestRate)
                        {{ $interestRate }}%
                        @if (!$loop->last)
                            ,
                        @endif
                    @endforeach
                </p>
            </div>
            <table class="table-auto w-full p-4">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Month Number</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Starting Balance</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Monthly Payment</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Principal Component</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Interest Component</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Ending Balance</th>
                        {{-- <th class="px-4 py-2 bg-indigo-600 text-white">Effective Interest Rate</th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach (session('amortizationSchedule') as $entry)
                        <tr>
                            <td class="border px-4 py-2">{{ number_format($entry['month_number'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['starting_balance'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['monthly_payment'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['principal_component'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['interest_component'], 2) }}</td>
                            <td class="border px-4 py-2">{{ number_format($entry['ending_balance'], 2) }}</td>
                            {{-- <td class="border px-4 py-2">{{ number_format($entry['effective_interest_rate'], 2) }}%</td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center p-4">No amortization schedule data found.</p>
        @endif
    </div>
</body>
</html>
