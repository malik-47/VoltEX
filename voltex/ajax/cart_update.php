<?php
require_once __DIR__ . '/../config.php';
if (!is_logged_in()) redirect('../login.php');

$uid    = (int)$_SESSION['user_id'];
$pid    = (int)($_POST['product_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($pid) {
    $row  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT quantity FROM cart WHERE user_id=$uid AND product_id=$pid"));
    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE id=$pid"));
    if ($row && $prod) {
        $qty = $row['quantity'];
        if ($action === 'increase') $qty = min($qty + 1, $prod['stock']);
        if ($action === 'decrease') $qty = max($qty - 1, 1);
        mysqli_query($conn, "UPDATE cart SET quantity=$qty WHERE user_id=$uid AND product_id=$pid");
    }
}
redirect('../cart.php');
