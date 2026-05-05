<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success'=>false,'message'=>'Please login first','redirect'=>SITE_URL.'/login.php']);
    exit;
}

$uid = (int)$_SESSION['user_id'];
$pid = (int)($_POST['product_id'] ?? 0);
if (!$pid) { echo json_encode(['success'=>false]); exit; }

$exists = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM wishlist WHERE user_id=$uid AND product_id=$pid"));
if ($exists) {
    mysqli_query($conn, "DELETE FROM wishlist WHERE user_id=$uid AND product_id=$pid");
    echo json_encode(['success'=>true,'wishlisted'=>false]);
} else {
    mysqli_query($conn, "INSERT INTO wishlist (user_id,product_id) VALUES ($uid,$pid)");
    echo json_encode(['success'=>true,'wishlisted'=>true]);
}
