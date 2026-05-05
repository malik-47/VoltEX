<?php
require_once __DIR__ . '/config.php';
if (!is_logged_in()) { $_SESSION['redirect_after_login'] = 'wishlist.php'; redirect('login.php'); }
$uid   = (int)$_SESSION['user_id'];
$items = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM wishlist w JOIN products p ON w.product_id=p.id JOIN categories c ON p.category_id=c.id WHERE w.user_id=$uid ORDER BY w.added_at DESC");
$page_title = 'My Wishlist';
include 'includes/header.php';
?>
<div class="container" style="padding:40px 24px;">
  <div class="breadcrumb"><a href="index.php">Home</a><span>›</span><span>Wishlist</span></div>
  <h1 style="font-family:var(--font-head); font-size:22px; color:var(--white); margin-bottom:28px; letter-spacing:1px;">MY WISHLIST ❤️</h1>

  <?php if (mysqli_num_rows($items) === 0): ?>
  <div class="empty" style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg);">
    <div class="empty-icon">❤️</div>
    <h3>Your Wishlist is Empty</h3>
    <p>Save products you love and find them here anytime.</p>
    <a href="shop.php" class="btn btn-primary" style="margin-top:20px;">Browse Products</a>
  </div>
  <?php else: ?>
  <div class="products-grid">
    <?php while ($p = mysqli_fetch_assoc($items)):
      $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5 - round($p['rating']));
      $discount = $p['original_price'] ? round((1 - $p['price']/$p['original_price'])*100) : 0;
    ?>
    <div class="product-card">
      <div class="product-img">
        <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600'">
        <?php if ($p['badge']): ?><span class="product-badge"><?= h($p['badge']) ?></span><?php endif; ?>
        <button class="product-wish active" onclick="toggleWishlist(<?= $p['id'] ?>, this)">❤️</button>
      </div>
      <div class="product-info">
        <div class="product-cat"><?= h($p['cat_name']) ?></div>
        <div class="product-name"><?= h($p['name']) ?></div>
        <div class="product-rating">
          <span class="stars"><?= $stars ?></span>
          <span class="rating-val"><?= $p['rating'] ?></span>
        </div>
        <div class="product-price">
          <span class="price-current"><?= price($p['price']) ?></span>
          <?php if ($p['original_price']): ?>
          <span class="price-original"><?= price($p['original_price']) ?></span>
          <span class="price-discount">-<?= $discount ?>%</span>
          <?php endif; ?>
        </div>
        <div class="product-actions">
          <button class="btn-cart" onclick="addToCart(<?= $p['id'] ?>)">+ Add to Cart</button>
          <a href="product.php?slug=<?= h($p['slug']) ?>" class="btn-view">👁</a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
