<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$ordersFile = __DIR__ . '/../orders.json';
$orders = [];
if(file_exists($ordersFile)){
    $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
}

$scope = $_POST['scope'] ?? '';

function bucket_for_order($o){
    $date = $o['created_at'] ?? ($o['date'] ?? null);
    if(!$date) return 'older';
    $ts = @strtotime($date);
    if(!$ts) return 'older';
    $days = (time() - $ts) / 86400;
    if($days < 1) return 'today';
    if($days < 2) return 'yesterday';
    if($days < 7) return 'last7';
    if($days < 30) return 'last30';
    return 'older';
}

if($scope === 'all'){
    // backup then clear all orders
    if (file_exists($ordersFile)) {
        $backupDir = __DIR__ . '/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        $backupFile = $backupDir . '/orders.' . date('Ymd_His') . '.json';
        copy($ordersFile, $backupFile);
    }
    file_put_contents($ordersFile, json_encode([], JSON_PRETTY_PRINT), LOCK_EX);
    header('Location: orders.php?msg=cleared');
    exit;
}

if($scope === 'bucket'){
    $bucket = $_POST['bucket'] ?? '';
    $attendedVal = isset($_POST['attended']) ? (int)$_POST['attended'] : null;
    if(!$bucket){
        header('Location: orders.php?msg=invalid');
        exit;
    }
    $new = [];
    foreach($orders as $o){
        $k = bucket_for_order($o);
        $isAtt = isset($o['attended']) && $o['attended'] ? 1 : 0;
        if($k === $bucket && ($attendedVal === null || $attendedVal === $isAtt)){
            // skip (remove)
            continue;
        }
        $new[] = $o;
    }
    // backup current orders then write filtered list
    if (file_exists($ordersFile)) {
        $backupDir = __DIR__ . '/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);
        $backupFile = $backupDir . '/orders.' . date('Ymd_His') . '.json';
        copy($ordersFile, $backupFile);
    }
    file_put_contents($ordersFile, json_encode($new, JSON_PRETTY_PRINT), LOCK_EX);
    header('Location: orders.php?msg=cleared_bucket');
    exit;
}

header('Location: orders.php');
exit;
