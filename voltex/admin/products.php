<?php
require_once __DIR__ . '/config.php';
admin_guard();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM products WHERE id=$id");
    redirect(SITE_URL . '/admin/products.php?deleted=1');
}

// Handle toggle featured
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_featured'])) {
    $id = (int)$_POST['toggle_id'];
    mysqli_query($conn, "UPDATE products SET featured = NOT featured WHERE id=$id");
    redirect(SITE_URL . '/admin/products.php');
}

$q   = trim($_GET['q'] ?? '');
$cat = (int)($_GET['cat'] ?? 0);

$where = []; $conds = "1=1";
if ($q) { $esc = mysqli_real_escape_string($conn,$q); $where[] = "(p.name LIKE '%$esc%')"; }
if ($cat) $where[] = "p.category_id=$cat";
if ($where) $conds = implode(' AND ', $where);

$products   = mysqli_query($conn, "SELECT p.*, c.name as cat_name FROM products p JOIN categories c ON p.category_id=c.id WHERE $conds ORDER BY p.id DESC");
$categories = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

admin_header('Products');
?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;">PRODUCTS</h1>
  <a href="add_product.php" class="btn btn-primary btn-sm">+ Add New Product</a>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">✅ Product deleted.</div><?php endif; ?>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">✅ Product saved successfully.</div><?php endif; ?>

<form method="GET" style="display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap;">
  <div class="search-bar" style="flex:1; min-width:220px; position:relative;">
    <input type="text" name="q" class="form-control" placeholder="Search products…" value="<?= h($q) ?>" style="padding-left:36px;">
    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text2);">🔍</span>
  </div>
  <select name="cat" class="form-control" style="width:180px;">
    <option value="">All Categories</option>
    <?php mysqli_data_seek($categories,0); while($c=mysqli_fetch_assoc($categories)): ?>
    <option value="<?= $c['id'] ?>" <?= $cat===$c['id']?'selected':'' ?>><?= h($c['name']) ?></option>
    <?php endwhile; ?>
  </select>
  <button type="submit" class="btn btn-ghost btn-sm">Filter</button>
  <?php if ($q||$cat): ?><a href="products.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<div class="card">
  <div class="card-header">
    <h2>All Products (<?= mysqli_num_rows($products) ?>)</h2>
  </div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Rating</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($p = mysqli_fetch_assoc($products)): ?>
        <tr>
          <td style="color:var(--text2); font-size:12px;"><?= $p['id'] ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:12px;">
              <img src="<?= h($p['image_url']) ?>" style="width:44px; height:44px; object-fit:cover; border-radius:8px; border:1px solid var(--border);" onerror="this.src='https://images.unsplash.com/photo-1498049794561-7780e7231661?w=100'">
              <div>
                <div style="font-weight:600; color:var(--white); font-size:13px;"><?= h(substr($p['name'],0,40)) ?></div>
                <?php if ($p['badge']): ?><span class="badge badge-orange" style="font-size:10px;"><?= h($p['badge']) ?></span><?php endif; ?>
              </div>
            </div>
          </td>
          <td style="color:var(--text2); font-size:13px;"><?= h($p['cat_name']) ?></td>
          <td>
            <div style="font-family:var(--font-head); font-size:13px; color:var(--cyan);"><?= price($p['price']) ?></div>
            <?php if ($p['original_price']): ?><div style="font-size:11px; color:var(--text2); text-decoration:line-through;"><?= price($p['original_price']) ?></div><?php endif; ?>
          </td>
          <td><span style="color:<?= $p['stock']<5?'var(--warning)':($p['stock']===0?'var(--danger)':'var(--success)') ?>; font-weight:600;"><?= $p['stock'] ?></span></td>
          <td>
            <form method="POST" style="display:inline;">
              <input type="hidden" name="toggle_featured" value="1">
              <input type="hidden" name="toggle_id" value="<?= $p['id'] ?>">
              <button type="submit" class="badge <?= $p['featured']?'badge-success':'badge-danger' ?>" style="cursor:pointer; border:none; background:none; font:inherit;">
                <?= $p['featured'] ? '⭐ Yes' : 'No' ?>
              </button>
            </form>
          </td>
          <td><span class="badge badge-warn">⭐ <?= $p['rating'] ?></span></td>
          <td>
            <div style="display:flex; gap:6px;">
              <a href="edit_product.php?id=<?= $p['id'] ?>" class="btn btn-ghost btn-sm">✏️</a>
              <a href="<?= SITE_URL ?>/product.php?slug=<?= h($p['slug']) ?>" target="_blank" class="btn btn-ghost btn-sm">👁</a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
                <input type="hidden" name="delete_product" value="1">
                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);">🗑️</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer(); ?>
