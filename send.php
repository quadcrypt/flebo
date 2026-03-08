<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// PHPMailer distribution in this project is located in PHP-mailer/ (non-standard folder name)
require __DIR__ . '/PHP-mailer/Exception.php';
require __DIR__ . '/PHP-mailer/PHPMailer.php';
require __DIR__ . '/PHP-mailer/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit("Invalid request.");
}

/* =============================
   VALIDATE INPUTS
============================= */

if (!isset($_POST["cart"]) || empty($_POST["cart"])) {
    exit("Cart is empty.");
}

$name     = htmlspecialchars(trim($_POST["name"] ?? ''));
$emailRaw = $_POST["email"] ?? '';
$email    = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$address  = htmlspecialchars(trim($_POST["address"] ?? ''));
$delivery = htmlspecialchars(trim($_POST["delivery"] ?? ''));
$cart     = json_decode($_POST["cart"] ?? '[]', true);

if (!$email) {
    exit("Invalid email address.");
}

if (!is_array($cart) || count($cart) == 0) {
    exit("Invalid cart data.");
}

/* =============================
   SETUP MAILER
============================= */

$smtpHost   = getenv('SMTP_HOST') ?: null;
$smtpUser   = getenv('SMTP_USER') ?: null;
$smtpPass   = getenv('SMTP_PASS') ?: null;
$smtpPort   = getenv('SMTP_PORT') ?: null;
$smtpSecure = getenv('SMTP_SECURE') ?: null;

// If config file exists, prefer it when env vars are not set
$cfgFile = __DIR__ . '/smtp_config.php';
if(file_exists($cfgFile)){
    $cfg = include $cfgFile;
    $smtpHost   = $smtpHost   ?? ($cfg['SMTP_HOST'] ?? 'smtp.gmail.com');
    $smtpUser   = $smtpUser   ?? ($cfg['SMTP_USER'] ?? null);
    $smtpPass   = $smtpPass   ?? ($cfg['SMTP_PASS'] ?? null);
    $smtpPort   = $smtpPort   ?? ($cfg['SMTP_PORT'] ?? 587);
    $smtpSecure = $smtpSecure ?? ($cfg['SMTP_SECURE'] ?? 'tls');
} else {
    $smtpHost   = $smtpHost   ?? 'smtp.gmail.com';
    $smtpPort   = $smtpPort   ?? 587;
    $smtpSecure = $smtpSecure ?? 'tls';
}

/* =============================
   Inline SMTP configuration (edit here)
   If you prefer central config, remove values here and use smtp_config.php or environment vars.
============================= */
$inlineSmtp = [
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_USER' => 'obuahsilas2021@gmail.com', // your@gmail.com
    'SMTP_PASS' => 'dejexawnqzovdxui', // app password (16 chars) or SMTP password
    'SMTP_PORT' => 587,
    'SMTP_SECURE' => 'tls',
];

// Prefer inline settings when provided
if(!empty($inlineSmtp['SMTP_USER'])){
    $smtpHost   = $inlineSmtp['SMTP_HOST'];
    $smtpUser   = $inlineSmtp['SMTP_USER'];
    $smtpPass   = $inlineSmtp['SMTP_PASS'];
    $smtpPort   = $inlineSmtp['SMTP_PORT'];
    $smtpSecure = $inlineSmtp['SMTP_SECURE'];
}

$mail = new PHPMailer(true);

try {
    if (empty($smtpUser) || empty($smtpPass)) {
        error_log("send.php: SMTP_USER or SMTP_PASS is not set. Set environment variables or update configuration.");
        throw new Exception('SMTP credentials not configured.');
    }

    // Optional debug: use ?debug=1 to print SMTP debug to browser for testing
    $debugToBrowser = isset($_GET['debug']) && $_GET['debug'] === '1';
    if ($debugToBrowser) {
        $mail->SMTPDebug = 3;
        $mail->Debugoutput = function($str, $level) { echo "<pre>PHPMailer: " . htmlspecialchars($str) . "</pre>"; };
    } else {
        // default: send debug to PHP error log
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) { error_log("PHPMailer: " . $str); };
    }

    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;
    $mail->SMTPSecure = $smtpSecure;
    $mail->Port       = $smtpPort;
    $mail->SMTPOptions = [
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ];

    $mail->isHTML(true);

    /* =============================
       BUILD ORDER TABLE
    ============================= */

    $total = 0;
    // Build table with image column and embed local images when possible
    $table = "<table border='1' cellpadding='8' width='100%' style='border-collapse:collapse'>
    <tr style='background:#f3f4f6'>
        <th>Image</th>
        <th>Product</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Subtotal</th>
    </tr>";

    foreach ($cart as $i => $item) {
        $price = floatval($item["price"]);
        $qty   = intval($item["qty"]);
        $sub   = $price * $qty;
        $total += $sub;

        $imgHtml = '';
        // Prefer server-local images for embedding
        $imgPath = $item['image'] ?? '';
        if ($imgPath) {
            // If the image path is an absolute URL, use it directly
            if (preg_match('#^https?://#i', $imgPath)) {
                $imgHtml = "<img src='" . htmlspecialchars($imgPath) . "' style='width:80px;height:80px;object-fit:cover'>";
            } else {
                // Resolve local path
                $local = __DIR__ . '/' . ltrim($imgPath, '/\\');
                if (file_exists($local)) {
                    $cid = 'prod' . $i . '_' . substr(md5($imgPath . time()),0,8);
                    try {
                        $mail->addEmbeddedImage($local, $cid);
                        $imgHtml = "<img src='cid:" . $cid . "' style='width:80px;height:80px;object-fit:cover'>";
                    } catch (Exception $e) {
                        // fallback to path text
                        $imgHtml = "<div style='width:80px;height:80px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:12px;color:#666'>Image</div>";
                    }
                } else {
                    $imgHtml = "<div style='width:80px;height:80px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:12px;color:#666'>No image</div>";
                }
            }
        } else {
            $imgHtml = "<div style='width:80px;height:80px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:12px;color:#666'>No image</div>";
        }

        $table .= "<tr>
            <td style='width:90px'>" . $imgHtml . "</td>
            <td>" . htmlspecialchars($item['name'] ?? '') . "</td>
            <td align='center'>{$qty}</td>
            <td align='right'>₦" . number_format($price) . "</td>
            <td align='right'>₦" . number_format($sub) . "</td>
        </tr>";
    }

    $table .= "</table>";

    /* =============================
       PERSIST ORDER IMMEDIATELY (so UI responds quickly)
    ============================= */
    $order = [
        'id' => uniqid('ord_'),
        'name' => $name,
        'email' => $email,
        'user_id' => $_POST['user_id'] ?? null,
        'address' => $address,
        'delivery' => $delivery,
        'cart' => $cart,
        'total' => $total,
        'attended' => false,
        'created_at' => date('c')
    ];

    $ordersFile = __DIR__ . '/orders.json';
    $orders = [];
    if (file_exists($ordersFile)) {
        $orders = json_decode(file_get_contents($ordersFile), true) ?: [];
    }
    $orders[] = $order;
    // write with exclusive lock
    file_put_contents($ordersFile, json_encode($orders, JSON_PRETTY_PRINT), LOCK_EX);

    // send immediate response to client and continue processing in background
    // Only do the fast-close when not in debug-to-browser mode (debug prints must appear in output)
    if (!$debugToBrowser) {
        ignore_user_abort(true);
        if (ob_get_level()) { ob_end_clean(); }
        $response = "success";
        header('Content-Type: text/plain');
        header('Connection: close');
        header('Content-Length: ' . strlen($response));
        echo $response;
        flush();
    }
    /* =============================
       SEND ADMIN EMAIL
    ============================= */

    $mail->setFrom($smtpUser, 'FLEBO Store');
    $mail->addAddress($smtpUser);
    $mail->Subject = "New Order from $name";

    $mail->Body = "
        <h2>New Order Received</h2>
        <p>
        <b>Customer:</b> " . htmlspecialchars($name) . " <br>
        <b>Email:</b> " . htmlspecialchars($email) . " <br>
        <b>Address:</b> " . nl2br(htmlspecialchars($address)) . " <br>
        <b>Delivery Method:</b> " . htmlspecialchars($delivery) . "
        </p>
        <hr>
        $table
        <h3 style='text-align:right'>Grand Total: ₦" . number_format($total) . "</h3>
    ";

    $mail->send();

    /* =============================
       SEND CUSTOMER EMAIL
    ============================= */

    $mail->clearAddresses();
    $mail->addAddress($email);
    $mail->Subject = "Thank You For Your Order - FLEBO Store";

    $mail->Body = "
        <h2>Thank you, " . htmlspecialchars($name) . "!</h2>
        <p>Your order has been received successfully.</p>
        <p><b>Delivery Method:</b> " . htmlspecialchars($delivery) . "</p>
        <p><b>Total Amount:</b> ₦" . number_format($total) . "</p>
        <p>We will contact you shortly regarding delivery to:</p>
        <p><i>" . nl2br(htmlspecialchars($address)) . "</i></p>
        <br>
        <p>We appreciate your patronage.</p>
        <p><b>FLEBO Store</b></p>
    ";

    $mail->send();

} catch (Exception $e) {
    error_log('send.php exception: ' . $e->getMessage());
    // Include PHPMailer ErrorInfo when available
    $err = isset($mail) ? $mail->ErrorInfo : '';
    echo "Mailer Error: " . ($err ? $err : $e->getMessage());
}