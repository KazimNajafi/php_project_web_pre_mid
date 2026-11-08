<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$id = (int) nh_get('id');
$s = nh_db()->prepare('SELECT r.*, t.name AS type_name FROM rooms r JOIN room_types t ON t.id = r.room_type_id
                       WHERE r.id = ? AND t.is_active = 1');
$s->execute([$id]);
$room = $s->fetch();
if (!$room) {
    http_response_code(404);
    exit('Room not found. <a href="rooms.php">Back to rooms</a>');
}

$in = nh_get('check_in');
$out = nh_get('check_out');
$guestsRaw = nh_get('guests') ?: '2';
$error = '';
$free = null;       // null = dates not checked yet
$stay = null;
if ($in !== '' || $out !== '') {
    try {
        $stay = nh_stay($in, $out);
        $g = nh_guests($guestsRaw);
        if ((int) $room['is_available'] !== 1) {
            $free = false;
        } elseif ($g > (int) $room['capacity']) {
            throw new NhException('This room holds up to ' . (int) $room['capacity'] . ' guests.');
        } else {
            $free = nh_available_rooms($stay[0], $stay[1], $g, null, $id) !== [];
        }
    } catch (NhException $e) {
        $error = $e->getMessage();
    }
}

nh_header($room['name'], '', 'rooms');
?>
<section class="wrap section"><div class="two">
  <div>
    <div class="room-img" style="border-radius:14px"><?= nh_room_visual($room) ?></div>
    <p class="eyebrow" style="margin-top:18px"><?= nh_e($room['type_name']) ?> &middot; Room <?= nh_e($room['room_number']) ?></p>
    <h2 style="margin-top:0"><?= nh_e($room['name']) ?></h2>
    <p><?= nl2br(nh_e($room['description'])) ?></p>
    <p><strong>Beds:</strong> <?= nh_e($room['beds']) ?><br><strong>Capacity:</strong> up to <?= (int) $room['capacity'] ?> guests</p>
    <?php if ($room['amenities'] !== ''): ?>
      <h3>Amenities</h3><ul class="fac"><?php foreach (array_filter(array_map('trim', explode(',', $room['amenities']))) as $a): ?><li><?= nh_e($a) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
  <aside class="panel">
    <p class="price"><?= nh_e(nh_money($room['price_per_night'])) ?> <small>/ night</small></p>
    <?php if ((int) $room['is_available'] !== 1): ?><p class="err">This room is currently not available for booking.</p><?php endif; ?>
    <?php nh_search_form('room.php', $in, $out, $guestsRaw, ['id' => (string) $id]); ?>
    <?php if ($error !== ''): ?><p class="err" role="alert"><?= nh_e($error) ?></p>
    <?php elseif ($free === true): ?>
      <?php $n = $stay[2]; ?>
      <p class="flash flash-ok">Available! <?= $n ?> night<?= $n === 1 ? '' : 's' ?> &times; <?= nh_e(nh_money($room['price_per_night'])) ?> = <strong><?= nh_e(nh_money((float) $room['price_per_night'] * $n)) ?></strong></p>
      <a class="btn" href="book.php?<?= nh_e(http_build_query(['room_id' => $id, 'check_in' => $stay[0], 'check_out' => $stay[1], 'guests' => $guestsRaw])) ?>">Continue to booking</a>
    <?php elseif ($free === false): ?><p class="err">Sorry, this room is not available for those dates.</p><?php endif; ?>
    <p class="muted" style="margin-top:14px">Pay at the hotel &mdash; no online payment needed.</p>
  </aside>
</div></section>
<?php nh_footer(); ?>
