<?php
require 'config/database.php';

$type = $_GET['type'] ?? 'user';

if ($type === 'admin') {
    unset($_SESSION['admin_logged_in']);
    unset($_SESSION['admin_name']);
    header("Location: admin/login.php");
    exit;
} else {
    unset($_SESSION['user_id']);
    unset($_SESSION['name']);
    redirect('login.php?msg=logout');
}