<?php
require_once __DIR__ . '/../config.php';
unset($_SESSION['admin_id'], $_SESSION['admin_user']);
redirect(SITE_URL . '/admin/login.php');
