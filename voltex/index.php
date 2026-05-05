<?php
require_once __DIR__ . '/config.php';
$page_title = 'Home';
include 'includes/header.php';

// Featured products
$featured = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE p.featured=1 ORDER BY p.id DESC LIMIT 8");

// Categories with counts
$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as pcount FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name");

// Best sellers
$bestsellers = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.reviews_count DESC LIMIT 4");
?>

<!-- Hero -->
<section class="hero">
  <div class="hero-glow"></div>
  <div class="hero-glow2"></div>
  <div class="container">
    <div class="hero-content">
      <div class="hero-eyebrow"><span class="dot"></span> New Arrivals 2025</div>
      <h1>The Future of <em>Electronics</em> is Here</h1>
      <p>Discover Pakistan's most advanced electronics store. From flagship smartphones to pro-grade laptops — cutting-edge tech at unbeatable prices.</p>
      <div class="hero-btns">
        <a href="shop.php" class="btn btn-primary btn-lg">Shop Now ⚡</a>
        <a href="shop.php?featured=1" class="btn btn-outline btn-lg">New Arrivals</a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat"><div class="num">10K<span>+</span></div><div class="label">Happy Customers</div></div>
        <div class="hero-stat"><div class="num">500<span>+</span></div><div class="label">Products</div></div>
        <div class="hero-stat"><div class="num">8<span>+</span></div><div class="label">Categories</div></div>
        <div class="hero-stat"><div class="num">24/7</div><div class="label">Support</div></div>
      </div>
    </div>
  </div>
</section>

<!-- Categories -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-sub">Browse By</div>
        <div class="section-title">Shop <span>Categories</span></div>
      </div>
      <a href="shop.php" class="btn btn-ghost btn-sm">View All →</a>
    </div>
    <div class="cats-grid">
      <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
      <a href="shop.php?cat=<?= h($cat['slug']) ?>" class="cat-card">
        <div class="cat-icon"><?= $cat['icon'] ?></div>
        <div class="cat-name"><?= h($cat['name']) ?></div>
        <div class="cat-count"><?= $cat['pcount'] ?> Products</div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-sub">Hand Picked</div>
        <div class="section-title">Featured <span>Products</span></div>
      </div>
      <a href="shop.php?featured=1" class="btn btn-ghost btn-sm">View All →</a>
    </div>
    <div class="products-grid">
      <?php while ($p = mysqli_fetch_assoc($featured)):
        $discount = $p['original_price'] ? round((1 - $p['price']/$p['original_price'])*100) : 0;
        $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5 - round($p['rating']));
      ?>
      <div class="product-card">
        <div class="product-img">
          <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600'">
          <?php if ($p['badge']): ?><span class="product-badge"><?= h($p['badge']) ?></span><?php endif; ?>
          <button class="product-wish" onclick="toggleWishlist(<?= $p['id'] ?>, this)">🤍</button>
        </div>
        <div class="product-info">
          <div class="product-cat"><?= h($p['cat_name']) ?></div>
          <div class="product-name"><?= h($p['name']) ?></div>
          <div class="product-rating">
            <span class="stars"><?= $stars ?></span>
            <span class="rating-val"><?= $p['rating'] ?> (<?= number_format($p['reviews_count']) ?>)</span>
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
  </div>
</section>

<!-- Promo Banner -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="promo-banner">
      <div>
        <h2>⚡ Flash Sale — Up to 40% Off</h2>
        <p>Limited time offer on premium laptops, phones & accessories. Don't miss out!</p>
        <a href="shop.php" class="btn btn-primary">Shop the Sale</a>
      </div>
      <div style="font-size:80px;opacity:0.6;">💻</div>
    </div>
  </div>
</section>

<!-- Best Sellers -->
<section class="section" style="padding-top:0;">
  <div class="container">
    <div class="section-header">
      <div>
        <div class="section-sub">Most Popular</div>
        <div class="section-title">Best <span>Sellers</span></div>
      </div>
      <a href="shop.php" class="btn btn-ghost btn-sm">View All →</a>
    </div>
    <div class="products-grid">
      <?php while ($p = mysqli_fetch_assoc($bestsellers)):
        $discount = $p['original_price'] ? round((1 - $p['price']/$p['original_price'])*100) : 0;
        $stars = str_repeat('★', round($p['rating'])) . str_repeat('☆', 5 - round($p['rating']));
      ?>
      <div class="product-card">
        <div class="product-img">
          <img src="<?= h($p['image_url']) ?>" alt="<?= h($p['name']) ?>" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=600'">
          <?php if ($p['badge']): ?><span class="product-badge"><?= h($p['badge']) ?></span><?php endif; ?>
          <button class="product-wish" onclick="toggleWishlist(<?= $p['id'] ?>, this)">🤍</button>
        </div>
        <div class="product-info">
          <div class="product-cat"><?= h($p['cat_name']) ?></div>
          <div class="product-name"><?= h($p['name']) ?></div>
          <div class="product-rating">
            <span class="stars"><?= $stars ?></span>
            <span class="rating-val"><?= $p['rating'] ?> (<?= number_format($p['reviews_count']) ?>)</span>
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
  </div>
</section>

<!-- Features Row -->
<section class="section" style="padding-top:0; padding-bottom:40px;">
  <div class="container">
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:16px;">
      <?php $features = [
        ['🚚','Fast Delivery','Free shipping on orders above Rs. 5,000'],
        ['🛡️','Warranty','All products come with official warranty'],
        ['↩️','Easy Returns','30-day hassle-free return policy'],
        ['💳','Secure Payment','100% secure payment gateway'],
      ]; foreach ($features as $f): ?>
      <div style="background:var(--surface); border:1px solid var(--border); border-radius:var(--r-lg); padding:24px; display:flex; align-items:flex-start; gap:16px;">
        <div style="font-size:28px;"><?= $f[0] ?></div>
        <div>
          <div style="font-family:var(--font-sub); font-weight:700; color:var(--white); margin-bottom:4px;"><?= $f[1] ?></div>
          <div style="font-size:13px; color:var(--text2);"><?= $f[2] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
