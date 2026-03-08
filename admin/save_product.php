<?php
session_start();
if(!isset($_SESSION["admin"])) {
    header("Location: login.php");
    exit;
}
// Resolve products file path
$file = __DIR__ . '/../products.json';

if(!file_exists($file)){
    file_put_contents($file, "[]", LOCK_EX);
}

$products = json_decode(file_get_contents($file), true);
if(!is_array($products)) $products = [];

// Sanitize and validate inputs
$name = trim($_POST['name'] ?? '');
$name = strip_tags($name);
$price = isset($_POST['price']) ? intval($_POST['price']) : 0;
if($price < 0) $price = 0;
$category = trim($_POST['category'] ?? '');
$category = strip_tags($category);

$imagePath = '';
$errors = [];

// Handle image upload with validations
if(isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK){
    $tmp = $_FILES['image']['tmp_name'];
    $size = $_FILES['image']['size'] ?? 0;
    // limit 3MB
    if($size > 3 * 1024 * 1024){
        $errors[] = 'Image size exceeds 3MB limit.';
    } else {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];

        if(!array_key_exists($mime, $allowed)){
            $errors[] = 'Invalid image type. Allowed: JPG, PNG, WEBP.';
        } else {
            $ext = $allowed[$mime];
            $uploadDir = __DIR__ . '/../uploads/';
            if(!is_dir($uploadDir)){
                mkdir($uploadDir, 0755, true);
            }

            // create .htaccess to prevent script execution (if Apache)
            $ht = $uploadDir . '.htaccess';
            if(!file_exists($ht)){
                @file_put_contents($ht, "Options -Indexes\n<IfModule mod_php7.c>\n    php_flag engine off\n</IfModule>\n", LOCK_EX);
            }

            try{
                $safeName = bin2hex(random_bytes(12)) . '.' . $ext;
            }catch(Exception $e){
                $safeName = time() . '_' . substr(md5(uniqid('', true)),0,8) . '.' . $ext;
            }

            $target = $uploadDir . $safeName;
            if(!move_uploaded_file($tmp, $target)){
                $errors[] = 'Failed to move uploaded file.';
            } else {
                @chmod($target, 0644);
                $imagePath = 'uploads/' . $safeName; // relative path for web
            }
        }
    }
} else {
    $errors[] = 'Image upload is required.';
}

if($name === '') $errors[] = 'Product name is required.';
if($category === '') $errors[] = 'Category is required.';

if(!empty($errors)){
    $_SESSION['save_product_errors'] = $errors;
    header('Location: dashboard.php');
    exit;
}

$id = '';
try{
    $id = bin2hex(random_bytes(8));
}catch(Exception $e){
    $id = uniqid();
}

$products[] = [
    'id' => $id,
    'name' => $name,
    'price' => $price,
    'category' => $category,
    'image' => $imagePath
];

$res = file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT), LOCK_EX);
if($res === false){
    $_SESSION['save_product_errors'] = ['Failed to save product data.'];
    header('Location: dashboard.php');
    exit;
}

$_SESSION['save_product_success'] = 'Product added successfully.';
header('Location: dashboard.php');
exit;
?>