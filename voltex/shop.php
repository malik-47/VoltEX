<?php
require_once __DIR__ . '/config.php';

$q        = trim($_GET['q'] ?? '');
$cat_slug = trim($_GET['cat'] ?? '');
$sort     = $_GET['sort'] ?? 'newest';
$max_price= (int)($_GET['max_price'] ?? 400000);
$featured = isset($_GET['featured']);

// Build query
$where  = ["p.stock > 0"];
$params = [];

if ($q) {
    $esc = mysqli_real_escape_string($conn, $q);
    $where[] = "(p.name LIKE '%$esc%' OR p.description LIKE '%$esc%')";
}
if ($cat_slug) {
    $esc = mysqli_real_escape_string($conn, $cat_slug);
    $where[] = "c.slug = '$esc'";
}
if ($featured) $where[] = "p.featured = 1";
if ($max_price < 400000) $where[] = "p.price <= $max_price";

$order = match($sort) {
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
    'rating'     => 'p.rating DESC',
    'popular'    => 'p.reviews_count DESC',
    default      => 'p.id DESC',
};

$sql = "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE " . implode(' AND ', $where) . " ORDER BY $order";
$products = mysqli_query($conn, $sql);
$total    = mysqli_num_rows($products);

// Current category info
$cat_info = null;
if ($cat_slug) {
    $esc = mysqli_real_escape_string($conn, $cat_slug);
    $cat_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM categories WHERE slug='$esc'"));
}

$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as pcount FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name");

$page_title = $cat_info ? $cat_info['name'] : ($q ? "Search: $q" : 'Shop');
include 'includes/header.php';
?>

<div class="container">
  <div class="breadcrumb">
    <a href="index.php">Home</a> <span>›</span>
    <a href="shop.php">Shop</a>
    <?php if ($cat_info): ?><span>›</span> <span><?= h($cat_info['icon'].' '.$cat_info['name']) ?></span><?php endif; ?>
    <?php if ($q): ?><span>›</span> <span>Search: "<?= h($q) ?>"</span><?php endif; ?>
  </div>

  <div class="page-wrap">
    <!-- Sidebar Filters -->
    <aside class="page-sidebar">
      <form method="GET" id="filter-form">
        <?php if ($q): ?><input type="hidden" name="q" value="<?= h($q) ?>"><?php endif; ?>

        <div class="filter-title">⚙ Filters</div>

        <div class="filter-group">
          <div class="filter-group-label">Category</div>
          <?php mysqli_data_seek($categories, 0); while ($cat = mysqli_fetch_assoc($categories)): ?>
          <label class="filter-option">
            <input type="radio" name="cat" value="<?= h($cat['slug']) ?>" <?= $cat_slug===$cat['slug']?'checked':'' ?> onchange="this.form.submit()">
            <?= $cat['icon'] ?> <?= h($cat['name']) ?> <span style="color:var(--text2);font-size:11px;margin-left:auto;">(<?= $cat['pcount'] ?>)</span>
          </label>
          <?php endwhile; ?>
          <label class="filter-option">
            <input type="radio" name="cat" value="" <?= !$cat_slug?'checked':'' ?> onchange="this.form.submit()">
            🏪 All Categories
          </label>
        </div>

        <div class="filter-group">
          <div class="filter-group-label">Max Price</div>
          <div style="padding:0 4px;">
            <input type="range" id="price-range" name="max_price"
              min="1000" max="400000" step="5000" value="<?= $max_price ?>"
              style="width:100%; accent-color:var(--cyan);">
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text2); margin-top:6px;">
              <span>Rs. 1K</span>
              <span id="price-val">Rs. <?= number_format($max_price) ?></span>
              <span>Rs. 400K</span>
            </div>
          </div>
        </div>

        <div class="filter-group">
          <div class="filter-group-label">Other</div>
          <label class="filter-option">
            <input type="checkbox" name="featured" <?= $featured?'checked':'' ?> onchange="this.form.submit()">
            ⭐ Featured Only
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-sm btn-full">Apply Filters</button>
        <a href="shop.php" class="btn btn-ghost btn-sm btn-full" style="margin-top:8px;">Clear All</a>
      </form>
    </aside>

    <!-- Product Grid -->
    <div class="page-main">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div style="font-family:var(--font-sub); color:var(--text2); font-size:14px;">
          <?= $total ?> product<?= $total!==1?'s':'' ?> found
          <?= $q ? "for <strong style='color:var(--text)'>\"".h($q)."\"</strong>" : '' ?>
        </div>
        <form method="GET">
          <?php foreach ($_GET as $k=>$v): if ($k!=='sort'): ?>
          <input type="hidden" name="<?= h($k) ?>" value="<?= h($v) ?>">
          <?php endif; endforeach; ?>
          <select name="sort" class="form-control" style="width:auto;" onchange="this.form.submit()">
            <option value="newest"     <?= $sort==='newest'    ?'selected':'' ?>>Newest First</option>
            <option value="popular"    <?= $sort==='popular'   ?'selected':'' ?>>Most Popular</option>
            <option value="rating"     <?= $sort==='rating'    ?'selected':'' ?>>Top Rated</option>
            <option value="price_asc"  <?= $sort==='price_asc' ?'selected':'' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Price: High to Low</option>
          </select>
        </form>
      </div>

      <?php if ($total === 0): ?>
      <div class="empty">
        <div class="empty-icon">🔍</div>
        <h3>No Products Found</h3>
        <p>Try adjusting your filters or search terms.</p>
        <a href="shop.php" class="btn btn-ghost" style="margin-top:16px;">Clear Filters</a>
      </div>
      <?php else: ?>
      <div class="products-grid">
        <?php while ($p = mysqli_fetch_assoc($products)):
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
            <div style="font-size:12px; color:<?= $p['stock']<5?'var(--warning)':'var(--success)' ?>; margin-bottom:10px;">
              <?= $p['stock'] < 5 ? "⚠️ Only {$p['stock']} left!" : "✅ In Stock" ?>
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
  </div>
</div>

<?php include 'includes/footer.php'; ?>
