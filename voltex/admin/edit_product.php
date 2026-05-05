<?php
require_once __DIR__ . '/config.php';
$id = (int)($_GET['id'] ?? 0);
redirect(SITE_URL . '/admin/add_product.php?id=' . $id);
