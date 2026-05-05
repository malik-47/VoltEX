<?php
require_once __DIR__ . '/../config.php';
if (is_admin()) redirect(SITE_URL . '/admin/dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM admins WHERE username='".mysqli_real_escape_string($conn,$username)."'"));
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_user'] = $admin['username'];
        redirect(SITE_URL . '/admin/dashboard.php');
    } else { $error = 'Invalid username or password.'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login — VOLTEX</title>
  <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
</head>
<body>
<div id="toast"><span id="toast-msg"></span></div>
<div style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px;">
  <div style="width:100%; max-width:420px;">
    <div style="text-align:center; margin-bottom:32px;">
      <div style="font-family:var(--font-head); font-size:32px; font-weight:900; color:var(--white); letter-spacing:4px;">VOLT<span style="color:var(--cyan)">EX</span></div>
      <div style="font-family:var(--font-sub); font-size:11px; letter-spacing:4px; color:var(--orange); margin-top:4px; text-transform:uppercase;">Admin Panel</div>
    </div>
    <div class="card">
      <div class="card-header"><h2>🔐 Admin Login</h2></div>
      <div class="card-body">
        <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>
        <form method="POST">
          <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control" required autofocus value="<?= h($_POST['username'] ?? '') ?>"></div>
          <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
          <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In</button>
        </form>
        <p style="text-align:center; margin-top:16px; font-size:13px; color:var(--text2);">Contact your administrator if you've forgotten your credentials.</p>
      </div>
    </div>
    <p style="text-align:center; margin-top:16px; font-size:13px;"><a href="<?= SITE_URL ?>/index.php" style="color:var(--cyan);">← Back to Store</a></p>
  </div>
</div>
</body></html>
