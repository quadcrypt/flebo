<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

$configFile = __DIR__ . '/../smtp_config.php';
$config = [];
if(file_exists($configFile)){
    $config = include $configFile;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $cfg = [
        'SMTP_HOST' => trim($_POST['smtp_host'] ?? ''),
        'SMTP_USER' => trim($_POST['smtp_user'] ?? ''),
        'SMTP_PASS' => trim($_POST['smtp_pass'] ?? ''),
        'SMTP_PORT' => intval($_POST['smtp_port'] ?? 587),
        'SMTP_SECURE' => trim($_POST['smtp_secure'] ?? 'tls'),
        'SMTP_TEST_TO' => trim($_POST['smtp_test_to'] ?? ''),
    ];

    $export = "<?php\nreturn " . var_export($cfg, true) . ";\n";
    if(file_put_contents($configFile, $export, LOCK_EX) === false){
        $message = 'Failed to save settings.';
    } else {
        $message = 'Settings saved.';
    }
    $config = $cfg;
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>SMTP Settings</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8 bg-gray-100">
<div class="max-w-2xl mx-auto bg-white p-6 rounded shadow">
<h1 class="text-xl font-bold mb-4">SMTP Settings</h1>
<?php if(!empty($message)): ?>
    <div class="mb-4 text-sm text-green-700"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<form method="post">
    <label class="block mb-2">SMTP Host
        <input name="smtp_host" class="border p-2 w-full" value="<?= htmlspecialchars($config['SMTP_HOST'] ?? 'smtp.gmail.com') ?>">
    </label>
    <label class="block mb-2">SMTP User (email)
        <input name="smtp_user" class="border p-2 w-full" value="<?= htmlspecialchars($config['SMTP_USER'] ?? '') ?>">
    </label>
    <label class="block mb-2">SMTP App Password
        <input name="smtp_pass" class="border p-2 w-full" value="<?= htmlspecialchars($config['SMTP_PASS'] ?? '') ?>">
    </label>
    <label class="block mb-2">Port
        <input name="smtp_port" class="border p-2 w-full" value="<?= htmlspecialchars($config['SMTP_PORT'] ?? 587) ?>">
    </label>
    <label class="block mb-4">Secure
        <select name="smtp_secure" class="border p-2 w-full">
            <option value="tls" <?= (isset($config['SMTP_SECURE']) && $config['SMTP_SECURE']==='tls')? 'selected':'' ?>>tls</option>
            <option value="ssl" <?= (isset($config['SMTP_SECURE']) && $config['SMTP_SECURE']==='ssl')? 'selected':'' ?>>ssl</option>
        </select>
    </label>
    <label class="block mb-4">Test recipient (optional)
        <input name="smtp_test_to" class="border p-2 w-full" value="<?= htmlspecialchars($config['SMTP_TEST_TO'] ?? '') ?>">
    </label>
    <div class="flex gap-2">
        <button class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
        <a href="dashboard.php" class="px-4 py-2 rounded border">Back</a>
    </div>
</form>
</div>
</body>
</html>
