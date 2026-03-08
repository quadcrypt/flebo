<?php
// save_user.php - create or update a user profile stored in users.json
header('Content-Type: application/json');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$id = trim($_POST['id'] ?? '');
// new fields
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if($name === ''){
    echo json_encode(['ok'=>false,'error'=>'name_required']);
    exit;
}

$usersFile = __DIR__ . '/users.json';
$users = [];
if(file_exists($usersFile)){
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
}

if($id){
    // update existing
    if(isset($users[$id])){
        $users[$id]['name'] = $name;
        $users[$id]['email'] = $email;
        $users[$id]['phone'] = $phone;
        $users[$id]['address'] = $address;
        $users[$id]['updated_at'] = date('c');
        // update username if provided
        if($username !== '') $users[$id]['username'] = $username;
        // update password only if provided (store hashed)
        if($password !== '') $users[$id]['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // treat as create if not found
        $id = '';
    }
}

if(!$id){
    // Creating new user - check for duplicate email/phone
    foreach($users as $existingId => $existingUser){
        if($email !== '' && ($existingUser['email'] ?? '') === $email){
            echo json_encode(['ok'=>false,'error'=>'email_exists']);
            exit;
        }
        if($phone !== '' && ($existingUser['phone'] ?? '') === $phone){
            echo json_encode(['ok'=>false,'error'=>'phone_exists']);
            exit;
        }
    }
    
    $id = uniqid('user_');
    $users[$id] = [
        'id' => $id,
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'username' => $username,
        // store password hash if provided
        'password_hash' => $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : '',
        'created_at' => date('c')
    ];
}

file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT), LOCK_EX);

echo json_encode(['ok'=>true,'id'=>$id,'user'=>$users[$id]]);
