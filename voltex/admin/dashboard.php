<?php
require_once __DIR__ . '/config.php';
admin_guard();
global $conn;

$total_products  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM products"))[0];
$total_orders    = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders"))[0];
$total_users     = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users"))[0];
$total_revenue   = mysqli_fetch_row(mysqli_query($conn,"SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'"))[0] ?? 0;
$pending_orders  = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders WHERE status='pending'"))[0];
$low_stock       = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM products WHERE stock < 5"))[0];

$recent_orders = mysqli_query($conn,"SELECT o.*, u.name as uname FROM orders o JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8");
$top_products  = mysqli_query($conn,"SELECT p.name, p.price, p.stock, p.reviews_count, c.name as cat FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.reviews_count DESC LIMIT 5");

$status_badge = ['pending'=>'badge-warn','processing'=>'badge-cyan','shipped'=>'badge-orange','delivered'=>'badge-success','cancelled'=>'badge-danger'];

admin_header('Dashboard');
?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px;">
  <div>
    <h1 style="font-family:var(--font-head); font-size:22px; color:var(--white); letter-spacing:1px;">DASHBOARD</h1>
    <p style="color:var(--text2); font-size:14px; margin-top:4px;">Welcome back, <?= h($_SESSION['admin_user']) ?>! Here's what's happening.</p>
  </div>
  <div style="display:flex; gap:10px;">
    <a href="add_product.php" class="btn btn-primary btn-sm">+ Add Product</a>
    <a href="orders.php" class="btn btn-ghost btn-sm">View Orders</a>
  </div>
</div>

<!-- Stats -->
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; margin-bottom:28px;">
  <div class="dash-stat">
    <div class="label">Total Revenue</div>
    <div class="value" style="color:var(--cyan); font-size:24px;"><?= price($total_revenue) ?></div>
    <div class="sub">From confirmed orders</div>
    <div class="stat-icon">💰</div>
  </div>
  <div class="dash-stat">
    <div class="label">Total Orders</div>
    <div class="value"><?= number_format($total_orders) ?></div>
    <div class="sub"><span style="color:var(--warning);"><?= $pending_orders ?> pending</span></div>
    <div class="stat-icon">🛒</div>
  </div>
  <div class="dash-stat">
    <div class="label">Customers</div>
    <div class="value"><?= number_format($total_users) ?></div>
    <div class="sub">Registered accounts</div>
    <div class="stat-icon">👥</div>
  </div>
  <div class="dash-stat">
    <div class="label">Products</div>
    <div class="value"><?= number_format($total_products) ?></div>
    <div class="sub"><span style="color:<?= $low_stock>0?'var(--warning)':'var(--success)' ?>"><?= $low_stock ?> low stock</span></div>
    <div class="stat-icon">📦</div>
  </div>
</div>

<div style="display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start;">
  <!-- Recent Orders -->
  <div class="card">
    <div class="card-header">
      <h2>Recent Orders</h2>
      <a href="orders.php" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
          <?php while ($o = mysqli_fetch_assoc($recent_orders)): ?>
          <tr>
            <td><a href="orders.php?id=<?= $o['id'] ?>" style="color:var(--cyan); font-weight:700;">#<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></a></td>
            <td><?= h($o['uname']) ?></td>
            <td style="font-weight:600;"><?= price($o['total_amount']) ?></td>
            <td style="font-size:12px; color:var(--text2);"><?= strtoupper(h($o['payment_method'])) ?></td>
            <td><span class="badge <?= $status_badge[$o['status']]??'badge-cyan' ?>"><?= ucfirst(h($o['status'])) ?></span></td>
            <td style="color:var(--text2); font-size:12px;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Products -->
  <div class="card">
    <div class="card-header">
      <h2>Top Products</h2>
      <a href="products.php" class="btn btn-ghost btn-sm">All</a>
    </div>
    <div style="padding:0 16px;">
      <?php while ($p = mysqli_fetch_assoc($top_products)): ?>
      <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--border); gap:10px;">
        <div>
          <div style="font-weight:600; font-size:13px; color:var(--white);"><?= h(substr($p['name'],0,28)) ?>…</div>
          <div style="font-size:11px; color:var(--text2); margin-top:2px;"><?= h($p['cat']) ?> · Stock: <span style="color:<?= $p['stock']<5?'var(--warning)':'var(--success)' ?>"><?= $p['stock'] ?></span></div>
        </div>
        <div style="text-align:right; flex-shrink:0;">
          <div style="font-family:var(--font-head); font-size:13px; color:var(--cyan);"><?= price($p['price']) ?></div>
          <div style="font-size:11px; color:var(--text2);"><?= number_format($p['reviews_count']) ?> reviews</div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</div>

<?php admin_footer(); ?>
