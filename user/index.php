<?php
include '../config/auth.php'; // Pastikan path benar
include '../config/database.php';

// Proteksi: Pastikan hanya user yang sudah login bisa akses
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php"); // Keluar ke landing page jika belum login
    exit();
}

$id_user = $_SESSION['id_user'];

// 1. Ambil Data User Secara Spesifik (Untuk Nama & Foto Profil)
$query_user = "SELECT * FROM users WHERE id_user = :id";
$stmt_user = oci_parse($conn, $query_user);
oci_bind_by_name($stmt_user, ":id", $id_user);
oci_execute($stmt_user);
$user = oci_fetch_array($stmt_user, OCI_ASSOC);

// Simpan ulang ke session agar sinkron (opsional)
$_SESSION['nama'] = $user['NAMA'];

// 2. Ambil Data Menu dari Oracle (Untuk Loop Card Menu)
$query_menu = "SELECT * FROM menu_catering ORDER BY id_menu DESC";
$stmt_menu = oci_parse($conn, $query_menu);
oci_execute($stmt_menu);
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pelanggan - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
        .menu-slider::-webkit-scrollbar { display: none; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body x-data="{ profileOpen: false, orderOpen: false, selectedMenu: '', selectedHarga: 0, idMenu: '' }" class="bg-gray-50">

    <!-- NAVBAR USER -->
    <nav class="fixed w-full z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-orange-600">Catering<span class="text-gray-800">Rizky</span></h1>
            
            <div class="hidden md:flex space-x-8 font-medium">
                <a href="#menu" class="hover:text-orange-500 transition">Jelajah Menu</a>
                <a href="histori_pesanan.php" class="hover:text-orange-500 transition">Pesanan Saya</a>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative">
                <!-- Navbar Profile Section -->
<button @click="profileOpen = !profileOpen" class="flex items-center focus:outline-none group">
    <div class="text-right mr-3 hidden md:block">
        <p class="text-sm font-bold text-gray-800"><?= $user['NAMA'] ?></p>
        <p class="text-xs text-gray-500 italic">Pelanggan</p>
    </div>
    
   <!-- Foto Profil Dinamis -->
<div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white shadow-sm group-hover:ring-2 group-hover:ring-orange-50 transition">
    <?php if (!empty($user['FOTO_PROFIL'])): ?>
        <!-- Jika ada data di kolom FOTO_PROFIL (baik itu default.png atau file upload) -->
        <img src="../assets/img/profile/<?= $user['FOTO_PROFIL'] ?>" 
             class="w-full h-full object-cover" 
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        
        <!-- Fallback Inisial jika file gambar ternyata tidak ditemukan di folder (Error 404) -->
        <div class="hidden w-full h-full bg-orange-600 flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr($user['NAMA'], 0, 1)) ?>
        </div>
    <?php else: ?>
        <!-- Jika kolom di database benar-benar NULL/Kosong -->
        <div class="w-full h-full bg-orange-600 flex items-center justify-center text-white font-bold">
            <?= strtoupper(substr($user['NAMA'], 0, 1)) ?>
        </div>
    <?php endif; ?>
</div>
</button>

                <div x-show="profileOpen" @click.away="profileOpen = false" x-cloak
                     class="absolute right-0 mt-3 w-48 bg-white rounded-2xl shadow-xl py-2 border border-gray-100 z-50">
                    <a href="profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">Profil Saya</a>
                    <a href="histori_pesanan.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-600">Histori Pesanan</a>
                    <hr class="my-2 border-gray-100">
                    <a href="../logout.php" class="block px-4 py-2 text-sm text-red-600 font-bold hover:bg-red-50">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- HERO USER -->
    <section class="pt-32 pb-12 px-6">
        <div class="max-w-7xl mx-auto bg-orange-600 rounded-[40px] p-10 md:p-20 text-white relative overflow-hidden shadow-2xl">
            <div class="relative z-10" data-aos="fade-right">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Mau Makan Apa <br> Hari Ini, <?= explode(' ', $_SESSION['nama'])[0] ?>?</h2>
                <p class="text-orange-100 mb-8 max-w-md">Pesan sekarang dan nikmati layanan catering terbaik langsung ke alamat Anda.</p>
                <a href="#menu" class="bg-white text-orange-600 px-8 py-3 rounded-full font-bold shadow-lg hover:bg-orange-50 transition inline-block">Cek Menu</a>
            </div>
            <!-- Dekorasi -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full -mr-20 -mt-20 opacity-50"></div>
        </div>
    </section>

    <!-- SECTION MENU -->
    <section id="menu" class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-3xl font-bold mb-10" data-aos="fade-up">Daftar Menu Tersedia</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-aos="fade-up">
                <?php while ($row = oci_fetch_array($stmt_menu, OCI_ASSOC)): ?>
                <div class="bg-white rounded-[32px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 group">
                    <div class="h-52 bg-gray-200 relative overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1546069901-ba9599a7e63c" class="w-full h-full object-cover group-hover:scale-110 transition duration-500" alt="Food">
                    </div>
                    <div class="p-6">
                        <h4 class="text-xl font-bold mb-2"><?= $row['NAMA_MENU']; ?></h4>
                        <p class="text-gray-500 text-sm mb-6 line-clamp-2"><?= $row['DESKRIPSI']; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-bold text-orange-600">Rp <?= number_format($row['HARGA'], 0, ',', '.'); ?></span>
                            <button @click="orderOpen = true; selectedMenu = '<?= $row['NAMA_MENU']; ?>'; selectedHarga = <?= $row['HARGA']; ?>; idMenu = '<?= $row['ID_MENU']; ?>'" 
                                    class="bg-gray-900 text-white px-6 py-2 rounded-xl hover:bg-orange-600 transition shadow-md">Pesan</button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>

    <!-- Bagian Modal Pesanan -->
<div x-show="orderOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition.opacity>
    <!-- Tambahkan variabel qty di x-data untuk sinkronisasi hitungan -->
    <div @click.away="orderOpen = false" 
         x-data="{ qty: 1 }" 
         class="bg-white w-full max-w-lg p-10 rounded-[40px] shadow-2xl relative">
        
        <button @click="orderOpen = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 text-2xl">&times;</button>
        
        <h3 class="text-3xl font-bold mb-2 text-gray-800">Konfirmasi Pesanan</h3>
        <p class="text-gray-500 mb-8">Menu: <span x-text="selectedMenu" class="font-bold text-orange-600"></span></p>
        
        <form action="proses_pesan.php" method="POST" class="space-y-4">
            <!-- Data hidden untuk dikirim ke PHP -->
            <input type="hidden" name="id_menu" :value="idMenu">
            <input type="hidden" name="harga_satuan" :value="selectedHarga">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Jumlah Porsi</label>
                <!-- Gunakan x-model agar sinkron dengan variabel qty -->
                <input type="number" name="jumlah" min="1" x-model.number="qty" 
                       class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 transition" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Alamat Pengantaran</label>
                <textarea name="alamat" placeholder="Masukkan alamat lengkap pengantaran..." 
                          class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 h-28" required></textarea>
            </div>

            <!-- Box Estimasi yang Ter-sinkronisasi -->
            <div class="bg-orange-50 p-6 rounded-3xl flex justify-between items-center mb-4 border border-orange-100">
                <div>
                    <p class="text-xs text-orange-600 font-bold uppercase tracking-wider">Total yang Harus Dibayar</p>
                    <!-- Logic Matematika: Harga dikali Quantity -->
                    <p class="text-2xl font-bold text-gray-900">
                        Rp <span x-text="(selectedHarga * qty).toLocaleString('id-ID')"></span>
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 italic leading-tight">*Harga per porsi:</p>
                    <p class="text-xs font-semibold text-gray-600">Rp <span x-text="selectedHarga.toLocaleString('id-ID')"></span></p>
                </div>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-black transition transform active:scale-95">
                Konfirmasi & Pesan Sekarang
            </button>
        </form>
    </div>
</div>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, once: true });</script>
</body>
</html>