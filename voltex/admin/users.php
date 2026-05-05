<?php
require_once __DIR__ . '/config.php';
admin_guard();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$id");
    redirect(SITE_URL . '/admin/users.php?deleted=1');
}

$search = trim($_GET['q'] ?? '');
$cond = "1=1";
if ($search) { $esc = mysqli_real_escape_string($conn,$search); $cond = "(name LIKE '%$esc%' OR email LIKE '%$esc%')"; }

$users = mysqli_query($conn, "SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as order_count, (SELECT SUM(total_amount) FROM orders WHERE user_id=u.id AND status!='cancelled') as total_spent FROM users u WHERE $cond ORDER BY u.created_at DESC");

admin_header('Customers');
?>
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
  <h1 style="font-family:var(--font-head); font-size:20px; color:var(--white); letter-spacing:1px;">CUSTOMERS</h1>
</div>

<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" style="margin-bottom:16px;">✅ User deleted.</div><?php endif; ?>

<form method="GET" style="display:flex; gap:12px; margin-bottom:20px;">
  <div style="flex:1; max-width:360px; position:relative;">
    <input type="text" name="q" class="form-control" placeholder="Search name or email…" value="<?= h($search) ?>" style="padding-left:36px;">
    <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text2);">🔍</span>
  </div>
  <button type="submit" class="btn btn-ghost btn-sm">Search</button>
  <?php if ($search): ?><a href="users.php" class="btn btn-ghost btn-sm">Clear</a><?php endif; ?>
</form>

<div class="card">
  <div class="card-header"><h2>All Customers (<?= mysqli_num_rows($users) ?>)</h2></div>
  <div style="overflow-x:auto;">
    <table class="data-table">
      <thead><tr><th>#</th><th>Customer</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Joined</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($u = mysqli_fetch_assoc($users)): ?>
        <tr>
          <td style="color:var(--text2); font-size:12px;"><?= $u['id'] ?></td>
          <td>
            <div style="display:flex; align-items:center; gap:10px;">
              <div style="width:34px; height:34px; border-radius:50%; background:var(--cyan-soft); border:1px solid var(--border2); display:flex; align-items:center; justify-content:center; font-family:var(--font-head); font-size:14px; font-weight:800; color:var(--cyan); flex-shrink:0;">
                <?= strtoupper(substr($u['name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:600; color:var(--white); font-size:13px;"><?= h($u['name']) ?></div>
                <div style="font-size:11px; color:var(--text2);"><?= h($u['email']) ?></div>
              </div>
            </div>
          </td>
          <td style="font-size:13px; color:var(--text2);"><?= h($u['phone'] ?: '—') ?></td>
          <td><span class="badge badge-cyan"><?= $u['order_count'] ?> orders</span></td>
          <td style="font-family:var(--font-head); font-size:13px; color:var(--cyan);"><?= $u['total_spent'] ? price($u['total_spent']) : '—' ?></td>
          <td style="font-size:12px; color:var(--text2);"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <a href="mailto:<?= h($u['email']) ?>" class="btn btn-ghost btn-sm" title="Email">📧</a>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this customer?')">
              <input type="hidden" name="delete_user" value="1">
              <input type="hidden" name="delete_id" value="<?= $u['id'] ?>">
              <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);">🗑️</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php admin_footer(); ?>
