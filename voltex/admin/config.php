<?php
require_once __DIR__ . '/../config.php';

function admin_guard() {
    if (!is_admin()) {
        redirect(SITE_URL . '/admin/login.php');
    }
}

function admin_header($title = 'Dashboard') {
    $current = basename($_SERVER['PHP_SELF']);
    $cart_qty = 0;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= h($title) ?> — VOLTEX Admin</title>
      <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
      <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
    </head>
    <body>
    <div id="toast"><span id="toast-msg"></span></div>
    <nav class="navbar">
      <div class="container">
        <div class="nav-inner">
          <a href="<?= SITE_URL ?>/admin/dashboard.php" class="nav-brand">
            <span>⚡</span>VOLT<span>EX</span>
            <span style="font-size:10px; letter-spacing:3px; color:var(--orange); font-family:var(--font-sub); font-weight:600; margin-left:8px; background:rgba(255,107,43,0.1); border:1px solid rgba(255,107,43,0.3); padding:2px 8px; border-radius:4px;">ADMIN</span>
          </a>
          <div style="margin-left:auto; display:flex; align-items:center; gap:10px;">
            <a href="<?= SITE_URL ?>/index.php" class="nav-btn" target="_blank">🌐 View Site</a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="nav-btn">🚪 Logout</a>
          </div>
        </div>
      </div>
    </nav>
    <div class="dash-wrap">
      <aside class="dash-sidebar">
        <div class="dash-nav-label">Main</div>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="dash-nav-item <?= $current==='dashboard.php'?'active':'' ?>"><span class="icon">📊</span> Dashboard</a>
        <div class="dash-nav-label">Catalog</div>
        <a href="<?= SITE_URL ?>/admin/products.php" class="dash-nav-item <?= $current==='products.php'?'active':'' ?>"><span class="icon">📦</span> Products</a>
        <a href="<?= SITE_URL ?>/admin/categories.php" class="dash-nav-item <?= $current==='categories.php'?'active':'' ?>"><span class="icon">🏷️</span> Categories</a>
        <a href="<?= SITE_URL ?>/admin/add_product.php" class="dash-nav-item <?= $current==='add_product.php'?'active':'' ?>"><span class="icon">➕</span> Add Product</a>
        <div class="dash-nav-label">Sales</div>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="dash-nav-item <?= $current==='orders.php'?'active':'' ?>"><span class="icon">🛒</span> Orders</a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="dash-nav-item <?= $current==='users.php'?'active':'' ?>"><span class="icon">👥</span> Customers</a>
        <div class="dash-nav-label">System</div>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="dash-nav-item" style="color:var(--danger);"><span class="icon">🚪</span> Logout</a>
      </aside>
      <main class="dash-main">
    <?php
}

function admin_footer() {
    echo '</main></div>';
    echo '<script src="' . SITE_URL . '/js/main.js"></script>';
    echo '</body></html>';
}
