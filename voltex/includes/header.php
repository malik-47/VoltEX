<?php
require_once __DIR__ . '/../config.php';
$cart_qty = cart_count();
$current  = basename($_SERVER['PHP_SELF']);

// Get categories for nav
$cats = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($page_title) ? h($page_title).' — ' : '' ?>VOLTEX Electronics</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
</head>
<body>

<div class="topbar">
  <div class="container">
    <div class="inner">
      <span>⚡ Free Shipping on Orders Over Rs. 5,000 | Use Code: <a href="#">VOLT20</a> for 20% OFF</span>
      <span>📞 Support: 0800-VOLTEX &nbsp;|&nbsp; <a href="<?= SITE_URL ?>/track_order.php">Track Order</a></span>
    </div>
  </div>
</div>

<nav class="navbar">
  <div class="container">
    <div class="nav-inner">
      <a href="<?= SITE_URL ?>/index.php" class="nav-brand">
        <span>⚡</span>VOLT<span>EX</span>
      </a>

      <form class="nav-search" method="GET" action="<?= SITE_URL ?>/shop.php">
        <input type="text" name="q" placeholder="Search phones, laptops, gadgets…" value="<?= h($_GET['q'] ?? '') ?>">
        <button type="submit">🔍</button>
      </form>

      <div class="nav-actions">
        <?php if (is_logged_in()): ?>
          <a href="<?= SITE_URL ?>/account.php" class="nav-btn">
            👤 <?= h(explode(' ', $_SESSION['user_name'])[0]) ?>
          </a>
          <a href="<?= SITE_URL ?>/wishlist.php" class="nav-btn">❤️</a>
        <?php else: ?>
          <a href="<?= SITE_URL ?>/login.php" class="nav-btn">Sign In</a>
        <?php endif; ?>
        <a href="<?= SITE_URL ?>/cart.php" class="nav-btn" style="position:relative;">
          🛒 Cart
          <?php if ($cart_qty > 0): ?>
          <span class="cart-badge"><?= $cart_qty ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>
  </div>
</nav>

<nav class="cat-nav">
  <div class="container">
    <div class="inner">
      <a href="<?= SITE_URL ?>/shop.php" class="<?= $current==='shop.php' && empty($_GET['cat']) ? 'active' : '' ?>">
        🏪 All Products
      </a>
      <?php mysqli_data_seek($cats, 0); while ($c = mysqli_fetch_assoc($cats)): ?>
      <a href="<?= SITE_URL ?>/shop.php?cat=<?= h($c['slug']) ?>"
         class="<?= ($_GET['cat'] ?? '')===$c['slug'] ? 'active' : '' ?>">
        <?= $c['icon'] ?> <?= h($c['name']) ?>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</nav>

<div id="toast">✅ <span id="toast-msg">Done!</span></div>
