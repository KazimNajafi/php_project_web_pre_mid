# SmartDine Product Requirements Document (PRD)

## 1. Project title and overview

**Project title:** SmartDine — Online Restaurant Management System

SmartDine is a web-based restaurant management platform that helps a restaurant business manage customer sign-up, secure authentication, menu browsing, reservation handling, and order flow in a simple and student-friendly way. The current implementation focuses on the core user management and authentication module, while the broader product vision includes booking, menu management, and order processing.

## 2. Problem statement and proposed solution

Many small restaurants still rely on manual processes for customer registration, table reservations, and order tracking. This creates delays, inconsistent customer records, and limited digital visibility. SmartDine proposes a simple online system where customers can create accounts, sign in securely, browse restaurant information, and interact with restaurant services in a modern, accessible web application.

## 3. Project goals and measurable objectives

### Primary goals

- Provide secure user registration and login.
- Offer a clean, responsive home page for customers.
- Protect authenticated pages with server-side session checks.
- Use a normalized MySQL database for future restaurant features.

### Measurable objectives

- 100% of authentication forms must validate data on the server side.
- 100% of passwords must be stored as hashed values.
- 100% of database queries involving user input must use prepared statements.
- Pages requiring login must redirect unauthorized users to the login page.
- Application pages must be correctly displayed on mobile and desktop screens.

## 4. Target users and stakeholders

### Primary target users

- Restaurant customers
- Restaurant staff
- Admin users

### Stakeholders

- Student developer and project evaluator
- Restaurant owner or manager
- Web technology teacher

## 5. Scope

### Included in current submission

- Public home page
- Sign-up page
- Login page
- Authenticated dashboard
- Logout flow
- MySQL schema design
- PHP session-based authentication
- Security features such as CSRF, prepared statements, password hashing, and session validation

### Excluded from current submission (future enhancement)

- Online food ordering workflow
- Payment gateway integration
- Admin dashboard with menu editing
- Staff order management
- Reservation approval system
- Live delivery tracking
- Advanced analytics and reports

## 6. Functional requirements

- FR-01: The system shall allow a new customer to create an account with name, email, and password.
- FR-02: The system shall validate that all required fields are filled.
- FR-03: The system shall validate email format before registration.
- FR-04: The system shall reject duplicate email addresses.
- FR-05: The system shall require an acceptable password policy before registration.
- FR-06: The system shall hash passwords using PHP password hashing functions before storing them.
- FR-07: The system shall authenticate users using email and password.
- FR-08: The system shall start and manage PHP sessions securely after login.
- FR-09: The system shall redirect logged-in users away from signup and login pages.
- FR-10: The system shall protect authenticated pages with a server-side login check.
- FR-11: The system shall allow users to log out and destroy their session.
- FR-12: The system shall show clear success and error feedback to users.
- FR-13: The system shall display a public home page for guests.
- FR-14: The system shall display a personalized dashboard for logged-in users.

## 7. Non-functional requirements

### Security

- Use PHP `password_hash()` and `password_verify()`.
- Use prepared statements for all database queries involving user input.
- Escape output with `htmlspecialchars()`.
- Protect state-changing forms with CSRF tokens.
- Avoid hard-coded database credentials in public files.

### Usability

- Use responsive layouts across desktop and mobile screens.
- Use readable form labels and error messages.
- Use clear navigation for guest and authenticated users.

### Performance

- Use efficient database queries with indexes on common search fields.
- Keep page logic simple and readable for educational purposes.

### Maintainability

- Separate configuration, database logic, and page logic into reusable files.
- Keep styling and JavaScript in dedicated asset folders.

### Responsiveness

- Use HTML5, CSS3, and Flexbox/Grid layouts.
- Ensure forms, cards, and navigation adapt to smaller screens.

## 8. User roles and permissions

| Role | Description | Permissions |
|------|-------------|-------------|
| Customer | Standard application user | Register, log in, view home, access dashboard |
| Staff | Restaurant operations support | Manage reservations and service tasks (future scope) |
| Admin | System manager | Full administrative access (future scope) |

## 9. User stories and acceptance criteria

### User story 01

As a customer, I want to sign up with my details so that I can create an account.

**Acceptance criteria:**
- The user enters name, email, password, and confirmation.
- The app validates required fields and email format.
- Duplicate emails are rejected.
- A success message appears after account creation.

### User story 02

As a customer, I want to log in so that I can access my personal dashboard.

**Acceptance criteria:**
- The user submits valid credentials.
- The app verifies the password.
- The user is redirected to the dashboard.
- Session data is saved securely.

### User story 03

As a user, I want to log out so that my session is securely ended.

**Acceptance criteria:**
- Session variables are cleared.
- Session cookie is removed.
- The user is redirected to the login or public home page.

## 10. Use cases and authentication flow

### Main use cases

- Create user account
- Login to application
- Redirect logged-in user to dashboard
- Access protected dashboard
- Log out
- Display validation errors

### Authentication flow

1. User opens sign-up page.
2. User submits valid registration data.
3. Server validates fields and checks duplicate emails.
4. Password is hashed and saved in the database.
5. User logs in using email and password.
6. Server verifies credentials and creates a secure session.
7. User is redirected to the dashboard.
8. Protected pages check session status before showing content.

## 11. Page-by-page frontend requirements

### Public home page

- Hero section with restaurant branding
- Calls to action for sign-up and login
- Navigation bar for guest users
- Responsive layout with cards and sections

### Sign-up page

- Name, email, password, and password confirmation fields
- Inline or message-based validation
- Link to login page

### Login page

- Email and password fields
- Generic invalid credential message
- Link to sign-up page

### Authenticated home/dashboard page

- Personalized greeting
- Account information display
- Logout option
- Navigation for protected features

## 12. System architecture and request/response flow

SmartDine follows a simple three-layer architecture:

- Frontend: HTML, CSS, and JavaScript
- Backend: PHP scripts handling logic
- Database: MySQL storing authentication and restaurant data

### Request/response flow

1. User submits a form in a browser.
2. PHP script receives the POST request.
3. Validation and database checks run on the server.
4. The script either renders a page or redirects the user.
5. Sessions store authenticated user state.
6. Future requests read session data to authorize access.

## 13. Technology stack and justification

- HTML5 — semantic page structure
- CSS3 — layout, styling, responsiveness
- Vanilla JavaScript — lightweight form enhancement and UI behavior
- PHP — server-side logic and session management
- MySQL — relational data storage
- Apache — web server for local development

This stack fits the assignment requirement and is easy for a student to understand and explain.

## 14. Database design summary

The database includes a normalized set of tables to support both current authentication needs and future restaurant features. The core user table stores user profiles and hashed credentials. Additional restaurant-related tables are included for categories, menu items, reservations, orders, and order items.

## 15. Validation and error-handling rules

- Required fields must be checked server-side.
- Email address must match valid format.
- Password must meet the minimum accepted strength.
- Duplicate emails are rejected.
- Incorrect login details show a generic message.
- Data is escaped before being rendered on the page.
- Database exceptions are handled gracefully.

## 16. Security requirements

- Passwords are never stored in plain text.
- Session fixation is prevented by regenerating the session ID after login.
- Prepared statements are used for all database access with user input.
- CSRF tokens protect state-changing forms.
- Sensitive configuration is stored using environment variables instead of hard-coded values.

## 17. Testing strategy

- Validate page rendering using local PHP server and browser.
- Check server-side validation for required fields, email format, and duplicate users.
- Test successful and failed login attempts.
- Confirm session creation and logout cleanup.
- Check redirect behavior for protected pages.
- Run PHP lint checks to ensure syntax validity.

## 18. Known limitations, assumptions, and future enhancements

### Current limitations

- Current project focuses on authentication and a responsive landing experience.
- Payment processing is not implemented.
- Admin and staff modules are future features.

### Assumptions

- Users will access the app through a local Apache/PHP environment.
- MySQL is available on the development machine.
- The project is intended for educational demonstration and learning.

### Future enhancements

- Online ordering flow
- Reservation system
- Menu management
- Inventory tracking
- Admin analytics dashboard
- Deployment to a live hosting environment

## Current submission vs future roadmap

The current submission is intentionally limited to the authentication and responsive UI foundation. The broader restaurant management system can be built on this foundation in future iterations without changing the core design substantially.
