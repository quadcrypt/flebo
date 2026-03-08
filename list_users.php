<?php
header('Content-Type: application/json');
$usersFile = __DIR__ . '/users.json';
$users = [];
if(file_exists($usersFile)){
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
}
// return array of users
echo json_encode(array_values($users));
