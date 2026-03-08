<?php
// user.php - Personal shopping page for a specific user
$id = $_GET['id'] ?? '';
$usersFile = __DIR__ . '/users.json';
$user = null;

if($id && file_exists($usersFile)){
    $users = json_decode(file_get_contents($usersFile), true) ?: [];
    if(isset($users[$id])) $user = $users[$id];
}

if(!$user){
    die('<!DOCTYPE html><html><head><title>User Not Found</title><script src="https://cdn.tailwindcss.com"></script></head><body class="bg-gray-100 p-8"><div class="max-w-3xl mx-auto bg-white p-6 rounded"><h1 class="text-2xl font-bold text-red-600">User not found</h1></div></body></html>');
}

$products = [];
if(file_exists("products.json")){
    $products = json_decode(file_get_contents("products.json"), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($user['name']) ?>'s Orders - FLEBO</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .toast { position:fixed; bottom:20px; right:20px; background:#16a34a; color:white; padding:14px 20px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,.2); opacity:0; transform:translateY(20px); transition:all .4s ease; z-index:999; pointer-events:none; }
        .toast.show { opacity:1; transform:translateY(0); }
    </style>
</head>
<body class="bg-gray-100">

<nav class="bg-white shadow-lg fixed w-full z-50">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-green-600">FLEBO</h1>
        <div class="text-center flex-1">
            <p class="text-sm font-bold">Welcome, <?= htmlspecialchars($user['name']) ?></p>
            <p class="text-xs text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        </div>
        <button onclick="openCart()" class="relative bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            🛒 <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">0</span>
        </button>
    </div>
</nav>

<main class="pt-20 max-w-7xl mx-auto px-4 py-12">
    <h2 class="text-3xl font-bold mb-8 text-center">Place Your Order</h2>
    <div id="productContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
</main>

<!-- Cart Modal -->
<div id="cartModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center p-4 z-[60]">
    <div class="bg-white w-full max-w-4xl rounded-xl p-6 grid md:grid-cols-2 gap-6 max-h-[90vh] overflow-y-auto relative">
        <button onclick="closeCart()" aria-label="Close cart" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800">✕</button>
        <div>
            <h3 class="font-bold text-xl mb-4 border-b pb-2">Your Cart</h3>
            <div id="cartItems" class="space-y-4"></div>
            <div id="cartWarnings" class="mt-4"></div>
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="font-bold text-lg flex justify-between">
                    <span>Total:</span>
                    <span class="text-green-600">₦<span id="cartTotal">0</span></span>
                </h4>
            </div>
        </div>

        <div>
            <h3 class="font-bold text-xl mb-4 border-b pb-2">Order Details</h3>
            <form id="orderForm" onsubmit="handleCheckout(event)">
                <input type="hidden" name="cart" id="cartData">
                <input type="hidden" name="user_id" id="userId" value="<?= htmlspecialchars($id) ?>">
                <input type="email" name="email" id="email" placeholder="Email Address" class="border w-full p-3 mb-3 rounded focus:ring-2 focus:ring-green-500 outline-none" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
                <input type="text" name="name" placeholder="Full Name" class="border w-full p-3 mb-3 rounded focus:ring-2 focus:ring-green-500 outline-none" value="<?= htmlspecialchars($user['name'] ?? '') ?>" readonly>
                <textarea name="address" placeholder="Delivery Address" class="border w-full p-3 mb-3 rounded h-24 focus:ring-2 focus:ring-green-500 outline-none" readonly><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                <select name="delivery" required class="border p-3 w-full mb-4 rounded focus:ring-2 focus:ring-green-500 outline-none">
                    <option value="">Select Delivery Option</option>
                    <option value="Door Delivery">Door Delivery</option>
                    <option value="Pickup at Shop">Pickup at Shop</option>
                </select>
                <button type="submit" id="submitBtn" class="bg-green-600 hover:bg-green-700 text-white w-full p-4 rounded-lg font-bold transition shadow-lg">
                    Place Order Now
                </button>
            </form>
            <button onclick="closeCart()" class="mt-4 text-gray-500 w-full text-sm hover:underline">Continue Shopping</button>
        </div>
    </div>
</div>

<div id="successBanner" class="fixed top-0 left-0 w-full bg-green-600 text-white text-center p-4 hidden z-[100] font-bold shadow-lg">
    Order placed successfully! Check your email for details.
</div>

<div id="toast" class="toast">Added to cart</div>

<footer class="bg-gray-900 text-gray-300 mt-16 py-12">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <p>© <?php echo date("Y"); ?> FLEBO Wholesale Store.</p>
    </div>
</footer>

<script>
window.products = <?php echo json_encode($products); ?>;
let cart = JSON.parse(localStorage.getItem("user_cart_<?= $id ?>")) || [];

function renderProducts(){
    let container = document.getElementById("productContainer");
    container.innerHTML = "";
    if(!window.products || window.products.length === 0) return;

    window.products.forEach((p) => {
        const inCart = cart.some(item => item.id === p.id);
        const safeId = encodeURIComponent(p.id);
        container.innerHTML += `
        <div class="bg-white p-4 rounded shadow transition relative product-card border border-transparent">
            <img src="${p.image}" class="h-40 w-full object-cover rounded">
            <h3 class="font-bold mt-2 text-gray-800">${p.name}</h3>
            <p class="text-green-600 font-bold">₦${Number(p.price).toLocaleString()}</p>
            <div class="flex items-center gap-0 mt-2">
                <button onclick="decreaseQty('${safeId}')" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-l">-</button>
                <input type="text" readonly value="1" id="qty-${safeId}" class="w-16 text-center border-t border-b">
                <button onclick="increaseQty('${safeId}')" class="px-3 py-1 bg-gray-200 text-gray-800 rounded-r">+</button>
                <button onclick="addToCart('${safeId}')" class="bg-green-600 hover:bg-green-700 text-white ml-2 p-2 rounded font-medium transition">Add</button>
            </div>
            <span id="badge-${safeId}" class="absolute top-2 right-2 bg-blue-600 text-white text-[10px] px-2 py-1 rounded font-bold shadow ${inCart ? '' : 'hidden'}">IN CART</span>
        </div>`;
    });
}

function increaseQty(id){
    const el = document.getElementById('qty-' + id);
    if(!el) return; let v = parseInt(el.value) || 1; el.value = v + 1;
}

function decreaseQty(id){
    const el = document.getElementById('qty-' + id);
    if(!el) return; let v = parseInt(el.value) || 1; if(v>1) el.value = v - 1;
}

function addToCart(id){
    const decodedId = decodeURIComponent(id);
    const prod = (window.products || []).find(x => String(x.id) === String(decodedId));
    if(!prod) return showToast('Product not found');
    let qtyEl = document.getElementById("qty-" + encodeURIComponent(prod.id));
    let qty = qtyEl ? (parseInt(qtyEl.value) || 1) : 1;
    let existing = cart.find(item => String(item.id) === String(prod.id));
    if(existing) existing.qty += qty; else cart.push({ id: prod.id, name: prod.name, price: prod.price, image: prod.image, qty: qty });
    saveCart();
    renderProducts();
    showToast("Added to cart!");
}

function saveCart(){
    localStorage.setItem("user_cart_<?= $id ?>", JSON.stringify(cart));
    updateBadge();
}

function updateBadge(){
    let count = cart.length;
    let el = document.getElementById("cartCount");
    el.innerText = count;
}

function removeItem(i){
    cart.splice(i, 1);
    saveCart();
    renderCart();
    renderProducts();
}

function openCart(){
    renderCart();
    document.getElementById("cartModal").classList.remove("hidden");
}

function closeCart(){ 
    document.getElementById("cartModal").classList.add("hidden"); 
}

function renderCart(){
    let html = "", total = 0;
    let hasUnavailable = false;
    
    cart.forEach((item, i) => {
        const prod = (window.products || []).find(p => String(p.id) === String(item.id));
        let sub = item.price * item.qty;
        total += sub;
        
        if(!prod){
            hasUnavailable = true;
            html += `<div class="flex items-center gap-3 border-b pb-3">
                <div class="w-12 h-12 flex items-center justify-center bg-gray-100 rounded text-xs text-gray-500">Removed</div>
                <div class="flex-1">
                    <p class="font-bold text-sm">${item.name} <span class="text-red-500 text-xs">(no longer available)</span></p>
                    <p class="text-xs text-gray-500">₦${Number(item.price).toLocaleString()} x ${item.qty}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-sm">₦${Number(sub).toLocaleString()}</p>
                    <button onclick="removeItem(${i})" class="text-red-500 text-xs">Remove</button>
                </div>
            </div>`;
        } else {
            html += `<div class="flex items-center gap-3 border-b pb-3">
                <img src="${item.image}" class="w-12 h-12 object-cover rounded">
                <div class="flex-1">
                    <p class="font-bold text-sm">${item.name}</p>
                    <p class="text-xs text-gray-500">₦${Number(item.price).toLocaleString()} x ${item.qty}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-sm">₦${Number(sub).toLocaleString()}</p>
                    <button onclick="removeItem(${i})" class="text-red-500 text-xs">Remove</button>
                </div>
            </div>`;
        }
    });

    document.getElementById("cartItems").innerHTML = html || "<p class='text-center py-10 text-gray-400'>Cart is empty</p>";
    document.getElementById("cartTotal").innerText = total.toLocaleString();

    const warn = document.getElementById('cartWarnings');
    if(hasUnavailable){
        warn.innerHTML = `<div class="bg-yellow-50 border border-yellow-200 p-3 rounded text-sm">
            Some items are no longer available. Remove them to proceed.
            <div class="mt-2"><button onclick="removeUnavailableItems()" class="bg-red-500 text-white px-3 py-1 rounded text-xs">Remove unavailable items</button></div>
        </div>`;
        document.getElementById('submitBtn').disabled = true;
        document.getElementById('submitBtn').classList.add('opacity-60','cursor-not-allowed');
    } else {
        warn.innerHTML = '';
        document.getElementById('submitBtn').disabled = false;
        document.getElementById('submitBtn').classList.remove('opacity-60','cursor-not-allowed');
    }
}

function removeUnavailableItems(){
    cart = cart.filter(item => (window.products || []).some(p => String(p.id) === String(item.id)));
    saveCart();
    renderCart();
    renderProducts();
    showToast('Removed unavailable items');
}

function handleCheckout(e){
    e.preventDefault();
    if(cart.length === 0){
        alert('Your cart is empty');
        return;
    }
    
    const form = document.getElementById('orderForm');
    const formData = new FormData(form);
    formData.set('cart', JSON.stringify(cart));
    
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerText = "Processing...";
    
    fetch('send.php', { method: 'POST', body: formData })
        .then(data => {
            if(data.trim() === "success"){
                cart = [];
                saveCart();
                renderProducts();
                closeCart();
                document.getElementById("orderForm").reset();
                document.getElementById("successBanner").classList.remove("hidden");
                setTimeout(() => document.getElementById("successBanner").classList.add("hidden"), 6000);
            } else {
                alert("System Message:\n" + data);
            }
        })
        .catch(err => {
            alert("Network error occurred. Check your connection.");
        })
        .finally(() => {
            btn.innerText = "Place Order Now";
            btn.disabled = false;
        });
}

function showToast(msg){
    let t = document.getElementById("toast");
    t.innerText = msg;
    t.classList.add("show");
    setTimeout(() => t.classList.remove("show"), 2000);
}

// Initialize
renderProducts();
updateBadge();
</script>

</body>
</html>
