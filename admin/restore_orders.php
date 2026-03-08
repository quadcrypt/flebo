<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$backupDir = __DIR__ . '/backups';
$ordersFile = __DIR__ . '/../orders.json';

$file = $_POST['file'] ?? '';
if(!$file) { header('Location: orders.php?msg=invalid'); exit; }

// sanitize: only basename and allow .json
$base = basename($file);
if(pathinfo($base, PATHINFO_EXTENSION) !== 'json') { header('Location: orders.php?msg=invalid'); exit; }
$src = $backupDir . '/' . $base;
if(!file_exists($src)) { header('Location: orders.php?msg=notfound'); exit; }

// backup current orders
if(file_exists($ordersFile)){
    if(!is_dir($backupDir)) mkdir($backupDir, 0755, true);
    $backupFile = $backupDir . '/orders_pre_restore_' . date('Ymd_His') . '.json';
    copy($ordersFile, $backupFile);
}

// restore
if(copy($src, $ordersFile)){
    header('Location: orders.php?msg=restored');
    exit;
} else {
    header('Location: orders.php?msg=restore_failed');
    exit;
}
