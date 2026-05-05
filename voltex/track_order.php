<?php
require_once __DIR__ . '/config.php';
$page_title = 'Track Order';
$order = null; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $email    = trim($_POST['email'] ?? '');
    $esc_email = mysqli_real_escape_string($conn, $email);
    if ($order_id && $email) {
        $order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM orders WHERE id=$order_id AND email='$esc_email'"));
        if (!$order) $error = 'No order found with that ID and email combination.';
    } else { $error = 'Please enter Order ID and Email.'; }
}

$steps = ['pending'=>0,'processing'=>1,'shipped'=>2,'delivered'=>3];
include 'includes/header.php';
?>
<div class="container" style="max-width:700px; padding:60px 24px;">
  <h1 style="font-family:var(--font-head); font-size:24px; color:var(--white); margin-bottom:8px; letter-spacing:1px;">TRACK YOUR ORDER</h1>
  <p style="color:var(--text2); margin-bottom:32px;">Enter your order details to check the current status.</p>

  <div class="card" style="margin-bottom:28px;">
    <div class="card-body">
      <form method="POST">
        <div class="form-grid">
          <div class="form-group"><label>Order ID *</label><input type="number" name="order_id" class="form-control" placeholder="e.g. 12345" required value="<?= h($_POST['order_id'] ?? '') ?>"></div>
          <div class="form-group"><label>Email Address *</label><input type="email" name="email" class="form-control" placeholder="Order email" required value="<?= h($_POST['email'] ?? '') ?>"></div>
        </div>
        <button type="submit" class="btn btn-primary">Track Order 🔍</button>
      </form>
    </div>
  </div>

  <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

  <?php if ($order):
    $step = $steps[$order['status']] ?? 0;
    $items = mysqli_query($conn, "SELECT * FROM order_items WHERE order_id={$order['id']}");
  ?>
  <div class="card">
    <div class="card-header">
      <h2>Order #<?= str_pad($order['id'],6,'0',STR_PAD_LEFT) ?></h2>
      <span class="badge badge-warn"><?= ucfirst(h($order['status'])) ?></span>
    </div>
    <div class="card-body">
      <!-- Progress Bar -->
      <div style="margin-bottom:32px;">
        <div style="display:flex; justify-content:space-between; position:relative; margin-bottom:8px;">
          <div style="position:absolute; top:18px; left:0; right:0; height:2px; background:var(--border); z-index:0;"></div>
          <div style="position:absolute; top:18px; left:0; height:2px; background:var(--cyan); z-index:1; transition:width 0.5s; width:<?= min(100, $step * 33.3) ?>%;"></div>
          <?php $track_steps = [['📦','Placed'],['⚙️','Processing'],['🚚','Shipped'],['✅','Delivered']];
          foreach ($track_steps as $i => [$icon, $label]): ?>
          <div style="text-align:center; z-index:2; position:relative;">
            <div style="width:36px; height:36px; border-radius:50%; background:<?= $i<=$step?'var(--cyan)':'var(--surface2)' ?>; border:2px solid <?= $i<=$step?'var(--cyan)':'var(--border)' ?>; display:flex; align-items:center; justify-content:center; font-size:16px; margin:0 auto 8px;">
              <?= $icon ?>
            </div>
            <div style="font-family:var(--font-sub); font-size:11px; letter-spacing:1px; text-transform:uppercase; color:<?= $i<=$step?'var(--cyan)':'var(--text2)' ?>;"><?= $label ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Deliver To</div><div style="font-weight:600; color:var(--white);"><?= h($order['name']) ?></div><div style="font-size:13px; color:var(--text2);"><?= h($order['address'].', '.$order['city']) ?></div></div>
        <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Payment</div><div style="font-weight:600;"><?= strtoupper(h($order['payment_method'])) ?></div></div>
      </div>

      <?php while ($item = mysqli_fetch_assoc($items)): ?>
      <div style="display:flex; justify-content:space-between; padding:10px 0; border-top:1px solid var(--border); font-size:14px;">
        <span><?= h($item['name']) ?> <span style="color:var(--text2);">×<?= $item['quantity'] ?></span></span>
        <span style="color:var(--cyan); font-weight:600;"><?= price($item['price'] * $item['quantity']) ?></span>
      </div>
      <?php endwhile; ?>
      <div style="display:flex; justify-content:space-between; padding:12px 0; font-family:var(--font-head); font-size:16px; color:var(--cyan); border-top:1px solid var(--border); margin-top:4px;">
        <span>Total</span><span><?= price($order['total_amount']) ?></span>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
