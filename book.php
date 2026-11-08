<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$src = static fn(string $k): string => $isPost ? nh_post($k) : nh_get($k);

$user = nh_user();
if ($user === null) {                       // send guests to sign in, then bring them back (see home.php)
    if (!$isPost) {
        $_SESSION['nh_return_to'] = 'book.php?' . http_build_query($_GET);
    }
    nh_flash('Please sign in or register to complete your booking.');
    nh_redirect('login.php');
}

$roomId = (int) $src('room_id');
$in = $src('check_in');
$out = $src('check_out');
$guestsRaw = $src('guests');
$fatal = '';
$room = null;
$stay = null;
$g = 1;
try {
    $stay = nh_stay($in, $out);
    $g = nh_guests($guestsRaw);
    $found = nh_available_rooms($stay[0], $stay[1], $g, null, $roomId);
    if ($found === []) {
        throw new NhException('This room is not available for the selected dates and number of guests.');
    }
    $room = $found[0];
} catch (NhException $e) {
    $fatal = $e->getMessage();
}

$name = $isPost ? nh_post('guest_name') : (string) $user['username'];
$phone = nh_post('guest_phone');
$notes = nh_post('special_requests');
$errors = [];

if ($isPost && $room !== null) {
    $token = (string) ($_SESSION['nh_book_token'] ?? '');
    if (!csrf_is_valid()) {
        $errors[] = 'Your session expired. Refresh the page and try again.';
    } elseif ($token === '' || !hash_equals($token, nh_post('form_token'))) {
        nh_flash('That booking form was already submitted or has expired. Check "My bookings" before trying again.', 'err');
        nh_redirect('dashboard.php');
    } else {
        if ($name === '' || mb_strlen($name) > 120) {
            $errors[] = 'Enter the guest name (up to 120 characters).';
        }
        if (preg_match('/^[0-9+\-\s()]{7,20}$/', $phone) !== 1) {
            $errors[] = 'Enter a valid phone number.';
        }
        if (mb_strlen($notes) > 500) {
            $errors[] = 'Special requests must be 500 characters or fewer.';
        }
        if (!$errors) {
            unset($_SESSION['nh_book_token']);          // one-time token: blocks double submits
            try {
                $b = nh_create_booking((int) $user['id'], (int) $room['id'], $in, $out, $g, $name, $phone, $notes);
                nh_flash('Your reservation has been received.');
                nh_redirect('booking_confirmation.php?ref=' . urlencode($b['reference']));
            } catch (NhException $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}
if (empty($_SESSION['nh_book_token'])) {
    $_SESSION['nh_book_token'] = bin2hex(random_bytes(16));
}

nh_header('Review your booking', '', 'rooms');
?>
<section class="wrap section">
<?php if ($fatal !== ''): ?>
  <h2>Booking unavailable</h2><p class="err"><?= nh_e($fatal) ?></p><a class="btn" href="rooms.php">Back to rooms</a>
<?php else: $price = (float) $room['price_per_night']; $total = $price * $stay[2]; ?>
  <p class="eyebrow">Step 2 of 2</p><h2>Review &amp; confirm your reservation</h2>
  <div class="two">
    <form class="panel form" method="post" action="book.php">
      <?php nh_csrf(); ?>
      <input type="hidden" name="form_token" value="<?= nh_e($_SESSION['nh_book_token']) ?>">
      <input type="hidden" name="room_id" value="<?= (int) $room['id'] ?>">
      <input type="hidden" name="check_in" value="<?= nh_e($stay[0]) ?>">
      <input type="hidden" name="check_out" value="<?= nh_e($stay[1]) ?>">
      <input type="hidden" name="guests" value="<?= $g ?>">
      <h3 style="margin:0">Guest information</h3>
      <?php foreach ($errors as $er): ?><p class="err" role="alert"><?= nh_e($er) ?></p><?php endforeach; ?>
      <label>Full name of main guest<input type="text" name="guest_name" maxlength="120" value="<?= nh_e($name) ?>" required></label>
      <label>Phone number<input type="tel" name="guest_phone" maxlength="20" value="<?= nh_e($phone) ?>" placeholder="+92 300 1234567" required></label>
      <label>Special requests (optional)<textarea name="special_requests" rows="3" maxlength="500"><?= nh_e($notes) ?></textarea></label>
      <div class="note"><strong>Payment at the hotel.</strong> No online payment is taken. Your payment status will be <em>Unpaid</em> until you pay at reception; staff will mark it as paid.</div>
      <button class="btn" type="submit">Confirm reservation</button>
    </form>
    <aside class="panel">
      <p class="eyebrow"><?= nh_e($room['type_name']) ?> &middot; Room <?= nh_e($room['room_number']) ?></p>
      <h3 style="margin-top:0"><?= nh_e($room['name']) ?></h3>
      <table class="sum">
        <tr><td>Check-in</td><td><?= nh_e($stay[0]) ?></td></tr>
        <tr><td>Check-out</td><td><?= nh_e($stay[1]) ?></td></tr>
        <tr><td>Nights</td><td><?= $stay[2] ?></td></tr>
        <tr><td>Guests</td><td><?= $g ?></td></tr>
        <tr><td>Price per night</td><td><?= nh_e(nh_money($price)) ?></td></tr>
        <tr><td><strong>Total</strong></td><td><strong><?= nh_e(nh_money($total)) ?></strong></td></tr>
      </table>
      <p class="muted">The total is recalculated on our server when you confirm.</p>
    </aside>
  </div>
<?php endif; ?>
</section>
<?php nh_footer(); ?>
