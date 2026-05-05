<?php
require_once __DIR__ . '/config.php';
admin_guard();

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_cat'])) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '📦');
    $s    = slug($name);
    if ($name) {
        $n = mysqli_real_escape_string($conn,$name);
        $sl = mysqli_real_escape_string($conn,$s);
        $ic = mysqli_real_escape_string($conn,$icon);
        mysqli_query($conn, "INSERT INTO categories (name,slug,icon) VALUES ('$n','$sl','$ic')");
    }
    redirect(SITE_URL . '/admin/categories.php?saved=1');
}

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cat'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM categories WHERE id=$id");
    redirect(SITE_URL . '/admin/categories.php?deleted=1');
}

$categories = mysqli_query($conn, "SELECT c.*, COUNT(p.id) as pcount FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.name");
admin_header('Categories');
?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px;">
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;">CATEGORIES</h1>
</div>

<?php if (isset($_GET['saved'])): ?><div class="alert alert-success">✅ Category saved.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success">✅ Category deleted.</div><?php endif; ?>

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">
  <div class="card">
    <div class="card-header"><h2>All Categories (<?= mysqli_num_rows($categories) ?>)</h2></div>
    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead><tr><th>Icon</th><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($c = mysqli_fetch_assoc($categories)): ?>
          <tr>
            <td style="font-size:24px;"><?= $c['icon'] ?></td>
            <td style="font-weight:600; color:var(--white);"><?= h($c['name']) ?></td>
            <td style="font-family:monospace; font-size:12px; color:var(--text2);"><?= h($c['slug']) ?></td>
            <td><span class="badge badge-cyan"><?= $c['pcount'] ?></span></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                <input type="hidden" name="delete_cat" value="1">
                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);">🗑️</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2>Add New Category</h2></div>
    <div class="card-body">
      <form method="POST">
        <div class="form-group"><label>Category Name *</label><input type="text" name="name" class="form-control" required placeholder="e.g. Drones"></div>
        <div class="form-group"><label>Icon (Emoji)</label><input type="text" name="icon" class="form-control" value="📦" placeholder="e.g. 🚁"></div>
        <button type="submit" name="add_cat" class="btn btn-primary btn-full">Add Category</button>
      </form>
    </div>
  </div>
</div>

<?php admin_footer(); ?>
