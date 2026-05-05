<?php
require_once __DIR__ . '/config.php';
if (!is_logged_in()) { $_SESSION['redirect_after_login'] = 'account.php'; redirect('login.php'); }
$uid  = (int)$_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));
$orders = mysqli_query($conn, "SELECT * FROM orders WHERE user_id=$uid ORDER BY created_at DESC");
$page_title = 'My Account';
include 'includes/header.php';

$status_badge = ['pending'=>'badge-warn','processing'=>'badge-cyan','shipped'=>'badge-orange','delivered'=>'badge-success','cancelled'=>'badge-danger'];
?>
<div class="container" style="padding:40px 24px;">
  <h1 style="font-family:var(--font-head); font-size:22px; color:var(--white); margin-bottom:28px; letter-spacing:1px;">MY ACCOUNT</h1>

  <div style="display:grid; grid-template-columns:280px 1fr; gap:28px; align-items:start;">
    <!-- Profile Card -->
    <div class="card">
      <div class="card-body" style="text-align:center;">
        <div style="width:72px; height:72px; border-radius:50%; background:var(--cyan-soft); border:2px solid var(--border2); display:flex; align-items:center; justify-content:center; font-family:var(--font-head); font-size:28px; font-weight:800; color:var(--cyan); margin:0 auto 14px;">
          <?= strtoupper(substr($user['name'],0,1)) ?>
        </div>
        <div style="font-family:var(--font-sub); font-size:18px; font-weight:700; color:var(--white); margin-bottom:4px;"><?= h($user['name']) ?></div>
        <div style="font-size:13px; color:var(--text2); margin-bottom:20px;"><?= h($user['email']) ?></div>
        <div style="text-align:left;">
          <?php $nav_items = [['account.php','👤','My Orders'],['wishlist.php','❤️','Wishlist'],['cart.php','🛒','Cart'],['logout.php','🚪','Logout']]; ?>
          <?php foreach ($nav_items as [$href,$icon,$label]): ?>
          <a href="<?= SITE_URL ?>/<?= $href ?>" class="dash-nav-item">
            <span class="icon"><?= $icon ?></span> <?= $label ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Orders -->
    <div class="card">
      <div class="card-header"><h2>📦 My Orders</h2></div>
      <?php if (mysqli_num_rows($orders) === 0): ?>
      <div class="empty"><div class="empty-icon">📦</div><h3>No Orders Yet</h3><p>Shop now and your orders will appear here.</p><a href="<?= SITE_URL ?>/shop.php" class="btn btn-primary" style="margin-top:16px;">Shop Now</a></div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Order #</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
          <tbody>
            <?php while ($o = mysqli_fetch_assoc($orders)):
              $item_count = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(quantity) FROM order_items WHERE order_id={$o['id']}"))[0];
            ?>
            <tr>
              <td style="color:var(--cyan); font-weight:700;">#<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></td>
              <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
              <td><?= $item_count ?> item<?= $item_count!=1?'s':'' ?></td>
              <td style="font-weight:600;"><?= price($o['total_amount']) ?></td>
              <td><?= strtoupper(h($o['payment_method'])) ?></td>
              <td><span class="badge <?= $status_badge[$o['status']] ?? 'badge-cyan' ?>"><?= ucfirst(h($o['status'])) ?></span></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
