<!-- resources/views/loan_input_form.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Mortgage Loan Calculator</title>
    <!-- Add Tailwind CSS link here -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-md w-full p-6 bg-white rounded-lg shadow-md">
            <h1 class="text-2xl font-semibold mb-6 text-center">Mortgage Loan Calculator</h1>
            <form method="POST" action="{{ route('calculate.amortization.schedule') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="loan_amount" class="block text-sm font-medium text-gray-700">Loan Amount:</label>
                    <input type="number" name="loan_amount" class="w-full h-10 px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base text-gray-700" required>

                </div>

                <div>
                    <label for="annual_interest_rate" class="block text-sm font-medium text-gray-700">Annual Interest Rate (%):</label>
                    <input type="number" name="annual_interest_rate" class="w-full h-10 px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base text-gray-700" required>

                </div>

                <div>
                    <label for="loan_term" class="block text-sm font-medium text-gray-700">Loan Term (years):</label>
                    <input type="number" name="loan_term" class="w-full h-10 px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base text-gray-700" required>
                    
                </div>

                <div>
                    <label for="monthly_fixed_extra_payment" class="block text-sm font-medium text-gray-700">Monthly Fixed Extra Payment (optional):</label>
                    <input type="number" name="monthly_fixed_extra_payment" class="w-full h-10 px-4 py-2 mt-1 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 text-base text-gray-700">

                </div>

                <button type="submit" class="w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Calculate Amortization Schedule</button>
            </form>
        </div>
    </div>
</body>
</html>
