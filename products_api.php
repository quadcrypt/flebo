<?php
// Return products.json as JSON for client-side refresh
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/products.json';
if(!file_exists($file)){
    echo json_encode([]);
    exit;
}
$data = file_get_contents($file);
if($data === false){
    echo json_encode([]);
} else {
    $products = json_decode($data, true);
    $changed = false;
    if(is_array($products)){
        foreach($products as $i => $p){
            if(!isset($p['id']) || $p['id'] === ''){
                try{
                    $products[$i]['id'] = bin2hex(random_bytes(8));
                }catch(Exception $e){
                    $products[$i]['id'] = uniqid();
                }
                $changed = true;
            }
        }
        if($changed){
            // persist ids back to products.json
            file_put_contents($file, json_encode($products, JSON_PRETTY_PRINT), LOCK_EX);
        }
    }
    echo json_encode($products ?: []);
}
exit;
