<?php
/**
 * FLEBO GLOBAL - OPTIMIZED & RESPONSIVE VERSION
 * Enhancements: Faster Load Times, Smooth Counter, Mobile-First UI.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- DYNAMIC PATH LOADER ---
$folderName = 'PHP-mailer'; 
$basePath = __DIR__ . '/' . $folderName;

if (file_exists($basePath . '/src/Exception.php')) {
    require $basePath . '/src/Exception.php';
    require $basePath . '/src/PHPMailer.php';
    require $basePath . '/src/SMTP.php';
} elseif (file_exists($basePath . '/Exception.php')) {
    require $basePath . '/Exception.php';
    require $basePath . '/PHPMailer.php';
    require $basePath . '/SMTP.php';
} else {
    die("<div style='background:#fee2e2; color:#991b1b; font-family:sans-serif; padding:20px; text-align:center; border-radius:10px;'>
            <h1 style='margin-bottom:10px;'>PHPMailer Missing</h1>
            <p>Ensure <strong>$basePath</strong> exists in your directory.</p>
         </div>");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$messageStatus = "";
$messageType = "";

if(isset($_POST['sendMessage'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'obuahsilas2021@gmail.com'; 
            $mail->Password   = 'dejexawnqzovdxui';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom('obuahsilas2021@gmail.com', 'Flebo Global');
            $mail->addAddress('obuahsilas2021@gmail.com', 'Flebo Global Contact');
            $mail->addReplyTo($email, $name);
            $mail->isHTML(true);
            $mail->Subject = 'New Message from ' . $name;
            $mail->Body    = "<h2>Inquiry Details</h2><p><strong>Name:</strong> $name</p><p><strong>Message:</strong><br>$message</p>";
            $mail->send();
            $messageStatus = "Message sent successfully!";
            $messageType = "success";
        } catch (Exception $e) {
            $messageStatus = "Error: " . $mail->ErrorInfo;
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Flebo Global Limited</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap');
        
        :root {
            --primary: #059669;
            --secondary: #d97706;
            --accent: #1e3a8a;
        }

        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        
        /* Optimization: Hide content before reveal */
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity 0.8s ease-out, transform 0.8s ease-out; }
        .reveal.active { opacity: 1; transform: translateY(0); }

        /* Color Extensions */
        .gradient-green { background: linear-gradient(135deg, #064e3b 0%, #059669 100%); }
        .gradient-text { background: linear-gradient(90deg, #059669, #d97706); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .glass { background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }

        /* Animation Speeds */
        @keyframes kenburns {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }
        .animate-kenburns { animation: kenburns 15s linear infinite alternate; }

        /* Back to top smooth show */
        #backToTop { opacity: 0; visibility: hidden; transition: 0.4s; }
        #backToTop.show { opacity: 1; visibility: visible; }

        /* Custom Scrollbar for better aesthetics */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }
    </style>
</head>
<body class="bg-[#fafafa] text-slate-900 overflow-x-hidden">

<button id="backToTop" class="fixed bottom-6 right-6 z-[99] bg-green-700 text-white p-4 rounded-full shadow-2xl hover:scale-110 active:scale-95 transition-all">
    <i class="fas fa-arrow-up"></i>
</button>

<nav class="fixed w-full z-[100] bg-white/90 backdrop-blur-md border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 md:px-6 py-4 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <div class="w-8 h-8 md:w-10 md:h-10 bg-green-700 rounded-lg flex items-center justify-center text-white font-bold text-lg md:text-xl">F</div>
            <h1 class="text-xl md:text-2xl font-extrabold tracking-tighter">FLEBO<span class="text-amber-600">GLOBAL</span></h1>
        </div>
        <div class="hidden md:flex space-x-8 font-semibold text-slate-600">
            <a href="index.php" class="hover:text-green-600 transition">Home</a>
            <a href="about.php" class="text-green-600 border-b-2 border-green-600">About</a>
        </div>
        <a href="#contact" class="bg-green-700 text-white px-4 md:px-6 py-2 rounded-full font-bold text-sm md:text-base hover:bg-amber-600 transition shadow-lg">Get Quote</a>
    </div>
</nav>

<section class="relative h-[85vh] md:h-screen flex items-center justify-center overflow-hidden bg-black">
    <div id="hero-slider" class="absolute inset-0">
        <div class="hero-slide absolute inset-0 transition-opacity duration-1000 opacity-100">
            <div class="absolute inset-0 bg-black/40 z-10"></div>
            <img src="images/fresh.jpg" class="w-full h-full object-cover animate-kenburns" loading="eager">
            <div class="absolute inset-0 z-20 flex flex-col items-center justify-center text-center px-6">
                <h2 class="text-white text-4xl md:text-8xl font-black mb-4 leading-tight">Purity In <span class="text-green-400">Nature</span></h2>
                <p class="text-gray-200 text-lg md:text-2xl max-w-2xl">From fertile Nigerian soils directly to your dining table.</p>
            </div>
        </div>
        </div>
</section>

<section id="stats-section" class="py-12 bg-white border-b relative z-30">
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div class="reveal">
            <p class="text-3xl md:text-5xl font-black text-green-700 mb-1"><span class="counter" data-target="15000">0</span>+</p>
            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Tons Processed</p>
        </div>
        <div class="reveal">
            <p class="text-3xl md:text-5xl font-black text-amber-600 mb-1"><span class="counter" data-target="200">0</span>+</p>
            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Partner Farmers</p>
        </div>
        <div class="reveal">
            <p class="text-3xl md:text-5xl font-black text-blue-800 mb-1"><span class="counter" data-target="36">0</span></p>
            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">States Covered</p>
        </div>
        <div class="reveal">
            <p class="text-3xl md:text-5xl font-black text-green-700 mb-1"><span class="counter" data-target="100">0</span>%</p>
            <p class="text-[10px] md:text-xs font-bold text-slate-400 uppercase tracking-widest">Safe & Organic</p>
        </div>
    </div>
</section>

<section class="py-20 md:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 md:gap-20 items-center">
        <div class="reveal">
            <span class="text-amber-600 font-bold uppercase tracking-widest text-sm mb-2 block">Our Heritage</span>
            <h2 class="text-3xl md:text-5xl font-black text-slate-900 mb-6 leading-tight">Feeding the Future with <br><span class="gradient-text">Integrity</span></h2>
            <p class="text-slate-600 text-base md:text-lg leading-relaxed mb-8">
                Flebo Global Limited was founded in 2020. We oversee the entire journey—from the rich Nigerian soil to our modern processing facilities in Rivers State, and finally to your doorstep.
            </p>
            <div class="bg-amber-50 p-6 md:p-8 rounded-3xl border-l-8 border-amber-500 shadow-sm">
                <p class="italic text-amber-900 text-base md:text-lg">"Quality isn't just a goal; it's our promise to every Nigerian household."</p>
                <p class="mt-4 font-bold text-slate-900">— Florence Oribhabor, MD</p>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 reveal">
            <img src="images/fresh.jpg" class="h-64 md:h-96 w-full object-cover rounded-[30px] md:rounded-[50px] shadow-xl" loading="lazy">
            <img src="images/farm.jpg" class="h-64 md:h-96 w-full object-cover rounded-[30px] md:rounded-[50px] shadow-xl mt-8 md:mt-16" loading="lazy">
        </div>
    </div>
</section>

<section class="py-20 bg-slate-900">
    <div class="max-w-7xl mx-auto px-6 mb-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <h2 class="text-white text-3xl md:text-4xl font-bold">Our <span class="text-green-500">Excellence Hub</span></h2>
        <div class="flex space-x-3">
            <button class="swiper-prev w-12 h-12 rounded-full border border-white/20 text-white hover:bg-green-600 transition flex items-center justify-center"><i class="fas fa-arrow-left"></i></button>
            <button class="swiper-next w-12 h-12 rounded-full border border-white/20 text-white hover:bg-green-600 transition flex items-center justify-center"><i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-6">
        <div class="swiper excellenceSwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="bg-white/5 p-8 rounded-[40px] glass text-white h-full border-b-4 border-green-600">
                        <div class="w-14 h-14 bg-green-600 rounded-2xl mb-6 flex items-center justify-center text-2xl font-black">01</div>
                        <h3 class="text-xl font-bold mb-3">Precision Milling</h3>
                        <p class="text-slate-400 text-sm md:text-base">Advanced cleaning and processing for premium grain purity.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bg-white/5 p-8 rounded-[40px] glass text-white h-full border-b-4 border-amber-600">
                        <div class="w-14 h-14 bg-amber-600 rounded-2xl mb-6 flex items-center justify-center text-2xl font-black">02</div>
                        <h3 class="text-xl font-bold mb-3">Fresh Supply Chain</h3>
                        <p class="text-slate-400 text-sm md:text-base">Optimized cold storage to maintain nutrient density from farm to warehouse.</p>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="bg-white/5 p-8 rounded-[40px] glass text-white h-full border-b-4 border-blue-600">
                        <div class="w-14 h-14 bg-blue-600 rounded-2xl mb-6 flex items-center justify-center text-2xl font-black">03</div>
                        <h3 class="text-xl font-bold mb-3">Smart Logistics</h3>
                        <p class="text-slate-400 text-sm md:text-base">Nationwide distribution network ensuring timely delivery across Nigeria.</p>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination !static mt-10"></div>
        </div>
    </div>
</section>

<section id="contact" class="py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12 reveal">
        <div class="bg-white p-8 md:p-12 rounded-[40px] shadow-sm border border-slate-100">
            <h3 class="text-2xl md:text-3xl font-black mb-8 text-blue-900 uppercase tracking-tighter">Get in <span class="text-green-600">Touch</span></h3>
            <form method="POST" class="space-y-4 md:space-y-6">
                <input type="text" name="name" placeholder="Full Name" required class="w-full bg-slate-50 px-6 py-4 rounded-2xl border-none focus:ring-2 focus:ring-green-500 outline-none transition-all">
                <input type="email" name="email" placeholder="Email Address" required class="w-full bg-slate-50 px-6 py-4 rounded-2xl border-none focus:ring-2 focus:ring-amber-500 outline-none transition-all">
                <textarea name="message" rows="4" placeholder="How can we help?" required class="w-full bg-slate-50 px-6 py-4 rounded-2xl border-none focus:ring-2 focus:ring-blue-500 outline-none transition-all"></textarea>
                <button type="submit" name="sendMessage" class="w-full bg-slate-900 text-white py-5 rounded-2xl font-black text-lg hover:bg-green-700 transition-all transform active:scale-95 shadow-xl">SEND MESSAGE</button>
            </form>
        </div>
        <div class="flex flex-col gap-6">
            <div class="rounded-[40px] overflow-hidden shadow-2xl h-64 md:h-80 border-4 border-white">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3975.875323223032!2d6.9458694!3d4.8211046!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x1069cedca27076d1%3A0xc383c276326466f!2sRumuepirikom%2C%20Port%20Harcourt!5e0!3m2!1sen!2sng!4v1700000000000!5m2!1sen!2sng" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-6 bg-amber-600 text-white rounded-3xl">
                    <p class="text-[10px] font-bold uppercase opacity-70 mb-1">Local HQ</p>
                    <p class="font-bold text-sm">Rumuepirikom/Iwofe, PH</p>
                </div>
                <div class="p-6 bg-blue-900 text-white rounded-3xl">
                    <p class="text-[10px] font-bold uppercase opacity-70 mb-1">Inquiries</p>
                    <p class="font-bold text-sm truncate">florenceoribhabor@gmail.com</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="bg-slate-900 text-white py-12 px-6 text-center">
    <h2 class="text-2xl font-black mb-4">FLEBO <span class="text-amber-500">GLOBAL</span></h2>
    <p class="text-slate-500 text-sm mb-6">Excellence in Food Processing & Distribution.</p>
    <div class="flex justify-center space-x-6 mb-8">
        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-whatsapp"></i></a>
        <a href="#" class="text-slate-400 hover:text-white transition"><i class="fab fa-instagram"></i></a>
    </div>
    <p class="text-[10px] text-slate-600 uppercase tracking-[0.2em]">© <?php echo date("Y"); ?> Flebo Global Ltd. All Rights Reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    // 1. Slow-Motion Counter Logic
    function animateCounter(el) {
        const target = +el.getAttribute('data-target');
        let count = 0;
        const speed = target > 1000 ? 50 : 20; // Adjusts based on size of number
        const inc = target / speed;

        const updateCount = () => {
            if (count < target) {
                count += inc;
                el.innerText = Math.ceil(count).toLocaleString();
                setTimeout(updateCount, 40); // 40ms delay creates the 'slower' feel
            } else {
                el.innerText = target.toLocaleString();
            }
        };
        updateCount();
    }

    // 2. Swiper Initialization
    const swiper = new Swiper('.excellenceSwiper', {
        slidesPerView: 1,
        spaceBetween: 20,
        autoplay: { delay: 4000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
        navigation: { nextEl: '.swiper-next', prevEl: '.swiper-prev' },
        breakpoints: {
            640: { slidesPerView: 1.5 },
            1024: { slidesPerView: 3 }
        }
    });

    // 3. Intersection Observer (Reveal + Counters)
    const observerOptions = { threshold: 0.2 };
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                if (entry.target.id === 'stats-section') {
                    document.querySelectorAll('.counter').forEach(c => animateCounter(c));
                }
            }
        });
    }, observerOptions);

    document.querySelectorAll('.reveal').forEach(r => observer.observe(r));
    observer.observe(document.getElementById('stats-section'));

    // 4. Back to Top Toggle
    const topBtn = document.getElementById("backToTop");
    window.onscroll = () => {
        if (window.scrollY > 400) topBtn.classList.add('show');
        else topBtn.classList.remove('show');
    };
    topBtn.onclick = () => window.scrollTo({ top: 0, behavior: 'smooth' });

    // 5. Hero Slider (Simple Fade)
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide');
    if(slides.length > 1) {
        setInterval(() => {
            slides[currentSlide].classList.replace('opacity-100', 'opacity-0');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.replace('opacity-0', 'opacity-100');
        }, 6000);
    }
</script>

</body>
</html>