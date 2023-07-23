<!-- resources/views/amortization_schedule.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Amortization Schedule</title>
</head>
<body>
    <h1>Amortization Schedule</h1>
    @if(session('amortizationSchedule'))
        <table>
            <thead>
                <tr>
                    <th>Month Number</th>
                    <th>Starting Balance</th>
                    <th>Monthly Payment</th>
                    <th>Principal Component</th>
                    <th>Interest Component</th>
                    <th>Ending Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach (session('amortizationSchedule') as $entry)
                    <tr>
                        <td>{{ $entry['month_number'] }}</td>
                        <td>{{ $entry['starting_balance'] }}</td>
                        <td>{{ $entry['monthly_payment'] }}</td>
                        <td>{{ $entry['principal_component'] }}</td>
                        <td>{{ $entry['interest_component'] }}</td>
                        <td>{{ $entry['ending_balance'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No amortization schedule data found.</p>
    @endif
</body>
</html>
