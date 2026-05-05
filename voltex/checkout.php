<?php
require_once __DIR__ . '/config.php';
if (!is_logged_in()) redirect('login.php');

$uid   = (int)$_SESSION['user_id'];
$res   = mysqli_query($conn, "SELECT c.quantity, p.* FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$uid");
$items = [];
$subtotal = 0;
while ($row = mysqli_fetch_assoc($res)) { $items[] = $row; $subtotal += $row['price'] * $row['quantity']; }
if (empty($items)) redirect('cart.php');

$shipping = $subtotal >= 5000 ? 0 : 300;
$total    = $subtotal + $shipping;

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$uid"));

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $payment = $_POST['payment'] ?? 'cod';
    $notes   = trim($_POST['notes'] ?? '');

    if (!$name || !$email || !$phone || !$address || !$city) {
        $error = 'Please fill in all required fields.';
    } else {
        $n = mysqli_real_escape_string($conn, $name);
        $e = mysqli_real_escape_string($conn, $email);
        $ph = mysqli_real_escape_string($conn, $phone);
        $a  = mysqli_real_escape_string($conn, $address);
        $ci = mysqli_real_escape_string($conn, $city);
        $pa = mysqli_real_escape_string($conn, $payment);
        $no = mysqli_real_escape_string($conn, $notes);

        mysqli_query($conn, "INSERT INTO orders (user_id,total_amount,payment_method,name,email,phone,address,city,notes) VALUES ($uid,$total,'$pa','$n','$e','$ph','$a','$ci','$no')");
        $order_id = mysqli_insert_id($conn);

        foreach ($items as $item) {
            $pid = $item['id']; $pname = mysqli_real_escape_string($conn, $item['name']);
            $pprice = $item['price']; $pqty = $item['quantity'];
            mysqli_query($conn, "INSERT INTO order_items (order_id,product_id,name,price,quantity) VALUES ($order_id,$pid,'$pname',$pprice,$pqty)");
            mysqli_query($conn, "UPDATE products SET stock=stock-$pqty WHERE id=$pid");
        }
        mysqli_query($conn, "DELETE FROM cart WHERE user_id=$uid");

        redirect("order_success.php?id=$order_id");
    }
}

$page_title = 'Checkout';
include 'includes/header.php';
?>
<div class="container" style="padding:40px 24px;">
  <h1 style="font-family:var(--font-head); font-size:22px; color:var(--white); margin-bottom:28px; letter-spacing:1px;">CHECKOUT</h1>

  <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

  <form method="POST">
    <div style="display:grid; grid-template-columns:1fr 360px; gap:28px; align-items:start;">
      <div>
        <div class="card" style="margin-bottom:20px;">
          <div class="card-header"><h2>📦 Shipping Information</h2></div>
          <div class="card-body">
            <div class="form-grid">
              <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? $user['name']) ?>">
              </div>
              <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? $user['email']) ?>">
              </div>
              <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" class="form-control" required value="<?= h($_POST['phone'] ?? $user['phone']) ?>">
              </div>
              <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" class="form-control" required value="<?= h($_POST['city'] ?? $user['city']) ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Full Address *</label>
              <textarea name="address" class="form-control" required rows="3"><?= h($_POST['address'] ?? $user['address']) ?></textarea>
            </div>
            <div class="form-group">
              <label>Order Notes (Optional)</label>
              <textarea name="notes" class="form-control" rows="2" placeholder="Any special instructions…"><?= h($_POST['notes'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h2>💳 Payment Method</h2></div>
          <div class="card-body">
            <?php $methods = [['cod','💵','Cash on Delivery','Pay when your order arrives'],['easypaisa','📱','EasyPaisa','Pay via EasyPaisa mobile wallet'],['jazzcash','💜','JazzCash','Pay via JazzCash mobile wallet']]; ?>
            <?php foreach ($methods as [$val, $icon, $label, $desc]): ?>
            <label style="display:flex; align-items:center; gap:14px; padding:14px; border:1px solid var(--border); border-radius:var(--r); cursor:pointer; margin-bottom:10px; transition:border-color 0.2s;">
              <input type="radio" name="payment" value="<?= $val ?>" <?= ($_POST['payment'] ?? 'cod')===$val?'checked':'' ?> style="accent-color:var(--cyan);">
              <span style="font-size:22px;"><?= $icon ?></span>
              <div>
                <div style="font-weight:600; color:var(--white);"><?= $label ?></div>
                <div style="font-size:12px; color:var(--text2);"><?= $desc ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div>
        <div class="order-summary">
          <h3>ORDER SUMMARY</h3>
          <?php foreach ($items as $item): ?>
          <div class="summary-row" style="padding:6px 0;">
            <span class="summary-label" style="font-size:13px;"><?= h(substr($item['name'],0,30)) ?>… ×<?= $item['quantity'] ?></span>
            <span class="summary-value" style="font-size:13px;"><?= price($item['price'] * $item['quantity']) ?></span>
          </div>
          <?php endforeach; ?>
          <div style="border-top:1px solid var(--border); margin:12px 0;"></div>
          <div class="summary-row"><span class="summary-label">Subtotal</span><span class="summary-value"><?= price($subtotal) ?></span></div>
          <div class="summary-row"><span class="summary-label">Shipping</span><span class="summary-value"><?= $shipping===0 ? '<span style="color:var(--success)">FREE</span>' : price($shipping) ?></span></div>
          <div class="summary-row total"><span class="summary-label">Total</span><span><?= price($total) ?></span></div>
          <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:20px;">Place Order ⚡</button>
        </div>
      </div>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
