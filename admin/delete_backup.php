<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$backupDir = __DIR__ . '/backups';
$file = $_POST['file'] ?? '';
if(!$file){ header('Location: orders.php?msg=invalid'); exit; }
$base = basename($file);
$path = $backupDir . '/' . $base;
if(!file_exists($path)){ header('Location: orders.php?msg=notfound'); exit; }
// prevent deleting unexpected files
if(pathinfo($base, PATHINFO_EXTENSION) !== 'json'){ header('Location: orders.php?msg=invalid'); exit; }

if(unlink($path)){
    header('Location: orders.php?msg=deleted');
    exit;
} else {
    header('Location: orders.php?msg=delete_failed');
    exit;
}
