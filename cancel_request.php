<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

nh_require_post();
$user = nh_require_login();
if (!csrf_is_valid()) {
    nh_flash('Your session expired. Please try again.', 'err');
    nh_redirect('dashboard.php');
}
// The user_id condition makes it impossible to touch someone else's booking.
$s = nh_db()->prepare("UPDATE bookings SET cancellation_requested = 1
    WHERE id = ? AND user_id = ? AND status IN ('Pending','Confirmed')");
$s->execute([(int) nh_post('booking_id'), (int) $user['id']]);
if ($s->rowCount() > 0) {
    nh_flash('Cancellation requested. The hotel will confirm it shortly.');
} else {
    nh_flash('That booking cannot be cancelled online, or it was already requested.', 'err');
}
nh_redirect('dashboard.php');
