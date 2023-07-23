<!-- resources/views/loan_input_form.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Loan Input Form</title>
</head>
<body>
    <h1>Loan Input Form</h1>
    <form method="POST" action="{{ route('calculate.amortization.schedule') }}">
        @csrf
        <label for="loan_amount">Loan Amount:</label>
        <input type="number" name="loan_amount" required>

        <label for="annual_interest_rate">Annual Interest Rate:</label>
        <input type="number" name="annual_interest_rate" step="0.01" required>

        <label for="loan_term">Loan Term (years):</label>
        <input type="number" name="loan_term" required>

        <button type="submit">Calculate Amortization Schedule</button>
    </form>
</body>
</html>
