<?php
require_once __DIR__ . '/config.php';
if (!is_logged_in()) redirect('login.php');
$id    = (int)($_GET['id'] ?? 0);
$uid   = (int)$_SESSION['user_id'];
$order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id=$id AND user_id=$uid"));
if (!$order) redirect('account.php');
$items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id=$id");
$page_title = 'Order Confirmed';
include 'includes/header.php';
?>
<div class="container" style="max-width:640px; padding:60px 24px; text-align:center;">
  <div style="font-size:72px; margin-bottom:20px;">✅</div>
  <h1 style="font-family:var(--font-head); font-size:28px; color:var(--white); margin-bottom:10px; letter-spacing:1px;">ORDER CONFIRMED!</h1>
  <p style="color:var(--text2); font-size:16px; margin-bottom:32px;">Thank you for your purchase! Order <strong style="color:var(--cyan)">#<?= str_pad($id,6,'0',STR_PAD_LEFT) ?></strong> has been placed successfully.</p>

  <div class="card" style="text-align:left; margin-bottom:24px;">
    <div class="card-header"><h2>Order Details</h2></div>
    <div class="card-body">
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Order #</div><div style="font-weight:600; color:var(--cyan);"><?= str_pad($id,6,'0',STR_PAD_LEFT) ?></div></div>
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Date</div><div style="font-weight:600;"><?= date('d M Y', strtotime($order['created_at'])) ?></div></div>
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Payment</div><div style="font-weight:600;"><?= strtoupper(h($order['payment_method'])) ?></div></div>
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Status</div><span class="badge badge-warn">Pending</span></div>
      </div>
      <?php while ($item = mysqli_fetch_assoc($items)): ?>
      <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--border); font-size:14px;">
        <span><?= h($item['name']) ?> ×<?= $item['quantity'] ?></span>
        <span style="color:var(--cyan); font-weight:600;"><?= price($item['price'] * $item['quantity']) ?></span>
      </div>
      <?php endwhile; ?>
      <div style="display:flex; justify-content:space-between; padding:12px 0; font-family:var(--font-head); font-size:16px; color:var(--cyan);">
        <span>Total</span><span><?= price($order['total_amount']) ?></span>
      </div>
    </div>
  </div>

  <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
    <a href="account.php" class="btn btn-outline">View My Orders</a>
    <a href="shop.php" class="btn btn-primary">Continue Shopping ⚡</a>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
