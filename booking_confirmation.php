<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$user = nh_require_login();
$s = nh_db()->prepare('SELECT b.*, r.name AS room_name, r.room_number, t.name AS type_name
    FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN room_types t ON t.id = r.room_type_id
    WHERE b.reference = ? AND (b.user_id = ? OR ? = 1)');
$s->execute([nh_get('ref'), (int) $user['id'], $user['role'] === 'admin' ? 1 : 0]);
$b = $s->fetch();
if (!$b) {                                   // other people's bookings look identical to non-existent ones
    http_response_code(404);
    exit('Booking not found. <a href="dashboard.php">My bookings</a>');
}
nh_header('Booking confirmation', '', 'dash');
?>
<section class="wrap section">
  <p class="eyebrow">Reservation received</p><h2>Thank you, <?= nh_e($b['guest_name']) ?>!</h2>
  <p>Your booking reference:</p><p><span class="ref"><?= nh_e($b['reference']) ?></span></p>
  <div class="panel" style="max-width:640px">
    <table class="sum">
      <tr><td>Room</td><td><?= nh_e($b['room_name']) ?> (<?= nh_e($b['type_name']) ?>, #<?= nh_e($b['room_number']) ?>)</td></tr>
      <tr><td>Check-in</td><td><?= nh_e($b['check_in']) ?></td></tr>
      <tr><td>Check-out</td><td><?= nh_e($b['check_out']) ?></td></tr>
      <tr><td>Guests / nights</td><td><?= (int) $b['guests'] ?> / <?= (int) $b['nights'] ?></td></tr>
      <tr><td>Price per night</td><td><?= nh_e(nh_money($b['price_per_night'])) ?></td></tr>
      <tr><td><strong>Total</strong></td><td><strong><?= nh_e(nh_money($b['total_amount'])) ?></strong></td></tr>
      <tr><td>Reservation status</td><td><?= nh_badge($b['status']) ?></td></tr>
      <tr><td>Payment</td><td><?= nh_e($b['payment_method']) ?> &middot; <?= nh_badge($b['payment_status']) ?></td></tr>
    </table>
    <div class="note"><strong>Pay at the hotel.</strong> Please quote your reference at reception. Our team will confirm your reservation shortly.</div>
    <a class="btn" href="dashboard.php">Go to my bookings</a>
  </div>
</section>
<?php nh_footer(); ?>
