<?php
$products = [];
if(file_exists("products.json")){
    $products = json_decode(file_get_contents("products.json"), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FLEBO</title>
    <link rel="icon" type="image/png" href="iso.png">

    <link rel="apple-touch-icon" href="iso.png">

    <link rel="shortcut icon" href="iso.png" type="image/x-icon">

    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .slide { position:absolute; width:100%; height:100%; background-size:cover; background-position:center; transition:opacity 1s ease-in-out; }
        .badge-animate { animation:bounce .4s ease; }
        @keyframes bounce { 0%, 100% {transform:scale(1)} 50% {transform:scale(1.4)} }
        .toast { position:fixed; bottom:20px; right:20px; background:#16a34a; color:white; padding:14px 20px; border-radius:8px; box-shadow:0 10px 25px rgba(0,0,0,.2); opacity:0; transform:translateY(20px); transition:all .4s ease; z-index:999; pointer-events:none; }
        .toast.show { opacity:1; transform:translateY(0); }
    </style>
</head>
<body class="bg-gray-100">

<div id="successBanner" class="fixed top-0 left-0 w-full bg-green-600 text-white text-center p-4 hidden z-[100] font-bold shadow-lg">
    Order placed successfully! Check your email for details.
</div>

<nav id="storeNav" class="bg-white shadow-lg fixed w-full z-50 hidden">
    <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-green-600">FLEBO</h1>
        <div class="hidden md:flex items-center gap-3">
            <input type="text" id="searchInput" placeholder="Search groceries..." class="border p-2 w-64 rounded">
            <select id="categoryFilter" class="border p-2 rounded">
                <option value="">All Categories</option>
            </select>
            <div class="relative ml-3">
                <div id="userAvatar" class="w-10 h-10 rounded-full bg-green-600 text-white flex items-center justify-center font-bold cursor-pointer" title="Profile" onclick="toggleAvatarMenu()" style="display:none">A</div>
                <div id="avatarMenu" class="absolute right-0 mt-2 w-48 bg-white rounded shadow-lg hidden z-50">
                    <button onclick="openProfile('edit')" class="w-full text-left px-4 py-2 hover:bg-gray-100">Edit Profile</button>
                    <button onclick="logoutProfile()" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600">Logout</button>
                </div>
            </div>
        </div>
        <button onclick="openCart()" class="relative bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">
            🛒 <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white text-xs px-2 py-1 rounded-full">0</span>
        </button>
    </div>
</nav>

<div id="storeContent" class="hidden">

<section class="pt-20">
    <div class="relative h-[300px] md:h-[450px] overflow-hidden">
        <div class="slide opacity-100" style="background-image:url('');"></div>
        <div class="slide opacity-100" style="background-image:url('https://images.unsplash.com/photo-1601597111158-2fceff292cdc');"></div>
        <div class="slide opacity-0" style="background-image:url('https://images.unsplash.com/photo-1586201375761-83865001e31c');"></div>
        <div class="slide opacity-0" style="background-image:url('https://images.unsplash.com/photo-1603048297172-c92544798d5a');"></div>
        <div class="absolute inset-0 bg-black/40 flex items-center justify-center text-center text-white">
            <div>
                <h2 class="text-3xl md:text-5xl font-bold mb-2">Wholesale Groceries Made Easy</h2>
                <p class="text-lg">Bulk buying at unbeatable prices</p>
            </div>
        </div>
    </div>
</section>

<section class="max-w-7xl mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold mb-8 text-center">Available Products</h2>
    <div id="productContainer" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"></div>
</section>

</div>

<div id="loginModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center p-4 z-[70]">
    <div class="bg-white w-full max-w-md rounded-xl p-6 relative">
        <h3 id="loginHeading" class="font-bold text-xl mb-4">Login</h3>
        <form id="loginForm" onsubmit="handleLogin(event)">
            <input type="text" id="loginUsername" placeholder="Username" class="border w-full p-3 mb-3 rounded" required>
            <input type="password" id="loginPassword" placeholder="Password" class="border w-full p-3 mb-3 rounded" required>
            <button type="submit" class="bg-green-600 text-white w-full p-3 rounded font-bold hover:bg-green-700">Login</button>
        </form>
        <p id="loginInfo" class="mt-4 text-sm text-gray-600 text-center">Don't have an account? <button type="button" onclick="switchToSignup()" class="text-green-600 font-bold hover:underline">Create one</button></p>
    </div>
</div>

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
            <h3 class="font-bold text-xl mb-4 border-b pb-2">Delivery Details</h3>
            <form id="orderForm" onsubmit="handleCheckout(event)">
                <input type="hidden" name="cart" id="cartData">
                <input type="hidden" name="user_id" id="userId">
                <input type="email" required name="email" id="email" placeholder="Email Address" class="border w-full p-3 mb-3 rounded focus:ring-2 focus:ring-green-500 outline-none">
                <input type="text" required name="name" placeholder="Full Name" class="border w-full p-3 mb-3 rounded focus:ring-2 focus:ring-green-500 outline-none">
                <textarea required name="address" placeholder="Delivery Address" class="border w-full p-3 mb-3 rounded h-24 focus:ring-2 focus:ring-green-500 outline-none"></textarea>
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

<div id="profileModal" class="fixed inset-0 bg-black/60 hidden flex items-center justify-center p-4 z-[70]">
    <div class="bg-white w-full max-w-md rounded-xl p-6 relative">
        <button onclick="closeProfile()" class="absolute top-3 right-3 text-gray-500">✕</button>
        <h3 id="profileHeading" class="font-bold text-xl mb-4">Create your profile</h3>
        <form id="profileForm" onsubmit="saveProfile(event)">
            <input type="hidden" id="profileId" name="id">
            <input name="name" id="profileName" placeholder="Full name" class="border w-full p-3 mb-3 rounded" required>
            <input name="username" id="profileUsername" placeholder="Username" class="border w-full p-3 mb-3 rounded" required>
            <input name="password" type="password" id="profilePassword" placeholder="Password" class="border w-full p-3 mb-3 rounded" required>
            <input name="email" id="profileEmail" placeholder="Email (optional)" class="border w-full p-3 mb-3 rounded">
            <input name="phone" id="profilePhone" placeholder="Phone (optional)" class="border w-full p-3 mb-3 rounded">
            <textarea name="address" id="profileAddress" placeholder="Address (optional)" class="border w-full p-3 mb-3 rounded h-24"></textarea>
            <div class="flex gap-2">
                <button id="profileSaveBtn" class="bg-green-600 text-white px-4 py-2 rounded">Save Profile</button>
            </div>
        </form>
        <div id="profileInfoText" class="mt-4 text-sm text-gray-500">Create a profile to place orders.</div>
    </div>
</div>

<div id="toast" class="toast">Added to cart</div>

<div id="storeFooter" class="hidden">
<footer class="bg-slate-900 text-white pt-20 pb-10 border-t-8 border-green-600 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none" style="background: radial-gradient(circle at top right, #059669, transparent 40%), radial-gradient(circle at bottom left, #d97706, transparent 40%);"></div>
    
    <div class="max-w-7xl mx-auto px-6 relative z-10">
        <div class="grid md:grid-cols-4 gap-12 mb-16">
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center space-x-2 mb-6">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg">F</div>
                    <h2 class="text-2xl font-black tracking-tighter">FLEBO<span class="text-amber-500">GLOBAL</span></h2>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed mb-6">Your trusted wholesale grocery partner. We bring purity, quality, and affordability directly from the farm to your business.</p>
                <div class="flex space-x-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-blue-600 hover:text-white transition-all shadow-lg"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-green-500 hover:text-white transition-all shadow-lg"><i class="fab fa-whatsapp"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-pink-600 hover:text-white transition-all shadow-lg"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-slate-300 hover:bg-blue-400 hover:text-white transition-all shadow-lg"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="text-lg font-bold mb-6 text-white border-b-2 border-green-600 inline-block pb-1">Quick Links</h4>
                <ul class="space-y-3 text-sm">
                    <li><a href="#" class="text-slate-400 hover:text-amber-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-green-500"></i> Wholesale Grains</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-amber-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-green-500"></i> Premium Oils</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-amber-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-green-500"></i> Delivery Policy</a></li>
                    <li><a href="#" class="text-slate-400 hover:text-amber-500 transition-colors flex items-center gap-2"><i class="fas fa-chevron-right text-[10px] text-green-500"></i> Contact Support</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-lg font-bold mb-6 text-white border-b-2 border-amber-500 inline-block pb-1">Company</h4>
                <p class="text-slate-400 text-sm mb-4 leading-relaxed">Discover our heritage, mission, and the team driving food security across Nigeria.</p>
                <a href="about.php" class="inline-flex items-center justify-center bg-gradient-to-r from-green-600 to-green-700 text-white px-6 py-3 rounded-full font-bold text-sm shadow-lg shadow-green-900/50 hover:from-amber-500 hover:to-amber-600 hover:shadow-amber-900/50 transition-all transform hover:-translate-y-1">
                    Discover Who We Are <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>

            <div>
                <h4 class="text-lg font-bold mb-6 text-white border-b-2 border-blue-600 inline-block pb-1">Get In Touch</h4>
                <ul class="space-y-4 text-sm text-slate-400">
                    <li class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-amber-500 mt-1"></i>
                        <span>Rumuepirikom / Iwofe<br>Rivers State, Nigeria</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-phone-alt text-green-500"></i>
                        <span>+234 800 000 0000</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-envelope text-blue-400"></i>
                        <span>youremail@gmail.com</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <i class="fas fa-clock text-pink-500"></i>
                        <span>Mon - Sat: 8:00 AM - 6:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-slate-500 uppercase tracking-widest font-semibold">© <?php echo date("Y"); ?> Flebo Global Limited. All Rights Reserved.</p>
            <a href="/admin/login.php"><div class="h-1 w-32 rounded-full" style="background: linear-gradient(90deg, #059669, #d97706, #1e3a8a);"></div></a>
        </div>
    </div>
</footer>
</div>

<script>
window.products = <?php echo json_encode($products); ?>;
let lastProductsJSON = JSON.stringify(window.products || []);
let cart = JSON.parse(localStorage.getItem("cart")) || [];

// Migrate legacy cart entries that used numeric index -> convert to id-based entries
if(Array.isArray(cart) && cart.length > 0 && window.products){
    let migrated = false;
    cart = cart.map(item => {
        if(item.index !== undefined){
            const idx = parseInt(item.index, 10);
            const prod = window.products[idx];
            if(prod){
                migrated = true;
                return { id: prod.id || null, name: prod.name, price: prod.price, image: prod.image, qty: item.qty || 1 };
            }
        }
        return item;
    });
    if(migrated) localStorage.setItem('cart', JSON.stringify(cart));
}

// Polling to refresh products (safer than SSE for broad hosting)
function pollProducts(){
    fetch('products_api.php')
        .then(r=>r.json())
        .then(data=>{
            const j = JSON.stringify(data || []);
            if(j !== lastProductsJSON){
                lastProductsJSON = j;
                window.products = data || [];
                try{ renderProducts(); renderCart(); updateBadge(); populateCategories(); }catch(e){console.error(e);}                
            }
        }).catch(err=>{ /* silent */ });
}
// start polling every 5 seconds
setInterval(pollProducts, 5000);
// initial poll to ensure IDs are present
pollProducts();

// HERO SLIDER
let slides=document.querySelectorAll(".slide"), current=0;
setInterval(()=>{
    slides[current].classList.add("opacity-0");
    current=(current+1)%slides.length;
    slides[current].classList.remove("opacity-0");
},4000);

// RENDER PRODUCTS
function renderProducts(){
    let container=document.getElementById("productContainer");
    container.innerHTML="";
    if(!window.products || window.products.length === 0) return;

    // apply category filter if present
    const selectedCategory = document.getElementById('categoryFilter') ? document.getElementById('categoryFilter').value : '';
    window.products.forEach((p)=>{
        if(selectedCategory && String(p.category || '').toLowerCase() !== String(selectedCategory).toLowerCase()) return;
        const inCart = cart.some(item => item.id === p.id);
        const safeId = encodeURIComponent(p.id);
        container.innerHTML+=`
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

// CART ACTIONS
function addToCart(id){
    const decodedId = decodeURIComponent(id);
    const prod = (window.products || []).find(x => String(x.id) === String(decodedId));
    if(!prod) return showToast('Product not found');
    let qtyEl = document.getElementById("qty-"+encodeURIComponent(prod.id));
    let qty = qtyEl ? (parseInt(qtyEl.value) || 1) : 1;
    let existing = cart.find(item => String(item.id) === String(prod.id));
    if(existing) existing.qty += qty; else cart.push({ id: prod.id, name: prod.name, price: prod.price, image: prod.image, qty: qty });
    saveCart();
    renderProducts();
    showToast("Added to cart!");
}

function saveCart(){
    localStorage.setItem("cart", JSON.stringify(cart));
    updateBadge();
}

function updateBadge(){
    // show number of distinct products in cart
    let count = cart.length;
    let el = document.getElementById("cartCount");
    el.innerText = count;
    el.classList.add("badge-animate");
    setTimeout(()=>el.classList.remove("badge-animate"), 400);
}

function removeItem(i){
    cart.splice(i,1);
    saveCart();
    renderCart();
    renderProducts();
}

// MODAL LOGIC
function openCart(){
    renderCart();
    populateOrderFormFromProfile();
    document.getElementById("cartModal").classList.remove("hidden");
}
function closeCart(){ document.getElementById("cartModal").classList.add("hidden"); }

function renderCart(){
    let html="", total=0;
    let hasUnavailable = false;
    cart.forEach((item, i)=>{
        // find current product by id
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

    // warnings area
    const warn = document.getElementById('cartWarnings');
    if(hasUnavailable){
        warn.innerHTML = `<div class="bg-yellow-50 border border-yellow-200 p-3 rounded text-sm">
            Some items in your cart are no longer available. Remove them to proceed.
            <div class="mt-2"><button onclick="removeUnavailableItems()" class="bg-red-500 text-white px-3 py-1 rounded text-xs">Remove unavailable items</button></div>
        </div>`;
        // disable checkout
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

// AJAX CHECKOUT
function handleCheckout(e){
    e.preventDefault();
    if(cart.length === 0) return alert("Your cart is empty!");

    // basic email format validation
    const email = document.getElementById('email')?.value || '';
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailRe.test(email)){
        return alert('Please enter a valid email address.');
    }

    // Force sync cart data just before sending
    document.getElementById("cartData").value = JSON.stringify(cart);

    const btn = document.getElementById("submitBtn");
    const formData = new FormData(document.getElementById("orderForm"));
    
    btn.innerText = "Processing...";
    btn.disabled = true;

    fetch('send.php', { method: 'POST', body: formData })
    .then(r => r.text())
    .then(data => {
        if(data.trim() === "success"){
            cart = [];
            saveCart();
            renderProducts();
            closeCart();
            document.getElementById("orderForm").reset();
            document.getElementById("successBanner").classList.remove("hidden");
            setTimeout(()=>document.getElementById("successBanner").classList.add("hidden"), 6000);
        } else {
            // Show the exact error returned from PHP
            alert("System Message:\n" + data);
        }
    })
    .catch(err => {
        alert("Network error occurred. Check your connection.");
    })
    .finally(()=> {
        btn.innerText = "Place Order Now";
        btn.disabled = false;
    });
}

function showToast(msg){
    let t=document.getElementById("toast");
    t.innerText=msg; t.classList.add("show");
    setTimeout(()=>t.classList.remove("show"), 2000);
}

// --- User profile / onboarding client logic ---
function openProfile(){
    closeAvatarMenu();
    document.getElementById('profileModal').classList.remove('hidden');
    const selected = getSelectedUser();
    if (selected) {
        document.getElementById('profileId').value = selected.id || '';
        document.getElementById('profileName').value = selected.name || '';
        document.getElementById('profileUsername').value = selected.username || '';
        document.getElementById('profilePassword').value = '';
        document.getElementById('profileEmail').value = selected.email || '';
        document.getElementById('profilePhone').value = selected.phone || '';
        document.getElementById('profileAddress').value = selected.address || '';
    } else {
        document.getElementById('profileId').value = '';
        document.getElementById('profileName').value = '';
        document.getElementById('profileUsername').value = '';
        document.getElementById('profilePassword').value = '';
        document.getElementById('profileEmail').value = '';
        document.getElementById('profilePhone').value = '';
        document.getElementById('profileAddress').value = '';
    }
    document.getElementById('profileModal').classList.remove('hidden');
    // update info text
    updateProfileInfoText();
    // adjust order form requirements based on profile
    populateOrderFormFromProfile();
}
function closeProfile(){ document.getElementById('profileModal').classList.add('hidden'); }

function toggleAvatarMenu(){
    const menu = document.getElementById('avatarMenu');
    if(menu.classList.contains('hidden')){
        menu.classList.remove('hidden');
    } else {
        menu.classList.add('hidden');
    }
}

function closeAvatarMenu(){
    document.getElementById('avatarMenu').classList.add('hidden');
}

function saveProfile(e){
    e.preventDefault();
    const form = document.getElementById('profileForm');
    const data = new FormData(form);
    fetch('save_user.php', { method:'POST', body:data }).then(r=>r.json()).then(js=>{
        if(js.ok){
            // DO NOT auto-select - store username for login
            const username = data.get('username');
            localStorage.setItem('just_created_username', username);
            closeProfile();
            showToast('Account created! Now log in.');
            // show login modal and switch to login
            switchToLogin();
        } else if(js.error === 'email_exists'){
            alert('An account with this email already exists');
        } else if(js.error === 'phone_exists'){
            alert('An account with this phone number already exists');
        } else {
            alert('Unable to save profile: ' + (js.error || 'unknown error'));
        }
    }).catch(err=>{ alert('Network error saving profile'); });
}

function logoutProfile(){
    // remove selected user (logout) but keep saved users list
    localStorage.removeItem('ozone_selected_user');
    // hide avatar
    const avatar = document.getElementById('userAvatar'); if(avatar) avatar.style.display = 'none';
    // close menu and modal
    closeAvatarMenu();
    const modal = document.getElementById('profileModal'); if(modal) modal.classList.add('hidden');
    // clear hidden user id used for orders
    const uid = document.getElementById('userId'); if(uid) uid.value = '';
    // restore order form requirements
    populateOrderFormFromProfile();
    // refresh info text
    updateProfileInfoText();
    // hide store and show login
    hideStoreUI();
    const loginModal = document.getElementById('loginModal');
    loginModal.classList.remove('hidden');
    loginModal.classList.add('flex');
}

function updateProfileInfoText(){
    const u = getSelectedUser();
    const info = document.getElementById('profileInfoText');
    const heading = document.getElementById('profileHeading');
    if(u){
        if(heading) heading.innerText = 'Your profile';
        if(info) info.innerText = 'Update your information below.';
    } else {
        if(heading) heading.innerText = 'Create your profile';
        if(info) info.innerText = 'Create a profile to place orders.';
    }
}

function switchToLogin(){
    const loginModal = document.getElementById('loginModal');
    loginModal.classList.remove('hidden');
    loginModal.classList.add('flex');
    document.getElementById('profileModal').classList.add('hidden');
    document.getElementById('loginUsername').value = localStorage.getItem('just_created_username') || '';
    document.getElementById('loginPassword').value = '';
    document.getElementById('loginHeading').innerText = 'Login';
    document.getElementById('loginInfo').innerHTML = 'Don\'t have an account? <button type="button" onclick="switchToSignup()" class="text-green-600 font-bold hover:underline">Create one</button>';
}

function switchToSignup(){
    document.getElementById('profileModal').classList.remove('hidden');
    const loginModal = document.getElementById('loginModal');
    loginModal.classList.add('hidden');
    loginModal.classList.remove('flex');
    localStorage.removeItem('just_created_username');
}

function handleLogin(e){
    e.preventDefault();
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    
    fetch('login.php', {
        method: 'POST',
        body: new URLSearchParams({username, password})
    }).then(r=>r.json()).then(js=>{
        if(js.ok){
            // login success - select this user
            localStorage.setItem('ozone_selected_user', js.id);
            let map = JSON.parse(localStorage.getItem('ozone_users')||'{}');
            map[js.id] = js.user;
            localStorage.setItem('ozone_users', JSON.stringify(map));
            localStorage.removeItem('just_created_username');
            // hide login and show store
            const loginModal = document.getElementById('loginModal');
            loginModal.classList.add('hidden');
            loginModal.classList.remove('flex');
            showStoreUI();
            showToast('Logged in successfully');
            updateProfileUI();
        } else {
            alert('Invalid username or password');
        }
    }).catch(err=>{ alert('Login error: ' + err.message); });
}

function showStoreUI(){
    document.getElementById('storeNav').classList.remove('hidden');
    document.getElementById('storeContent').classList.remove('hidden');
    document.getElementById('storeFooter').classList.remove('hidden');
    populateOrderFormFromProfile();
}

function hideStoreUI(){
    document.getElementById('storeNav').classList.add('hidden');
    document.getElementById('storeContent').classList.add('hidden');
    document.getElementById('storeFooter').classList.add('hidden');
}
    
function updateProfileUI(){
    const selId = localStorage.getItem('ozone_selected_user');
    const avatar = document.getElementById('userAvatar');
    if(!selId){ avatar.style.display='none'; document.getElementById('userId').value=''; return; }
    const map = JSON.parse(localStorage.getItem('ozone_users')||'{}');
    const u = map[selId];
    if(!u){
        // try to fetch from server
        fetch('list_users.php').then(r=>r.json()).then(list=>{
            const m = {};
            list.forEach(it=> m[it.id]=it);
            localStorage.setItem('ozone_users', JSON.stringify(m));
            updateProfileUI();
        });
        return;
    }
    const initial = (u.name || 'User').trim().charAt(0).toUpperCase();
    avatar.innerText = initial;
    avatar.style.display = 'flex';
    document.getElementById('userId').value = selId;
    // update modal info text / visit link
    updateProfileInfoText();
}

function getSelectedUser(){
    const selId = localStorage.getItem('ozone_selected_user');
    if(!selId) return null;
    const map = JSON.parse(localStorage.getItem('ozone_users')||'{}');
    return map[selId] || null;
}

// On first visit show login if no user logged in
document.addEventListener('DOMContentLoaded', ()=>{
    // hydrate local cache from server if empty
    if(!localStorage.getItem('ozone_users')){
        fetch('list_users.php').then(r=>r.json()).then(list=>{
            const m = {}; list.forEach(it=> m[it.id]=it); localStorage.setItem('ozone_users', JSON.stringify(m));
        }).catch(()=>{});
    }
    
    // check if user is logged in
    const selectedUserId = localStorage.getItem('ozone_selected_user');
    const loginModal = document.getElementById('loginModal');

    if(selectedUserId){
        // user is logged in - show store UI
        updateProfileUI();
        showStoreUI();
        loginModal.classList.add('hidden');
        loginModal.classList.remove('flex');
    } else {
        // user not logged in - show login modal
        loginModal.classList.remove('hidden');
        loginModal.classList.add('flex');
        hideStoreUI();
    }
});

function populateOrderFormFromProfile(){
    const u = getSelectedUser();
    const emailEl = document.getElementById('email');
    const nameEl = document.querySelector('input[name="name"]');
    const addrEl = document.querySelector('textarea[name="address"]');
    
    if(u){
        // User is logged in - fill in and make not required
        if(emailEl){ 
            emailEl.value = u.email || ''; 
            emailEl.required = false;
        }
        if(nameEl){ 
            nameEl.value = u.name || ''; 
            nameEl.required = false;
        }
        if(addrEl){ 
            addrEl.value = u.address || ''; 
            addrEl.required = false;
        }
    } else {
        // User not logged in - clear and require
        if(emailEl){ 
            emailEl.value = ''; 
            emailEl.required = true;
        }
        if(nameEl){ 
            nameEl.value = ''; 
            nameEl.required = true;
        }
        if(addrEl){ 
            addrEl.value = ''; 
            addrEl.required = true;
        }
    }
}

function populateCategories(){
    const sel = document.getElementById('categoryFilter');
    if(!sel || !window.products) return;
    const cats = new Set();
    window.products.forEach(p => { if(p.category) cats.add(p.category); });
    // clear except first "All Categories"
    sel.innerHTML = '<option value="">All Categories</option>';
    Array.from(cats).sort().forEach(c => {
        const o = document.createElement('option'); o.value = c; o.innerText = c; sel.appendChild(o);
    });
    sel.onchange = () => { renderProducts(); };
}

// SEARCH
document.getElementById("searchInput")?.addEventListener("keyup", function(){
    let val = this.value.toLowerCase();
    document.querySelectorAll(".product-card").forEach(c => {
        let name = c.querySelector("h3").innerText.toLowerCase();
        c.style.display = name.includes(val) ? "block" : "none";
    });
});

renderProducts();
updateBadge();
populateCategories();
</script>
</body>

</html>
