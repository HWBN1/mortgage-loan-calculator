# Mortgage Loan Calculator Application

This is a web application built with Laravel that allows users to calculate mortgage loan details, generate amortization schedules, and simulate extra repayments.

## Prerequisites

Make sure you have the following installed on your machine:

- Docker ([Install Docker](https://docs.docker.com/get-docker/))
- Docker Compose ([Install Docker Compose](https://docs.docker.com/compose/install/))

## Getting Started

1. Clone the repository:

```bash
git clone https://github.com/your-username/mortgage-loan-calculator.git
cd mortgage-loan-calculator
``` 

2.  Build and start the Docker containers:

`docker-compose up -d` 

3.  Install Composer dependencies:

`docker-compose exec app composer install` 

4.  Create the .env file:

`cp .env.example .env` 

5.  Generate the Laravel application key:

`docker-compose exec app php artisan key:generate` 

6.  Run the database migrations:

`docker-compose exec app php artisan migrate` 

## Usage

To access the application, open your web browser and go to [http://localhost:8000](http://localhost:8000/).

## API Endpoints

The application provides the following API endpoints for loan calculations:

### Calculate Monthly Payment

Endpoint: POST /api/calculate-monthly-payment

Parameters:

-   loan_amount (numeric, required): The loan amount (principal).
-   annual_interest_rate (numeric, required): The annual interest rate (as a percentage).
-   loan_term (numeric, required): The loan term in years.

### Generate Amortization Schedule

Endpoint: POST /api/generate-amortization-schedule

Parameters:

-   loan_amount (numeric, required): The loan amount (principal).
-   annual_interest_rate (numeric, required): The annual interest rate (as a percentage).
-   loan_term (numeric, required): The loan term in years.

### Generate Extra Repayment Schedule

Endpoint: POST /api/generate-extra-repayment-schedule

Parameters:

-   loan_amount (numeric, required): The loan amount (principal).
-   annual_interest_rate (numeric, required): The annual interest rate (as a percentage).
-   loan_term (numeric, required): The loan term in years.
-   monthly_fixed_extra_payment (numeric, optional): Monthly fixed extra payment amount (default: 0).

## Running Unit Tests

To run the unit tests, use the following command:

`docker-compose exec app php artisan test` 

## Stopping the Application

To stop and remove the Docker containers, run the following command:

`docker-compose down` 

## License

This project is open-source and available under the MIT License.
