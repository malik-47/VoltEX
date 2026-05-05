<?php
require_once __DIR__ . '/config.php';
admin_guard();

$edit_id = (int)($_GET['id'] ?? 0);
$product = null;
if ($edit_id) {
    $product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE id=$edit_id"));
    if (!$product) redirect(SITE_URL . '/admin/products.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id  = (int)$_POST['category_id'];
    $name    = trim($_POST['name'] ?? '');
    $desc    = trim($_POST['description'] ?? '');
    $price   = (float)$_POST['price'];
    $orig    = trim($_POST['original_price'] ?? '');
    $orig_val = ($orig !== '' && is_numeric($orig)) ? (float)$orig : 'NULL';
    $stock   = (int)$_POST['stock'];
    $img     = trim($_POST['image_url'] ?? '');
    $badge   = trim($_POST['badge'] ?? '');
    $rating  = (float)$_POST['rating'];
    $reviews = (int)$_POST['reviews_count'];
    $featured = isset($_POST['featured']) ? 1 : 0;

    if (!$name || !$price || !$cat_id) {
        $error = 'Name, price and category are required.';
    } else {
        $slug_base = slug($name);
        $slug = $slug_base;
        // Ensure unique slug
        $i = 1;
        while (true) {
            $esc_slug = mysqli_real_escape_string($conn, $slug);
            $check = mysqli_fetch_row(mysqli_query($conn, "SELECT id FROM products WHERE slug='$esc_slug'" . ($edit_id ? " AND id != $edit_id" : "")));
            if (!$check) break;
            $slug = $slug_base . '-' . $i++;
        }
        $n  = mysqli_real_escape_string($conn, $name);
        $d  = mysqli_real_escape_string($conn, $desc);
        $im = mysqli_real_escape_string($conn, $img);
        $ba = mysqli_real_escape_string($conn, $badge);

        if ($edit_id) {
            mysqli_query($conn, "UPDATE products SET category_id=$cat_id, name='$n', slug='$esc_slug', description='$d', price=$price, original_price=$orig_val, stock=$stock, image_url='$im', badge='$ba', rating=$rating, reviews_count=$reviews, featured=$featured WHERE id=$edit_id");
        } else {
            mysqli_query($conn, "INSERT INTO products (category_id,name,slug,description,price,original_price,stock,image_url,badge,rating,reviews_count,featured) VALUES ($cat_id,'$n','$esc_slug','$d',$price,$orig_val,$stock,'$im','$ba',$rating,$reviews,$featured)");
        }
        redirect(SITE_URL . '/admin/products.php?saved=1');
    }
}

$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$page_title = $edit_id ? 'Edit Product' : 'Add Product';
admin_header($page_title);
$p = $product ?? [];
?>
<div style="display:flex; align-items:center; gap:16px; margin-bottom:28px;">
  <a href="products.php" style="color:var(--text2); font-size:20px;">←</a>
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;"><?= $edit_id ? 'EDIT PRODUCT' : 'ADD PRODUCT' ?></h1>
</div>

<?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>

<form method="POST">
  <div style="display:grid; grid-template-columns:1fr 360px; gap:24px; align-items:start;">
    <div>
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header"><h2>Product Information</h2></div>
        <div class="card-body">
          <div class="form-group"><label>Product Name *</label><input type="text" name="name" class="form-control" required value="<?= h($p['name'] ?? '') ?>"></div>
          <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="4"><?= h($p['description'] ?? '') ?></textarea></div>
          <div class="form-grid">
            <div class="form-group"><label>Category *</label>
              <select name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php while ($c = mysqli_fetch_assoc($categories)): ?>
                <option value="<?= $c['id'] ?>" <?= ($p['category_id'] ?? 0)===$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="form-group"><label>Badge Label</label><input type="text" name="badge" class="form-control" placeholder="e.g. Hot, New, Sale" value="<?= h($p['badge'] ?? '') ?>"></div>
          </div>
          <div class="form-group"><label>Image URL</label><input type="url" name="image_url" class="form-control" placeholder="https://…" value="<?= h($p['image_url'] ?? '') ?>"></div>
          <?php if (!empty($p['image_url'])): ?>
          <img src="<?= h($p['image_url']) ?>" style="height:120px; object-fit:cover; border-radius:8px; margin-top:8px;">
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h2>Pricing & Stock</h2></div>
        <div class="card-body">
          <div class="form-group"><label>Sale Price (Rs.) *</label><input type="number" name="price" class="form-control" step="0.01" required value="<?= h($p['price'] ?? '') ?>"></div>
          <div class="form-group"><label>Original Price (Rs.)</label><input type="number" name="original_price" class="form-control" step="0.01" value="<?= h($p['original_price'] ?? '') ?>"></div>
          <div class="form-group"><label>Stock Quantity</label><input type="number" name="stock" class="form-control" min="0" value="<?= h($p['stock'] ?? 0) ?>"></div>
        </div>
      </div>

      <div class="card" style="margin-bottom:16px;">
        <div class="card-header"><h2>Reviews & Visibility</h2></div>
        <div class="card-body">
          <div class="form-group"><label>Rating (0-5)</label><input type="number" name="rating" class="form-control" step="0.1" min="0" max="5" value="<?= h($p['rating'] ?? 4.5) ?>"></div>
          <div class="form-group"><label>Review Count</label><input type="number" name="reviews_count" class="form-control" min="0" value="<?= h($p['reviews_count'] ?? 0) ?>"></div>
          <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-size:14px; color:var(--text);">
            <input type="checkbox" name="featured" <?= !empty($p['featured'])?'checked':'' ?> style="accent-color:var(--cyan); width:16px; height:16px;">
            ⭐ Mark as Featured Product
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">
        <?= $edit_id ? '💾 Save Changes' : '⚡ Add Product' ?>
      </button>
    </div>
  </div>
</form>

<?php admin_footer(); ?>
