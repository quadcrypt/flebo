<?php
// login.php - validate username/password and return user profile
header('Content-Type: application/json');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if($username === '' || $password === ''){
    echo json_encode(['ok'=>false,'error'=>'credentials_required']);
    exit;
}

$usersFile = __DIR__ . '/users.json';
if(!file_exists($usersFile)){
    echo json_encode(['ok'=>false,'error'=>'no_users']);
    exit;
}

$users = json_decode(file_get_contents($usersFile), true) ?: [];

// find user by username
$found = null;
foreach($users as $id => $u){
    if(($u['username'] ?? '') === $username){
        $found = ['id' => $id, 'user' => $u];
        break;
    }
}

if(!$found){
    echo json_encode(['ok'=>false,'error'=>'invalid_credentials']);
    exit;
}

// verify password hash
if(!isset($found['user']['password_hash']) || !password_verify($password, $found['user']['password_hash'])){
    echo json_encode(['ok'=>false,'error'=>'invalid_credentials']);
    exit;
}

// success - return user profile
echo json_encode(['ok'=>true,'id'=>$found['id'],'user'=>$found['user']]);
?>
