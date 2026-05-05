<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success'=>false,'message'=>'Please login first','redirect'=>SITE_URL.'/login.php']);
    exit;
}

$uid    = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'add') {
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if (!$pid) { echo json_encode(['success'=>false,'message'=>'Invalid product']); exit; }

    // Check stock
    $prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT stock FROM products WHERE id=$pid"));
    if (!$prod || $prod['stock'] < 1) { echo json_encode(['success'=>false,'message'=>'Out of stock']); exit; }

    $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id, quantity FROM cart WHERE user_id=$uid AND product_id=$pid"));
    if ($existing) {
        $new_qty = min($existing['quantity'] + $qty, $prod['stock']);
        mysqli_query($conn, "UPDATE cart SET quantity=$new_qty WHERE id={$existing['id']}");
    } else {
        mysqli_query($conn, "INSERT INTO cart (user_id,product_id,quantity) VALUES ($uid,$pid,$qty)");
    }
}

if ($action === 'remove') {
    $pid = (int)($_POST['product_id'] ?? 0);
    mysqli_query($conn, "DELETE FROM cart WHERE user_id=$uid AND product_id=$pid");
}

if ($action === 'clear') {
    mysqli_query($conn, "DELETE FROM cart WHERE user_id=$uid");
    header('Location: ' . SITE_URL . '/cart.php'); exit;
}

$count = (int)mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(quantity) FROM cart WHERE user_id=$uid"))[0];
echo json_encode(['success'=>true,'count'=>$count]);
