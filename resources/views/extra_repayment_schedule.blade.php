<!DOCTYPE html>
<html>
<head>
    <title>Extra Repayment Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden w-2/3">
        <h1 class="text-3xl font-bold text-center bg-indigo-600 text-white p-4">Extra Repayment Schedule</h1>
        @if(session('extraRepaymentSchedule'))
            <table class="table-auto w-full p-4">
                <thead>
                    <tr>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Month Number</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Starting Balance</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Monthly Payment</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Principal Component</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Interest Component</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Extra Repayment Made</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Ending Balance after Extra Repayment</th>
                        <th class="px-4 py-2 bg-indigo-600 text-white">Remaining Loan Term after Extra Repayment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (session('extraRepaymentSchedule') as $entry)
                        <tr>
                            <td class="border px-4 py-2">{{ $entry['month_number'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['starting_balance'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['monthly_payment'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['principal_component'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['interest_component'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['extra_repayment_made'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['ending_balance_after_extra_repayment'] }}</td>
                            <td class="border px-4 py-2">{{ $entry['remaining_loan_term_after_extra_repayment'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-center p-4">No extra repayment schedule data found.</p>
        @endif
    </div>
</body>
</html>
