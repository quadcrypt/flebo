<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$ordersFile = __DIR__ . '/../orders.json';
if($id === null || !file_exists($ordersFile)){
    header('Location: dashboard.php');
    exit;
}

$orders = json_decode(file_get_contents($ordersFile), true);
if(!is_array($orders) || !isset($orders[$id])){
    header('Location: dashboard.php');
    exit;
}

$orders[$id]['attended'] = true;

file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT), LOCK_EX);

$return = isset($_GET['return']) && $_GET['return'] === 'orders' ? 'orders.php' : 'dashboard.php';
header('Location: ' . $return);
exit;
