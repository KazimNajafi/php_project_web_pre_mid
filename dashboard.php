<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$user = nh_require_login();
$s = nh_db()->prepare('SELECT b.*, r.name AS room_name, r.room_number, t.name AS type_name
    FROM bookings b JOIN rooms r ON r.id = b.room_id JOIN room_types t ON t.id = r.room_type_id
    WHERE b.user_id = ? ORDER BY b.check_in DESC, b.id DESC');
$s->execute([(int) $user['id']]);                       // always the logged-in user's id, never a browser value
$today = date('Y-m-d');
$groups = ['Upcoming reservations' => [], 'Current stays' => [], 'Previous bookings' => []];
foreach ($s->fetchAll() as $b) {
    if (in_array($b['status'], ['Cancelled', 'Rejected', 'Checked-Out'], true) || $b['check_out'] < $today) {
        $groups['Previous bookings'][] = $b;
    } elseif ($b['status'] === 'Checked-In' || $b['check_in'] <= $today) {
        $groups['Current stays'][] = $b;
    } else {
        $groups['Upcoming reservations'][] = $b;
    }
}
nh_header('My bookings', '', 'dash');
?>
<section class="wrap section">
  <p class="eyebrow">Your account</p><h2>Hello, <?= nh_e($user['username']) ?></h2>
  <div class="panel" style="margin-bottom:24px"><strong>Profile</strong>
    <p class="muted">Name: <?= nh_e($user['username']) ?> &middot; Email: <?= nh_e($user['email']) ?> &middot; Member since <?= nh_e(substr((string) $user['created_at'], 0, 10)) ?></p>
    <a class="btn btn-sm" href="rooms.php">Book a room</a></div>

  <?php foreach ($groups as $title => $list): ?>
    <h3><?= nh_e($title) ?> (<?= count($list) ?>)</h3>
    <?php if (!$list): ?><p class="muted">Nothing here.</p><?php else: ?>
    <div class="tbl-wrap"><table class="tbl"><thead><tr><th>Reference</th><th>Room</th><th>Dates</th><th>Total</th><th>Status</th><th>Payment</th><th></th></tr></thead><tbody>
    <?php foreach ($list as $b): ?><tr>
      <td><a href="booking_confirmation.php?ref=<?= nh_e(urlencode($b['reference'])) ?>"><?= nh_e($b['reference']) ?></a></td>
      <td><?= nh_e($b['room_name']) ?><br><span class="muted"><?= nh_e($b['type_name']) ?> #<?= nh_e($b['room_number']) ?></span></td>
      <td><?= nh_e($b['check_in']) ?> &rarr; <?= nh_e($b['check_out']) ?><br><span class="muted"><?= (int) $b['nights'] ?> night(s), <?= (int) $b['guests'] ?> guest(s)</span></td>
      <td><?= nh_e(nh_money($b['total_amount'])) ?></td>
      <td><?= nh_badge($b['status']) ?></td>
      <td><?= nh_badge($b['payment_status']) ?></td>
      <td><?php if (in_array($b['status'], ['Pending', 'Confirmed'], true)): ?>
        <?php if ((int) $b['cancellation_requested'] === 1): ?><span class="muted">Cancellation requested</span>
        <?php else: ?><form method="post" action="cancel_request.php" onsubmit="return confirm('Request cancellation of this booking?');">
          <?php nh_csrf(); ?><input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>"><button class="btn btn-sm btn-ghost" type="submit">Request cancellation</button></form>
        <?php endif; endif; ?></td>
    </tr><?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
  <?php endforeach; ?>
</section>
<?php nh_footer(); ?>
