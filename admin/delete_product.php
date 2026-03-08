<?php
session_start();
if(!isset($_SESSION["admin"])) exit;

$file = __DIR__ . "/../products.json";
$products = json_decode(file_get_contents($file), true);

$id = $_GET["id"] ?? null;
if($id === null){
	header("Location: dashboard.php");
	exit;
}

// Find product by id; if $id is numeric and matches index fallback
$found = null;
foreach($products as $idx => $p){
	if(isset($p['id']) && $p['id'] == $id){
		$found = $idx;
		break;
	}
}
if($found === null && is_numeric($id) && isset($products[intval($id)])){
	$found = intval($id);
}

if($found !== null){
	$imagePath = __DIR__ . '/../' . ltrim($products[$found]['image'], '/\\');
	if(file_exists($imagePath)) @unlink($imagePath);
	array_splice($products, $found, 1);
	file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT), LOCK_EX);
}

header("Location: dashboard.php");