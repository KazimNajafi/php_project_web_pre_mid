<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$in = nh_get('check_in');
$out = nh_get('check_out');
$guestsRaw = nh_get('guests');
$typeId = (int) nh_get('type');
$searched = $in !== '' || $out !== '';
$error = '';
$rooms = [];

try {
    if ($searched) {
        [$ci, $co, $nights] = nh_stay($in, $out);
        $rooms = nh_available_rooms($ci, $co, nh_guests($guestsRaw === '' ? '1' : $guestsRaw), $typeId ?: null);
    } else {
        $sql = 'SELECT r.*, t.name AS type_name FROM rooms r JOIN room_types t ON t.id = r.room_type_id
                WHERE r.is_available = 1 AND t.is_active = 1' . ($typeId ? ' AND r.room_type_id = ?' : '') . '
                ORDER BY r.price_per_night, r.room_number';
        $s = nh_db()->prepare($sql);
        $s->execute($typeId ? [$typeId] : []);
        $rooms = $s->fetchAll();
    }
} catch (NhException $e) {
    $error = $e->getMessage();
}
$q = ['check_in' => $in, 'check_out' => $out, 'guests' => $guestsRaw];

nh_header('Rooms', '', 'rooms');
?>
<section class="wrap section">
  <p class="eyebrow">Accommodation</p><h2>Rooms &amp; availability</h2>
  <?php nh_search_form('rooms.php', $in, $out, $guestsRaw === '' ? '2' : $guestsRaw, $typeId ? ['type' => (string) $typeId] : []); ?>
  <?php if ($error !== ''): ?><p class="err" role="alert"><?= nh_e($error) ?></p>
  <?php elseif ($searched): ?><p class="muted" style="margin:16px 0"><?= count($rooms) ?> room(s) available for <?= nh_e($ci) ?> &rarr; <?= nh_e($co) ?> (<?= (int) $nights ?> night<?= $nights === 1 ? '' : 's' ?>).</p>
  <?php else: ?><p class="muted" style="margin:16px 0">Showing all rooms. Enter dates to see live availability.</p><?php endif; ?>

  <?php if (!$error && !$rooms): ?><div class="note">No rooms match. Try different dates, fewer guests, or another category.</div><?php endif; ?>
  <div class="grid" style="margin-top:18px">
    <?php foreach ($rooms as $r) { nh_room_card($r, $q); } ?>
  </div>
</section>
<?php nh_footer(); ?>
