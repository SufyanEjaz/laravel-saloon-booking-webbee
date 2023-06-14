To run the code, migrate the database, seed the data, and execute the test cases, you can follow these steps:

Clone the repository: 
git clone https://github.com/SufyanEjaz/laravel-saloon-booking-webbee.git

Navigate to the project directory:
cd laravel-saloon-booking-webbee

Install dependencies:
composer install

Create a copy of the .env.example file and rename it to .env. Update the necessary configuration values in the .env file, such as database credentials.
Generate an application key:
php artisan key:generate

create a database in xampp/wampp having some name like "database_name" and change the the value of DB_DATABASE with the "database_name" in .env file 

Run the database migrations:
php artisan migrate

Seed the database with data:
php artisan db:seed

Execute the test cases:
php artisan test
