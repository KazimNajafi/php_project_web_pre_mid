# Northstar Hotel – Upgrade Pack

Adds a single-hotel room-booking system to the existing `php_project_web_pre_mid` project
(PHP 8 + PDO + MySQL/MariaDB, sessions, Bootstrap-free custom CSS). Pay-at-hotel only.

## IMPORTANT – read first (what has and has not been verified)

* I **could not run any of this code**. My environment had no PHP and no MySQL, so nothing was executed or even linted (`php -l`).
  Please run the checks in section 6 before relying on it.
* I could read `signin.php`, `home.php`, `schema.sql` and the README from your repo. GitHub blocked me from reading
  `config/app.php`, `config/db.php`, `login.php` and `logout.php`. The new code only depends on what the files I *could* read show:
  `config/app.php` starts the session and provides `csrf_token()` / `csrf_is_valid()`; `config/db.php` **returns a PDO**;
  `$_SESSION['user_id']`; `logout.php` accepts a POST with `csrf_token`. If any of those assumptions is wrong, the first page load will tell you.
* **No config file was changed.** No new DB connection file, no new session start, no new credentials.

## 1. Install (Windows/XAMPP; Linux is the same idea)

1. Put your existing project folder in `htdocs` (e.g. `htdocs/php project`).
2. **Copy the contents of this pack into the project root**, merging folders. It adds new files and replaces only `home.php`
   (the original code is kept; one clearly marked block was inserted – see section 5).
3. Start Apache and MySQL in XAMPP.
4. phpMyAdmin → import `schema.sql` (only if you do not already have the database), then import **`upgrade.sql`**.
   `upgrade.sql` is re-runnable; it keeps your `users`/`items` data and only adds a `role` column plus the hotel tables and 9 sample rooms.
5. Database environment variables: unchanged from your original README (`DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASSWORD` via Apache `SetEnv`,
   then restart Apache). `.env.example` is a template only; the app does not read a `.env` file. Never commit real credentials.
6. Make sure `uploads/rooms/` is writable by Apache, and `extension=fileinfo` is enabled in `php.ini` (default in XAMPP).
7. Open `http://localhost/<your folder>/index.php`.

## 2. Create the first admin

1. Register normally at `signin.php`.
2. In phpMyAdmin run (use your own email):
   `UPDATE users SET role = 'admin' WHERE email = 'you@example.com';`
3. Sign in again → you are redirected to `admin/index.php`. Admin rights are read from the database on every request, never from the browser.

## 3. How a customer books

Home → enter dates/guests → pick a room → *Continue to booking* → sign in or register (you are returned to the booking) →
enter guest name/phone → review the server-calculated total → *Confirm reservation* → booking reference shown
(`NS-XXXXXXXXXX`). Reservation starts **Pending / Unpaid**. Manage bookings and request cancellation under *My bookings*.

## 4. How the admin works

* **Reservations**: search by reference/name/email; Confirm, Reject, Check in, Check out, Cancel (allowed transitions only).
* **Payments**: *Mark paid* with method and date → creates a `payments` record and sets payment status to Paid. Amount comes from the stored booking total.
* **Rooms / Categories**: add/edit rooms, price, image upload, enable/disable; categories are database rows, not hardcoded.

Statuses: `Pending, Confirmed, Checked-In, Checked-Out, Cancelled, Rejected`. Payment: `Unpaid, Paid` (no partial/refund).
Only Pending, Confirmed and Checked-In block a room.

## 5. Files

**New:** `upgrade.sql`, `.env.example`, `includes/hotel.php`, `assets/hotel.css`, `assets/hotel.js`, `index.php`, `rooms.php`, `room.php`, `book.php`,
`booking_confirmation.php`, `dashboard.php`, `cancel_request.php`, `admin/{index,rooms,types,bookings,payments}.php`, `uploads/rooms/.htaccess`.
**Modified:** `home.php` only – block marked `NORTHSTAR UPGRADE` redirects admins to `admin/`, customers to `dashboard.php` (or back to a pending booking). Original code is below it, now unreachable for valid users.
**Removed:** none. **Config changes:** none. DB change: `users.role` added (default `customer`, so existing accounts keep working).

## 6. Testing – status: NOT RUN

Run `php -l` on every file first (`for f in $(find . -name "*.php"); do php -l "$f"; done`). Then walk through this checklist
(every row is currently **untested**):

| # | Test | Expected |
|---|------|----------|
| 1 | Register, log out, log in, wrong password | works / rejected; `dashboard.php` redirects to login when signed out |
| 2 | Old demo user `demo@example.com` still logs in | yes, lands on dashboard |
| 3 | Search with past date, or checkout ≤ checkin | error message, no results |
| 4 | Search 5 guests | only rooms with capacity ≥ 5 (rooms 402) |
| 5 | Book room 101 for 2 nights | reference created; total = 2 × price; status Pending/Unpaid |
| 6 | Search the same dates again | room 101 no longer listed |
| 7 | Second user tries to book 101 for overlapping dates (URL from step 5 saved) | "just booked" error |
| 8 | Admin cancels/rejects that booking | room 101 listed again |
| 9 | Back-to-back dates (checkout day = next check-in) | allowed |
| 10 | Admin raises room price; old booking total | old booking unchanged |
| 11 | Customer opens another customer's `booking_confirmation.php?ref=...` | 404 |
| 12 | Customer opens `/admin/index.php` | 403 |
| 13 | Double-click "Confirm reservation" / refresh after submit | only one booking |
| 14 | Admin marks paid; try again | one payment record; second attempt refused |
| 15 | Upload a `.php` renamed to `.jpg` | rejected |
| 16 | Two browsers book the same room simultaneously | exactly one succeeds (needs InnoDB) |

## 7. Known limitations

* Untested (see above). No automated test suite.
* No stock hotel photos are bundled (no internet in my environment): rooms without an upload show a styled placeholder; the hero is a CSS gradient. Upload images in Admin → Rooms.
* One image per room. "Number of rooms" per booking is not supported (one booking = one room; a family needing two rooms makes two bookings).
* Check-in/out buttons are not date-gated. No partial payments/refunds, no emails, no password reset (not in the original project either).
* Currency label is `PKR` and contact details/address are placeholders: edit the constants at the top of `includes/hotel.php`.
* The overlap guarantee relies on InnoDB row locking (`SELECT … FOR UPDATE` on the room), not a DB constraint (MySQL has none for date ranges).
* Registration/login rate limiting is not added.
