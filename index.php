<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/hotel.php';

$types = nh_db()->query(
    'SELECT t.id, t.name, t.description, MIN(r.price_per_night) AS min_price, MAX(r.capacity) AS max_cap, COUNT(r.id) AS rooms
     FROM room_types t LEFT JOIN rooms r ON r.room_type_id = t.id AND r.is_available = 1
     WHERE t.is_active = 1 GROUP BY t.id, t.name, t.description, t.sort_order
     ORDER BY t.sort_order, t.id'
)->fetchAll();

nh_header('Welcome', '', 'home');
?>
<section class="hero"><div class="wrap">
  <p class="eyebrow">Reserve online &middot; Pay at the hotel</p>
  <h1>A quiet place to rest,<br>beneath the Northstar.</h1>
  <p>Comfortable rooms, warm service and a simple booking process. Choose your dates, pick a room, and pay when you arrive.</p>
  <?php nh_search_form('rooms.php', '', '', '2'); ?>
</div></section>

<section class="wrap section">
  <p class="eyebrow">About us</p>
  <h2>Welcome to Northstar Hotel</h2>
  <p>Northstar is a single, independently run hotel focused on calm, comfortable stays for business and leisure travellers. Every reservation is confirmed by our reception team, and there is nothing to pay online.</p>
</section>

<section class="wrap section" style="padding-top:0">
  <p class="eyebrow">Accommodation</p>
  <h2>Our room categories</h2>
  <div class="grid">
  <?php foreach ($types as $t): ?>
    <article class="card"><div class="room-img"><span class="ph"><?= nh_e($t['name']) ?></span></div>
      <div class="card-b"><h3><?= nh_e($t['name']) ?></h3>
        <p class="muted"><?= nh_e($t['description']) ?></p>
        <?php if ((int) $t['rooms'] > 0): ?>
          <p class="price"><small>from</small> <?= nh_e(nh_money($t['min_price'])) ?> <small>/ night</small></p>
          <a class="btn btn-sm" href="rooms.php?type=<?= (int) $t['id'] ?>">See rooms</a>
        <?php else: ?><p class="muted">Currently no rooms listed.</p><?php endif; ?>
      </div></article>
  <?php endforeach; ?>
  </div>
</section>

<section class="dark section"><div class="wrap">
  <p class="eyebrow">Facilities &amp; services</p><h2>Everything you need</h2>
  <ul class="fac">
    <li style="color:var(--ink)">Free Wi-Fi</li><li style="color:var(--ink)">Air-conditioned rooms</li>
    <li style="color:var(--ink)">Daily breakfast</li><li style="color:var(--ink)">24-hour reception</li>
    <li style="color:var(--ink)">Secure parking</li><li style="color:var(--ink)">Laundry service</li>
    <li style="color:var(--ink)">Airport pickup on request</li><li style="color:var(--ink)">Meeting space</li>
  </ul>
</div></section>

<section class="wrap section" id="contact">
  <p class="eyebrow">Contact</p><h2>Get in touch</h2>
  <div class="panel"><p><strong>Address:</strong> <?= nh_e(NH_ADDRESS) ?></p>
    <p><strong>Phone:</strong> <?= nh_e(NH_PHONE) ?></p><p><strong>Email:</strong> <?= nh_e(NH_EMAIL) ?></p>
    <p class="muted">Payment policy: no online payment is taken. Your reservation starts as <em>Unpaid</em>; pay at the hotel reception during your stay.</p></div>
</section>
<?php nh_footer(); ?>
