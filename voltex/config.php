<?php
session_start();

define('SITE_NAME', 'VOLTEX');
define('SITE_URL',  'http://localhost/voltex');
define('CURRENCY',  'Rs. ');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "voltex_store";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("DB Error: " . mysqli_connect_error());
mysqli_set_charset($conn, 'utf8mb4');

function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function price($n) { return CURRENCY . number_format($n, 0); }

function cart_count() {
    global $conn;
    if (empty($_SESSION['user_id'])) return 0;
    $uid = (int)$_SESSION['user_id'];
    $r = mysqli_fetch_row(mysqli_query($conn, "SELECT SUM(quantity) FROM cart WHERE user_id=$uid"));
    return (int)$r[0];
}

function is_logged_in() { return !empty($_SESSION['user_id']); }
function is_admin()     { return !empty($_SESSION['admin_id']); }

function redirect($url) { header("Location: $url"); exit; }

function slug($s) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $s)));
}
