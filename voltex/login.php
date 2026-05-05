<?php
// login.php
require_once __DIR__ . '/config.php';
if (is_logged_in()) redirect('account.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $user  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE email='".mysqli_real_escape_string($conn,$email)."'"));
    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $redirect = $_SESSION['redirect_after_login'] ?? 'account.php';
        unset($_SESSION['redirect_after_login']);
        redirect($redirect);
    } else { $error = 'Invalid email or password.'; }
}
$page_title = 'Sign In';
include 'includes/header.php';
?>
<div class="container" style="max-width:460px; padding:60px 24px;">
  <div class="card">
    <div class="card-header"><h2>⚡ Sign In to VOLTEX</h2></div>
    <div class="card-body">
      <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-group"><label>Email Address</label><input type="email" name="email" class="form-control" required autofocus value="<?= h($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">Sign In</button>
        <p style="text-align:center; margin-top:16px; color:var(--text2); font-size:14px;">Don't have an account? <a href="register.php" style="color:var(--cyan);">Register</a></p>
      </form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
