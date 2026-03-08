<?php
// admin/delete_user.php - delete a specific user
session_start();
if(!isset($_SESSION["admin"])){
    header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'error'=>'unauthorized']);
    exit;
}

header('Content-Type: application/json');

$userId = trim($_POST['id'] ?? '');

if($userId === ''){
    echo json_encode(['ok'=>false,'error'=>'id_required']);
    exit;
}

$usersFile = __DIR__ . '/../users.json';
if(!file_exists($usersFile)){
    echo json_encode(['ok'=>false,'error'=>'no_users']);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

if(!isset($users[$userId])){
    echo json_encode(['ok'=>false,'error'=>'user_not_found']);
    exit;
}

// delete the user
unset($users[$userId]);

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['ok'=>true,'message'=>'User deleted successfully']);
?>
