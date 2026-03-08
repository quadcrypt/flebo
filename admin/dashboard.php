<?php
session_start();
if(!isset($_SESSION["admin"])){
  header("Location: login.php");
  exit;
}

$products = [];
if(file_exists(__DIR__ . "/../products.json")){
  $decoded = json_decode(file_get_contents(__DIR__ . "/../products.json"), true);
  if(is_array($decoded)) $products = $decoded;
}
// Load orders
$orders = [];
$ordersFile = __DIR__ . "/../orders.json";
if(file_exists($ordersFile)){
  $od = json_decode(file_get_contents($ordersFile), true);
  if(is_array($od)) $orders = $od;
}

// compute unattended count for dashboard badge
$unattendedCount = 0;
foreach($orders as $o){
  if(!isset($o['attended']) || !$o['attended']) $unattendedCount++;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Dashboard</title>
<link rel="icon" type="image/png" href="iso.png">

<link rel="apple-touch-icon" href="iso.png">

<link rel="shortcut icon" href="iso.png" type="image/x-icon">
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">

<div class="flex justify-between mb-6 items-center">
  <h1 class="text-2xl font-bold">Admin Dashboard</h1>
  <div class="flex items-center gap-3">
    <a href="orders.php" class="relative bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
      Orders
      <?php if($unattendedCount>0): ?>
        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full"><?= $unattendedCount ?></span>
      <?php endif; ?>
    </a>
    <button onclick="openUsersModal()" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">Manage Users</button>
    <a href="logout.php" class="text-red-600">Logout</a>
  </div>
</div>

<!-- ADD PRODUCT FORM -->
<div class="bg-white p-6 rounded shadow mb-8">
<h2 class="font-bold mb-4">Add Product</h2>

<form action="save_product.php" method="POST" enctype="multipart/form-data" class="grid gap-4">

<input name="name" required placeholder="Product Name" class="border p-2 rounded">
<!-- 
<input name="price" required placeholder="Price" class="border p-2 rounded">
 -->

<input type="number" name="price" min="0" step="1" required 
class="border p-2 rounded" placeholder="Price">


<select name="category" required class="border p-2 rounded">
  <option value="">Select Category</option>
  <option value="Grains">Grains</option>
  <option value="Oils">Oils</option>
  <option value="Beverages">Beverages</option>
  <option value="Spices">Spices</option>
  <option value="Snacks">Snacks</option>
  <option value="Frozen Foods">Frozen Foods</option>
  <option value="Cleaning Supplies">Cleaning Supplies</option>
  <option value="Dairy">Dairy</option>
  <option value="Canned Foods">Canned Foods</option>
</select>

<input type="file" name="image" required class="border p-2 rounded">

<button class="bg-green-600 text-white p-2 rounded">
Add Product
</button>

</form>
</div>

<!-- PRODUCT LIST -->
<div class="bg-white p-6 rounded shadow">
<h2 class="font-bold mb-4">All Products</h2>

<?php if(!empty($products) && is_array($products)): ?>
  <?php foreach($products as $i => $p): ?>
  <div class="flex items-center justify-between border-b py-3">

  <div class="flex items-center gap-4">
  <img src="<?= htmlspecialchars($p['image'] ?? 'images/placeholder.png') ?>" class="w-16 h-16 object-cover rounded" alt="product image">
  <div>
  <p class="font-bold"><?= htmlspecialchars($p['name'] ?? '') ?></p>
  <p>₦<?= htmlspecialchars($p['price'] ?? '') ?> | <?= htmlspecialchars($p['category'] ?? '') ?></p>
  </div>
  </div>

  <a href="delete_product.php?id=<?= urlencode($p['id'] ?? $i) ?>" class="text-red-500">Delete</a>

  </div>
  <?php endforeach; ?>
<?php else: ?>
  <p class="text-sm text-gray-500">No products found.</p>
<?php endif; ?>

</div>


<!-- Users Management Modal -->
<div id="usersModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center p-4 z-50">
  <div class="bg-white w-full max-w-4xl rounded-xl p-6 max-h-[90vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-bold text-xl">Manage Users</h3>
      <button onclick="closeUsersModal()" class="text-gray-500 text-2xl">&times;</button>
    </div>
    <div id="usersContainer" class="space-y-3">
      <p class="text-gray-500 text-center py-8">Loading users...</p>
    </div>
  </div>
</div>

<script>
async function openUsersModal(){
  document.getElementById('usersModal').classList.remove('hidden');
  await loadUsers();
}

function closeUsersModal(){
  document.getElementById('usersModal').classList.add('hidden');
}

async function loadUsers(){
  try {
    const response = await fetch('../list_users.php');
    const users = await response.json();
    const container = document.getElementById('usersContainer');
    
    if(!Array.isArray(users) || users.length === 0){
      container.innerHTML = '<p class="text-gray-500 text-center py-8">No users found.</p>';
      return;
    }
    
    let html = '';
    users.forEach(u => {
      html += `
        <div class="flex items-center justify-between border rounded p-4 hover:bg-gray-50">
          <div class="flex-1">
            <p class="font-bold">${escapeHtml(u.name || 'Unknown')}</p>
            <p class="text-sm text-gray-600">
              Username: <span class="font-mono">${escapeHtml(u.username || '-')}</span>
              | Email: ${escapeHtml(u.email || '-')}
              | Phone: ${escapeHtml(u.phone || '-')}
            </p>
            <p class="text-xs text-gray-500">ID: ${escapeHtml(u.id || '-')}</p>
          </div>
          <button onclick="deleteUser('${escapeAttr(u.id)}')" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Delete</button>
        </div>
      `;
    });
    container.innerHTML = html;
  } catch(err){
    document.getElementById('usersContainer').innerHTML = `<p class="text-red-500">Error loading users: ${err.message}</p>`;
  }
}

async function deleteUser(userId){
  if(!confirm('Are you sure you want to delete this user? This action cannot be undone.')){
    return;
  }
  
  try {
    const response = await fetch('delete_user.php', {
      method: 'POST',
      body: new URLSearchParams({id: userId})
    });
    const result = await response.json();
    
    if(result.ok){
      alert('User deleted successfully');
      await loadUsers();
    } else {
      alert('Error deleting user: ' + (result.error || 'Unknown error'));
    }
  } catch(err){
    alert('Error: ' + err.message);
  }
}

function escapeHtml(text){
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

function escapeAttr(text){
  return text.replace(/'/g, '&#39;').replace(/"/g, '&quot;');
}
</script>

</body>
</html>