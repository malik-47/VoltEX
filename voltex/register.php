<?php
require_once __DIR__ . '/config.php';
if (is_logged_in()) redirect('account.php');
$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    if (!$name || !$email || !$pass) { $error = 'Name, email and password are required.'; }
    elseif (strlen($pass) < 6) { $error = 'Password must be at least 6 characters.'; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $error = 'Invalid email address.'; }
    else {
        $esc = mysqli_real_escape_string($conn, $email);
        if (mysqli_num_rows(mysqli_query($conn, "SELECT id FROM users WHERE email='$esc'"))) {
            $error = 'Email already registered. Please login.';
        } else {
            $hashed = password_hash($pass, PASSWORD_BCRYPT);
            $n  = mysqli_real_escape_string($conn, $name);
            $ph = mysqli_real_escape_string($conn, $phone);
            mysqli_query($conn, "INSERT INTO users (name,email,password,phone) VALUES ('$n','$esc','$hashed','$ph')");
            $_SESSION['user_id'] = mysqli_insert_id($conn);
            $_SESSION['user_name'] = $name;
            redirect('account.php');
        }
    }
}
$page_title = 'Create Account';
include 'includes/header.php';
?>
<div class="container" style="max-width:520px; padding:60px 24px;">
  <div class="card">
    <div class="card-header"><h2>⚡ Create Your Account</h2></div>
    <div class="card-body">
      <?php if ($error): ?><div class="alert alert-error">⚠️ <?= h($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="form-grid">
          <div class="form-group"><label>Full Name *</label><input type="text" name="name" class="form-control" required value="<?= h($_POST['name'] ?? '') ?>"></div>
          <div class="form-group"><label>Phone</label><input type="tel" name="phone" class="form-control" value="<?= h($_POST['phone'] ?? '') ?>"></div>
        </div>
        <div class="form-group"><label>Email Address *</label><input type="email" name="email" class="form-control" required value="<?= h($_POST['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Password * (min 6 chars)</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary btn-full btn-lg">Create Account</button>
        <p style="text-align:center; margin-top:16px; color:var(--text2); font-size:14px;">Already have an account? <a href="login.php" style="color:var(--cyan);">Sign In</a></p>
      </form>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
