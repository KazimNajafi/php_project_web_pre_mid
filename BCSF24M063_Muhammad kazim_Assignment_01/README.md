# SmartDine — Online Restaurant Management System

SmartDine is a student-friendly restaurant management web application built with PHP, MySQL, HTML5, CSS3, and vanilla JavaScript. The current implementation includes a complete authentication flow: sign-up, login, protected dashboard, and logout.

## Features included

- Responsive public home page
- User registration with server-side validation
- Login with password verification and session handling
- Protected authenticated dashboard
- Logout with session cleanup
- MySQL-ready schema and documentation
- PHP prepared statements and CSRF protection for state-changing forms

## Project structure

- `docs/` — product requirements and database design
- `database/` — MySQL schema
- `assets/css/` — styling
- `assets/js/` — frontend behavior
- `includes/` — reusable PHP logic
- `index.php` — public home page
- `signup.php` — registration page
- `login.php` — login page
- `home.php` — protected user dashboard
- `logout.php` — session invalidation

## Local setup

1. Make sure PHP and Apache/MySQL are available in your local environment.
2. Create a database named `smartdine` in MySQL.
3. Import the SQL from `database/schema.sql`.
4. Set local environment variables before running the app:

   ```bash
   set SMARTDINE_DB_HOST=127.0.0.1
   set SMARTDINE_DB_NAME=smartdine
   set SMARTDINE_DB_USER=root
   set SMARTDINE_DB_PASS=
   ```

5. Start a local PHP server from the project root:

   ```bash
   php -S localhost:8000
   ```

6. Open `http://localhost:8000` in your browser.

## Notes

- Credentials are not stored in the repository.
- All authentication database queries use prepared statements.
- Passwords are hashed with `password_hash()` and verified with `password_verify()`.
