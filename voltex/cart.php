<?php
require_once __DIR__ . '/config.php';
$page_title = 'My Cart';
include 'includes/header.php';

$items = []; $subtotal = 0;
if (is_logged_in()) {
    $uid  = (int)$_SESSION['user_id'];
    $res  = mysqli_query($conn, "SELECT c.quantity, p.* FROM cart c JOIN products p ON c.product_id=p.id WHERE c.user_id=$uid");
    while ($row = mysqli_fetch_assoc($res)) {
        $items[] = $row;
        $subtotal += $row['price'] * $row['quantity'];
    }
}
$shipping = $subtotal >= 5000 ? 0 : 300;
$total    = $subtotal + $shipping;
?>
<div class="container">
  <div class="breadcrumb">
    <a href="index.php">Home</a><span>›</span><span>Cart</span>
  </div>

  <?php if (!is_logged_in()): ?>
  <div class="alert alert-info" style="margin:40px 0;">🔐 Please <a href="login.php"><strong>sign in</strong></a> to view your cart.</div>
  <?php elseif (empty($items)): ?>
  <div class="empty" style="padding:80px 0;">
    <div class="empty-icon">🛒</div>
    <h3>Your Cart is Empty</h3>
    <p>Add some products to get started.</p>
    <a href="shop.php" class="btn btn-primary" style="margin-top:20px;">Shop Now</a>
  </div>
  <?php else: ?>

  <h1 style="font-family:var(--font-head); font-size:22px; font-weight:800; color:var(--white); margin:20px 0 28px; letter-spacing:1px;">YOUR CART</h1>

  <div style="display:grid; grid-template-columns:1fr 340px; gap:28px; align-items:start;">
    <div class="card">
      <div class="card-header">
        <h2>Cart Items (<?= count($items) ?>)</h2>
        <a href="ajax/cart.php?action=clear&uid=<?= $uid ?>" class="btn btn-ghost btn-sm" onclick="return confirm('Clear all items?')">Clear Cart</a>
      </div>
      <div style="overflow-x:auto;">
        <table class="cart-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $item): ?>
            <tr id="cart-row-<?= $item['id'] ?>">
              <td>
                <div style="display:flex; align-items:center; gap:14px;">
                  <img src="<?= h($item['image_url']) ?>" alt="<?= h($item['name']) ?>"
                    style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid var(--border);"
                    onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=200'">
                  <div>
                    <div style="font-weight:600; color:var(--white);"><?= h($item['name']) ?></div>
                    <a href="product.php?slug=<?= h($item['slug']) ?>" style="font-size:12px; color:var(--cyan);">View Details</a>
                  </div>
                </div>
              </td>
              <td><?= price($item['price']) ?></td>
              <td>
                <form action="ajax/cart_update.php" method="POST" style="display:inline;">
                  <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                  <div class="qty-ctrl">
                    <button type="submit" name="action" value="decrease" class="qty-down">−</button>
                    <span><?= $item['quantity'] ?></span>
                    <button type="submit" name="action" value="increase" class="qty-up">+</button>
                  </div>
                </form>
              </td>
              <td style="color:var(--cyan); font-weight:700;"><?= price($item['price'] * $item['quantity']) ?></td>
              <td>
                <button onclick="removeCartItem(<?= $item['id'] ?>)" style="background:none; border:none; color:var(--danger); cursor:pointer; font-size:18px;" title="Remove">✕</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div>
      <div class="order-summary">
        <h3>ORDER SUMMARY</h3>
        <div class="summary-row"><span class="summary-label">Subtotal</span><span class="summary-value"><?= price($subtotal) ?></span></div>
        <div class="summary-row"><span class="summary-label">Shipping</span><span class="summary-value"><?= $shipping === 0 ? '<span style="color:var(--success)">FREE</span>' : price($shipping) ?></span></div>
        <?php if ($shipping > 0): ?>
        <div style="font-size:12px; color:var(--text2); margin-bottom:8px;">Add <?= price(5000 - $subtotal) ?> more for free shipping</div>
        <?php endif; ?>
        <div class="summary-row total"><span class="summary-label">Total</span><span><?= price($total) ?></span></div>
        <a href="checkout.php" class="btn btn-primary btn-full" style="margin-top:20px;">Proceed to Checkout ⚡</a>
        <a href="shop.php" class="btn btn-ghost btn-full" style="margin-top:10px;">Continue Shopping</a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
