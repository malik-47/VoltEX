<?php
require_once __DIR__ . '/config.php';
admin_guard();

// Update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        mysqli_query($conn, "UPDATE orders SET status='$status' WHERE id=$oid");
    }
    $qs = http_build_query(array_filter(['id' => $oid, 'updated' => 1]));
    redirect(SITE_URL . '/admin/orders.php?' . $qs);
}

// View single order
$view_id = (int)($_GET['id'] ?? 0);
$view_order = null;
if ($view_id) {
    $view_order = mysqli_fetch_assoc(mysqli_query($conn, "SELECT o.*, u.name as uname FROM orders o JOIN users u ON o.user_id=u.id WHERE o.id=$view_id"));
    if ($view_order) {
        $view_items = mysqli_query($conn, "SELECT oi.*, p.image_url FROM order_items oi LEFT JOIN products p ON oi.product_id=p.id WHERE oi.order_id=$view_id");
    }
}

// Filters
$filter_status = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');
$where = ["1=1"];
if ($filter_status && in_array($filter_status, ['pending','processing','shipped','delivered','cancelled'])) $where[] = "o.status='$filter_status'";
if ($search) { $esc = mysqli_real_escape_string($conn,$search); $where[] = "(u.name LIKE '%$esc%' OR o.email LIKE '%$esc%')"; }
$cond = implode(' AND ', $where);

$orders = mysqli_query($conn, "SELECT o.*, u.name as uname FROM orders o JOIN users u ON o.user_id=u.id WHERE $cond ORDER BY o.created_at DESC");
$status_badge = ['pending'=>'badge-warn','processing'=>'badge-cyan','shipped'=>'badge-orange','delivered'=>'badge-success','cancelled'=>'badge-danger'];

admin_header('Orders');
?>

<?php if ($view_order): ?>
<!-- Single Order View -->
<?php if (isset($_GET['updated'])): ?><div class="alert alert-success" style="margin-bottom:16px;">✅ Order status updated.</div><?php endif; ?>
<div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
  <a href="orders.php" style="color:var(--text2); font-size:20px;">←</a>
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;">ORDER #<?= str_pad($view_id,6,'0',STR_PAD_LEFT) ?></h1>
  <span class="badge <?= $status_badge[$view_order['status']]??'badge-cyan' ?>"><?= ucfirst(h($view_order['status'])) ?></span>
</div>

<div style="display:grid; grid-template-columns:1fr 320px; gap:24px; align-items:start;">
  <div>
    <div class="card" style="margin-bottom:20px;">
      <div class="card-header"><h2>Order Items</h2></div>
      <div style="padding:16px;">
        <?php while ($item = mysqli_fetch_assoc($view_items)): ?>
        <div style="display:flex; align-items:center; gap:14px; padding:12px 0; border-bottom:1px solid var(--border);">
          <img src="<?= h($item['image_url'] ?? '') ?>" style="width:52px; height:52px; object-fit:cover; border-radius:8px; border:1px solid var(--border); flex-shrink:0;" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=100'">
          <div style="flex:1;">
            <div style="font-weight:600; color:var(--white);"><?= h($item['name']) ?></div>
            <div style="font-size:12px; color:var(--text2);">Qty: <?= $item['quantity'] ?> × <?= price($item['price']) ?></div>
          </div>
          <div style="font-family:var(--font-head); font-size:14px; color:var(--cyan);"><?= price($item['price'] * $item['quantity']) ?></div>
        </div>
        <?php endwhile; ?>
        <div style="display:flex; justify-content:space-between; padding:14px 0; font-family:var(--font-head); font-size:16px; color:var(--cyan);">
          <span>Total</span><span><?= price($view_order['total_amount']) ?></span>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><h2>Shipping Details</h2></div>
      <div class="card-body">
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Name</div><div style="font-weight:600;"><?= h($view_order['name']) ?></div></div>
          <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Phone</div><div style="font-weight:600;"><?= h($view_order['phone']) ?></div></div>
          <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Email</div><div style="font-weight:600;"><?= h($view_order['email']) ?></div></div>
          <div><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Payment</div><div style="font-weight:600;"><?= strtoupper(h($view_order['payment_method'])) ?></div></div>
          <div style="grid-column:span 2;"><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Address</div><div style="font-weight:600;"><?= h($view_order['address'].', '.$view_order['city']) ?></div></div>
          <?php if ($view_order['notes']): ?>
          <div style="grid-column:span 2;"><div style="font-size:11px; color:var(--text2); text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Notes</div><div style="color:var(--text2);"><?= h($view_order['notes']) ?></div></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-header"><h2>Update Status</h2></div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="order_id" value="<?= $view_id ?>">
          <input type="hidden" name="update_status" value="1">
          <div class="form-group"><label>Order Status</label>
            <select name="status" class="form-control">
              <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
              <option value="<?= $s ?>" <?= $view_order['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary btn-full">Update Status</button>
        </form>
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
          <div style="font-size:12px; color:var(--text2); margin-bottom:8px;">Order placed: <?= date('d M Y H:i', strtotime($view_order['created_at'])) ?></div>
          <a href="mailto:<?= h($view_order['email']) ?>" class="btn btn-ghost btn-sm btn-full">📧 Email Customer</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- Orders List -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;">ORDERS</h1>
</div>

<?php if (isset($_GET['updated'])): ?><div class="alert alert-success" style="margin-bottom:16px;">✅ Order status updated.</div><?php endif; ?>

<form method="GET" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
  <div style="flex:1; min-width:200px; position:relative;">
    <input type="text" name="q" class="form-control" placeholder="Search customer name or email…" value="<?= h($search) ?>" style="padding-left:36px;">
    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text2);">🔍</span>
  </div>
  <select name="status" class="form-control" style="width:160px;">
    <option value="">All Statuses</option>
    <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
    <option value="<?= $s ?>" <?= $filter_status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
  <?php if ($filter_status || $search): ?><a href="orders.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<div class="card">
  <div class="card-header"><h2>All Orders (<?= mysqli_num_rows($orders) ?>)</h2></div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>City</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($o = mysqli_fetch_assoc($orders)): ?>
        <tr>
          <td><a href="orders.php?id=<?= $o['id'] ?>" style="color:var(--cyan); font-weight:700;">#<?= str_pad($o['id'],6,'0',STR_PAD_LEFT) ?></a></td>
          <td>
            <div style="font-weight:600; font-size:13px;"><?= h($o['uname']) ?></div>
            <div style="font-size:11px; color:var(--text2);"><?= h($o['email']) ?></div>
          </td>
          <td style="font-weight:600; color:var(--cyan);"><?= price($o['total_amount']) ?></td>
          <td style="font-size:12px;"><?= strtoupper(h($o['payment_method'])) ?></td>
          <td style="font-size:13px; color:var(--text2);"><?= h($o['city']) ?></td>
          <td><span class="badge <?= $status_badge[$o['status']]??'badge-cyan' ?>"><?= ucfirst(h($o['status'])) ?></span></td>
          <td style="font-size:12px; color:var(--text2);"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
          <td>
            <a href="orders.php?id=<?= $o['id'] ?>" class="btn btn-ghost btn-sm">View</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php admin_footer(); ?>
