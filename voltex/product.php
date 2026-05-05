<?php
require_once __DIR__ . '/config.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) redirect('shop.php');

$esc = mysqli_real_escape_string($conn, $slug);
$p   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, c.name as cat_name, c.slug as cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE p.slug='$esc'"));
if (!$p) redirect('shop.php');

// Related products
$related = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.category_id={$p['category_id']} AND p.id != {$p['id']} LIMIT 4");

$discount = $p['original_price'] ? round((1 - $p['price']/$p['original_price'])*100) : 0;
$stars    = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5 - round($p['rating']));

$page_title = $p['name'];
include 'includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="index.php">Home</a> <span>›</span>
    <a href="shop.php">Shop</a> <span>›</span>
    <a href="shop.php?cat=<?= h($p['cat_slug']) ?>"><?= h($p['cat_name']) ?></a> <span>›</span>
    <span><?= h($p['name']) ?></span>
  </div>

  <div class="product-detail">
    <div class="product-detail-img">
      <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=800'">
    </div>

    <div class="product-detail-info">
      <?php if ($p['badge']): ?>
      <span class="badge badge-orange" style="margin-bottom:12px;"><?= h($p['badge']) ?></span>
      <?php endif; ?>

      <div class="detail-cat"><?= h($p['cat_name']) ?></div>
      <div class="detail-name"><?= h($p['name']) ?></div>

      <div class="product-rating" style="margin-bottom:16px;">
        <span class="stars" style="font-size:18px;"><?= $stars ?></span>
        <span style="color:var(--text2); font-size:14px;"><?= $p['rating'] ?> out of 5 (<?= number_format($p['reviews_count']) ?> reviews)</span>
      </div>

      <div class="detail-price">
        <?= price($p['price']) ?>
        <?php if ($p['original_price']): ?>
        <span style="font-family:var(--font-body); font-size:16px; color:var(--text2); text-decoration:line-through; margin-left:12px; font-weight:400;"><?= price($p['original_price']) ?></span>
        <span class="badge badge-success" style="margin-left:8px;">Save <?= $discount ?>%</span>
        <?php endif; ?>
      </div>

      <p class="detail-desc"><?= h($p['description']) ?></p>

      <div class="detail-meta">
        <div class="row"><span class="k">Availability</span><span class="v" style="color:<?= $p['stock']>0?'var(--success)':'var(--danger)' ?>"><?= $p['stock']>0 ? "✅ In Stock ({$p['stock']} units)" : "❌ Out of Stock" ?></span></div>
        <div class="row"><span class="k">Category</span><span class="v"><?= h($p['cat_name']) ?></span></div>
        <div class="row"><span class="k">SKU</span><span class="v">VTX-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></span></div>
      </div>

      <?php if ($p['stock'] > 0): ?>
      <div class="qty-box">
        <label style="font-family:var(--font-sub); font-size:12px; letter-spacing:1px; color:var(--text2); text-transform:uppercase;">Quantity</label>
        <div class="qty-ctrl">
          <button class="qty-down" type="button">−</button>
          <span id="qty-display">1</span>
          <input type="hidden" id="qty-val" value="1">
          <button class="qty-up" type="button" data-max="<?= $p['stock'] ?>">+</button>
        </div>
      </div>
      <div style="display:flex; gap:12px; flex-wrap:wrap;">
        <button class="btn btn-primary btn-lg" onclick="addToCart(<?= $p['id'] ?>, parseInt(document.getElementById('qty-val').value))">
          🛒 Add to Cart
        </button>
        <button class="btn btn-outline" onclick="toggleWishlist(<?= $p['id'] ?>, this)">🤍 Wishlist</button>
      </div>
      <?php else: ?>
      <div class="alert alert-error">❌ This product is currently out of stock.</div>
      <?php endif; ?>

      <!-- Delivery info -->
      <div style="display:flex; gap:16px; margin-top:24px; flex-wrap:wrap;">
        <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text2);">🚚 Free delivery on orders above Rs. 5,000</div>
        <div style="display:flex; align-items:center; gap:8px; font-size:13px; color:var(--text2);">🛡️ Official Warranty Included</div>
      </div>
    </div>
  </div>

  <!-- Related Products -->
  <?php if (mysqli_num_rows($related) > 0): ?>
  <div class="section" style="padding-top:0;">
    <div class="section-header">
      <div>
        <div class="section-sub">You May Also Like</div>
        <div class="section-title">Related <span>Products</span></div>
      </div>
    </div>
    <div class="products-grid">
      <?php while ($r = mysqli_fetch_assoc($related)):
        $rs = str_repeat('★', round($r['rating'])) . str_repeat('☆', 5 - round($r['rating']));
      ?>
      <div class="product-card">
        <div class="product-img">
          <img src="<?= h($r['image_url']) ?>" alt="<?= h($r['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600'">
          <?php if ($r['badge']): ?><span class="product-badge"><?= h($r['badge']) ?></span><?php endif; ?>
        </div>
        <div class="product-info">
          <div class="product-cat"><?= h($r['cat_name']) ?></div>
          <div class="product-name"><?= h($r['name']) ?></div>
          <div class="product-rating">
            <span class="stars"><?= $rs ?></span>
            <span class="rating-val"><?= $r['rating'] ?></span>
          </div>
          <div class="product-price">
            <span class="price-current"><?= price($r['price']) ?></span>
          </div>
          <div class="product-actions">
            <button class="btn-cart" onclick="addToCart(<?= $r['id'] ?>)">+ Add to Cart</button>
            <a href="product.php?slug=<?= h($r['slug']) ?>" class="btn-view">👁</a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
// Sync qty-val hidden input with display span
document.addEventListener('click', function(e) {
  const display = document.getElementById('qty-display');
  const input   = document.getElementById('qty-val');
  if (!display || !input) return;
  if (e.target.classList.contains('qty-up')) {
    let v = parseInt(display.textContent) + 1;
    const max = parseInt(e.target.dataset.max || 99);
    if (v > max) v = max;
    display.textContent = v; input.value = v;
  }
  if (e.target.classList.contains('qty-down')) {
    let v = parseInt(display.textContent) - 1;
    if (v < 1) v = 1;
    display.textContent = v; input.value = v;
  }
});
</script>

<?php include 'includes/footer.php'; ?>
