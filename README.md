# miniaspire

#Installation

- Clone the repository to local
- Run `composer install`
- Setup .env from `.env.example` and add database
- Run `php artisan migrate`
- Run `php artisan jwt:secret`
- Run `php artisan serve`

# API

 We can use postman for testing API (https://www.postman.com/downloads/)

- create a user for login 
  - method - `post`
  - params - `name, email, password`
  - url`http://127.0.0.1:8000/api/register`
  
- login
  - method - `post`
  - params - `email, password`
  - url`http://127.0.0.1:8000/api/login`
  - response (token will generate)
  
- Create loan (use token in header)
  - method - `post`
  - params - `loan_amount, tenure, interest_rate`
  - url`http://127.0.0.1:8000/api/loan`

- Loan payment
  - method - `post`
  - params - `repayment_amount`
  - url`http://127.0.0.1:8000/api/payment`


# Third party package used

- https://jwt-auth.readthedocs.io/en/develop/laravel-installation/
