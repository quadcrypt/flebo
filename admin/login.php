<?php
session_start();

$correctUser = "flebo";
$hashedPassword = password_hash("flebo1234", PASSWORD_DEFAULT);

if($_SERVER["REQUEST_METHOD"]=="POST"){

	$username = trim($_POST["username"] ?? '');
	$password = $_POST["password"] ?? '';

	if($username === $correctUser && password_verify($password, $hashedPassword)){
$_SESSION["admin"]=true;
header("Location: dashboard.php");
exit;
}else{
$error="Invalid credentials";
}
}
?>

<!DOCTYPE html>
<html>
<head>
<script src="https://cdn.tailwindcss.com"></script>
<title>Admin Login</title>
<link rel="icon" type="image/png" href="iso.png">

<link rel="apple-touch-icon" href="iso.png">

<link rel="shortcut icon" href="iso.png" type="image/x-icon">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<form method="POST" class="bg-white p-8 rounded shadow w-80">
<h2 class="text-xl font-bold mb-4">Admin Login</h2>

<input name="username" placeholder="Username"
class="border w-full p-2 mb-3" required>

<input name="password" type="password" placeholder="Password"
class="border w-full p-2 mb-3" required>

<button class="bg-green-600 text-white w-full p-2 rounded">
Login
</button>

<?php if(isset($error)) echo "<p class='text-red-500 mt-2'>$error</p>"; ?>

</form>

</body>
</html>