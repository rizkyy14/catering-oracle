<?php
session_start();
include 'config/database.php';

// Ambil Data Menu dari Oracle
$query_menu = "SELECT * FROM menu_catering ORDER BY id_menu DESC";
$stmt_menu = oci_parse($conn, $query_menu);
oci_execute($stmt_menu);

// Logika logout sederhana jika ada request
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catering Rizky - Reimagining Culture Through Taste</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style> 
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
        .menu-slider::-webkit-scrollbar { display: none; } [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ loginOpen: false, regOpen: false, orderOpen: false, selectedMenu: '', selectedHarga: 0 }" class="bg-gray-50 overflow-x-hidden">

    <!-- NAVBAR -->
    <nav class="fixed w-full z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-orange-600">Catering<span class="text-gray-800">Rizky</span></h1>
            <div class="hidden md:flex space-x-8 font-medium">
                <a href="#home" class="hover:text-orange-500 transition">Home</a>
                <a href="#menu" class="hover:text-orange-500 transition">Menu</a>
                <a href="#about" class="hover:text-orange-500 transition">About</a>
                <a href="#contact" class="hover:text-orange-500 transition">Contact</a>
            </div>
            <div class="space-x-4">
                <?php if(isset($_SESSION['nama'])): ?>
                    <span class="text-gray-700 font-medium">Halo, <?= $_SESSION['nama'] ?></span>
                    <a href="?logout=1" class="text-red-500 text-sm font-bold">Logout</a>
                <?php else: ?>
                    <button @click="loginOpen = true" class="text-gray-700 hover:text-orange-600 font-semibold">Login</button>
                    <button @click="regOpen = true" class="bg-orange-600 text-white px-6 py-2 rounded-full hover:bg-orange-700 transition">Daftar</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- HERO -->
    <section id="home" class="h-screen flex items-center justify-center bg-orange-50 px-6">
        <div class="text-center" data-aos="zoom-out">
            <span class="bg-orange-100 text-orange-600 px-4 py-2 rounded-full text-sm font-bold mb-4 inline-block">Best Catering in Medan</span>
            <h2 class="text-5xl md:text-7xl font-bold text-gray-900 mb-6">Lezat, Sehat, <br> & <span class="text-orange-600">Terjangkau</span></h2>
            <p class="text-lg text-gray-600 mb-10 max-w-2xl mx-auto italic">"Reimagining Culture Through Digital Space & Taste."</p>
            <a href="#menu" class="bg-orange-600 text-white px-10 py-4 rounded-full text-lg font-bold shadow-xl hover:scale-105 transition-transform inline-block">Order Now</a>
        </div>
    </section>

    <!-- SECTION: ABOUT -->
    <section id="about" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12 items-center">
            <div data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1555244162-803834f70033" class="rounded-3xl shadow-2xl" alt="Chef">
            </div>
            <div data-aos="fade-left">
                <h3 class="text-4xl font-bold mb-6">Tentang <span class="text-orange-600">Kami</span></h3>
                <p class="text-gray-600 leading-relaxed mb-6">Catering Rizky berawal dari semangat mahasiswa Manajemen Informatika Politeknik Negeri Medan untuk mendigitalisasi layanan kuliner tradisional. Kami berkomitmen menyajikan hidangan dengan standar higienis tinggi tanpa menghilangkan cita rasa autentik.</p>
                <ul class="space-y-3 font-medium text-gray-700">
                    <li class="flex items-center">✅ Bahan Baku 100% Organik</li>
                    <li class="flex items-center">✅ Pengiriman Tepat Waktu</li>
                    <li class="flex items-center">✅ Harga Mahasiswa, Kualitas Resto</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- SECTION: MENU (ORACLE LOOP) -->
    <section id="menu" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 text-center mb-16">
            <h3 class="text-4xl font-bold" data-aos="fade-down">Menu Andalan</h3>
            <div class="w-20 h-1 bg-orange-600 mx-auto mt-4"></div>
        </div>

        <div class="flex overflow-x-auto space-x-8 px-6 pb-12 menu-slider scroll-smooth" data-aos="fade-up">
            <?php while ($row = oci_fetch_array($stmt_menu, OCI_ASSOC)): ?>
            <div class="min-w-[320px] md:min-w-[380px] bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-2xl transition duration-300">
                <div class="h-64 bg-gray-200">
                    <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c" class="w-full h-full object-cover" alt="Food">
                </div>
                <div class="p-8">
                    <h4 class="text-2xl font-bold mb-2"><?= $row['NAMA_MENU']; ?></h4>
                    <p class="text-gray-500 mb-6 text-sm"><?= $row['DESKRIPSI']; ?></p>
                    <div class="flex justify-between items-center">
                        <span class="text-2xl font-bold text-orange-600">Rp <?= number_format($row['HARGA'], 0, ',', '.'); ?></span>
                        <button @click="orderOpen = true; selectedMenu = '<?= $row['NAMA_MENU']; ?>'; selectedHarga = <?= $row['HARGA']; ?>" 
                                class="bg-gray-900 text-white px-6 py-2 rounded-xl hover:bg-orange-600 transition">Pesan</button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </section>

    <!-- SECTION: CONTACT, FEEDBACK & MAP -->
    <section id="contact" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12">
            <div data-aos="fade-up">
                <h3 class="text-3xl font-bold mb-6">Kritik & Saran</h3>
                <form class="space-y-4">
                    <input type="text" placeholder="Nama Lengkap" class="w-full p-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500">
                    <textarea placeholder="Pesan Anda" class="w-full p-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500 h-32"></textarea>
                    <button class="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold">Kirim Saran</button>
                </form>
            </div>
            <div data-aos="fade-up" class="rounded-3xl overflow-hidden shadow-xl h-80">
                <!-- Google Maps Iframe -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.103002626496!2d98.65345731475924!3d3.56586419740523!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30312fe3e48e02e1%3A0x6b668d29b0a68d0d!2sPoliteknik%20Negeri%20Medan!5e0!3m2!1sid!2sid!4v1649123456789!5m2!1sid!2sid" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <!-- MODAL LOGIN -->
<div x-show="loginOpen" 
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
     x-transition.opacity x-cloak>
    <div @click.away="loginOpen = false" class="bg-white w-full max-w-md p-10 rounded-[40px] shadow-2xl relative" data-aos="zoom-in">
        <button @click="loginOpen = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
        
        <h3 class="text-3xl font-bold mb-2 text-center text-gray-800">Login</h3>
        <p class="text-gray-500 text-center mb-8 text-sm">Masuk untuk mulai memesan catering favoritmu.</p>
        
        <form action="login.php" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Email</label>
                <input type="email" name="email" placeholder="contoh@gmail.com" 
                       class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 transition" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Password</label>
                <input type="password" name="password" placeholder="••••••••" 
                       class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 transition" required>
            </div>
            <button type="submit" name="login" class="w-full bg-orange-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-orange-700 transition transform hover:scale-[1.02]">
                Masuk Sekarang
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">
            Belum punya akun? <a href="#" @click="loginOpen = false; regOpen = true" class="text-orange-600 font-bold hover:underline">Daftar di sini</a>
        </p>
    </div>
</div>

<!-- MODAL REGISTER -->
<div x-show="regOpen" 
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
     x-transition.opacity x-cloak>
    <div @click.away="regOpen = false" class="bg-white w-full max-w-md p-10 rounded-[40px] shadow-2xl relative" data-aos="zoom-in">
        <button @click="regOpen = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
        
        <h3 class="text-3xl font-bold mb-2 text-center text-gray-800">Daftar Akun</h3>
        <p class="text-gray-500 text-center mb-8 text-sm">Bergabunglah dengan komunitas kuliner kami.</p>
        
        <form action="register.php" method="POST" class="space-y-4">
            <input type="text" name="nama" placeholder="Nama Lengkap" 
                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500" required>
            <input type="email" name="email" placeholder="Alamat Email" 
                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500" required>
            <input type="password" name="password" placeholder="Buat Password" 
                   class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500" required>
                   <div>
        <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Foto Profil</label>
        <input type="file" name="foto" class="w-full px-5 py-3 rounded-2xl bg-gray-100 border-none text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
    </div>
            <button type="submit" name="register" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-black transition transform hover:scale-[1.02]">
                Buat Akun
            </button>
        </form>
        <p class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun? <a href="#" @click="regOpen = false; loginOpen = true" class="text-orange-600 font-bold hover:underline">Login saja</a>
        </p>
    </div>
</div>

    <!-- MODAL PESANAN -->
    <div x-show="orderOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60" x-cloak>
        <div @click.away="orderOpen = false" class="bg-white w-full max-w-lg p-10 rounded-[40px] shadow-2xl relative">
            <h3 class="text-3xl font-bold mb-2">Konfirmasi Pesanan</h3>
            <p class="text-gray-500 mb-8">Menu: <span x-text="selectedMenu" class="font-bold text-orange-600"></span></p>
            <form action="proses_pesan.php" method="POST" class="space-y-4">
                <input type="hidden" name="nama_menu" :value="selectedMenu">
                <input type="number" name="jumlah" placeholder="Jumlah Porsi" class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none" required>
                <textarea name="alamat" placeholder="Alamat Pengantaran Lengkap" class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none h-24" required></textarea>
                <div class="bg-orange-50 p-4 rounded-2xl flex justify-between">
                    <span class="font-medium">Total Harga:</span>
                    <span class="font-bold text-orange-600">Rp <span x-text="selectedHarga.toLocaleString('id-ID')"></span></span>
                </div>
                <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-bold text-lg">Checkout ke Pembayaran</button>
            </form>
        </div>
    </div>
<footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h2 class="text-2xl font-bold mb-4">Catering Rizky</h2>
            <p class="text-gray-400 mb-8">Politeknik Negeri Medan - Manajemen Informatika 2026</p>
            <div class="flex justify-center space-x-6">
                <a href="#" class="hover:text-orange-500">Instagram</a>
                <a href="#" class="hover:text-orange-500">WhatsApp</a>
            </div>
        </div>
    </footer>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>
</html>