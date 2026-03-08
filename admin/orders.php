<?php
session_start();
if(!isset($_SESSION['admin'])){
    header('Location: login.php');
    exit;
}

// load messages
$msg = $_GET['msg'] ?? '';
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Orders - Admin</title>

<link rel="icon" type="image/png" href="iso.png">
<link rel="apple-touch-icon" href="iso.png">
<link rel="shortcut icon" href="iso.png" type="image/x-icon">

<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
<div class="max-w-6xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders</h1>
        <div class="flex gap-2 items-center">
            
            <form method="POST" action="clear_orders.php" onsubmit="return confirm('Are you sure you want to clear ALL orders? A backup will automatically be created.');" class="m-0">
                <input type="hidden" name="scope" value="all">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors font-medium">
                    Clear All Orders
                </button>
            </form>

            <a href="dashboard.php" class="px-4 py-2 border rounded hover:bg-gray-50 bg-white transition-colors">Back</a>
            <a href="logout.php" class="px-4 py-2 border rounded text-red-600 hover:bg-red-50 bg-white transition-colors">Logout</a>
        </div>
    </div>

    <?php if($msg): ?>
      <div class="mb-4 p-3 rounded bg-green-50 border text-sm text-green-800 font-medium">
        <?php 
            // Make the messages a bit more readable
            if($msg === 'cleared') echo 'All orders have been cleared and backed up.';
            elseif($msg === 'restored') echo 'Orders successfully restored from backup.';
            elseif($msg === 'restore_failed') echo 'Failed to restore orders.';
            elseif($msg === 'invalid') echo 'Invalid request.';
            elseif($msg === 'notfound') echo 'Backup file not found.';
            else echo htmlspecialchars($msg); 
        ?>
      </div>
    <?php endif; ?>

    <div id="ordersGrid">
        <?php include __DIR__ . '/orders_fragment.php'; ?>
    </div>

    <div class="mt-6 bg-white p-4 rounded shadow">
      <h3 class="font-bold mb-2">Backups</h3>
      <p class="text-sm text-gray-600 mb-3">Backups are created automatically before clearing or restoring orders.</p>
      <div class="flex gap-2 flex-wrap">
        <?php
          $backupDir = __DIR__ . '/backups';
          if(is_dir($backupDir)){
              $files = scandir($backupDir, SCANDIR_SORT_DESCENDING);
              foreach($files as $f){
                  if(in_array($f, ['.','..'])) continue;
                  if(pathinfo($f, PATHINFO_EXTENSION) !== 'json') continue;
                  $safe = htmlspecialchars($f);
                  echo '<div class="border p-2 rounded bg-gray-50">';
                  echo '<div class="text-xs mb-2">' . $safe . '</div>';
                  echo '<div class="flex gap-2">';
                  // restore (Fixed string concatenation for $safe variable)
                  echo '<form method="POST" action="restore_orders.php" onsubmit="return confirm(\'Restore backup ' . $safe . '? Current orders will be backed up.\');" style="display:inline">';
                  echo '<input type="hidden" name="file" value="' . $safe . '">';
                  echo '<button class="text-xs px-2 py-1 border rounded bg-white hover:bg-gray-100">Restore</button>';
                  echo '</form>';
                  // download
                  echo '<a href="backups/' . rawurlencode($f) . '" class="text-xs px-2 py-1 border rounded bg-white hover:bg-gray-100" download>Download</a>';
                  // delete (Fixed string concatenation for $safe variable)
                  echo '<form method="POST" action="delete_backup.php" onsubmit="return confirm(\'Delete backup ' . $safe . '?\');" style="display:inline">';
                  echo '<input type="hidden" name="file" value="' . $safe . '">';
                  echo '<button class="text-xs px-2 py-1 border rounded text-red-600 hover:bg-red-50">Delete</button>';
                  echo '</form>';
                  echo '</div></div>';
              }
          } else {
              echo '<p class="text-sm text-gray-500">No backups found.</p>';
          }
        ?>
      </div>
    </div>

</div>

<script>
// poll the orders fragment every 5s and update if changed
(function(){
  let last = '';
  async function poll(){
    try{
      const res = await fetch('orders_fragment.php', {cache:'no-store'});
      if(!res.ok) return;
      const html = await res.text();
      if(html !== last){ last = html; document.getElementById('ordersGrid').innerHTML = html; }
    }catch(e){/*ignore*/}
  }
  setInterval(poll, 5000);
})();
</script>
</body>
</html>