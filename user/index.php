<?php
include '../config/auth.php';
include '../config/database.php';

// --- LOGIKA UPDATE STATUS PEMBAYARAN DI index.php ---
if (isset($_GET['pembayaran_selesai'])) {
    $ext_id = $_GET['pembayaran_selesai'];
    
    $query_update = "UPDATE pesanan SET STATUS_PEMBAYARAN = 'PAID' 
                    WHERE EXTERNAL_ID = :eid AND STATUS_PEMBAYARAN = 'PENDING'";
    
    $stmt_update = oci_parse($conn, $query_update);
    oci_bind_by_name($stmt_update, ":eid", $ext_id);
    $exec_update = oci_execute($stmt_update);

    if ($exec_update) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    html: 'Pesanan Anda sedang diproses.<br><b>Tim kami akan menghubungi Anda melalui email untuk detail pengiriman.</b>',
                    confirmButtonColor: '#ea580c'
                }).then(() => { window.location.href='index.php'; });
            });
        </script>";
    }
}

if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// --- PERBAIKAN: Jangan jalankan query jika id_user bukan angka ---
if (is_numeric($id_user)) {
    $query_user = "SELECT * FROM users WHERE id_user = :id";
    $stmt_user = oci_parse($conn, $query_user);
    oci_bind_by_name($stmt_user, ":id", $id_user);
    oci_execute($stmt_user);
    $user = oci_fetch_array($stmt_user, OCI_ASSOC);
} else {
    // Jika ID tidak valid (seperti 'NEW_USER'), paksa jadi false agar masuk ke blok pengaman di bawah
    $user = false;
}

// --- BARIKADE PENGAMAN (Tetap gunakan ini) ---
if (!$user) {
    $user = [
        'NAMA'  => $_SESSION['nama'] ?? 'Pelanggan Baru',
        'EMAIL' => $_SESSION['email'] ?? 'pelanggan@catering.com',
        'FOTO_PROFIL' => null
    ];
} else {
    // Jika data ketemu, sinkronkan ulang ke session menggunakan kapital bawaan Oracle
    $_SESSION['nama'] = $user['NAMA'] ?? $_SESSION['nama'];
}

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(12px); }
        .menu-slider::-webkit-scrollbar { display: none; }
        [x-cloak] { display: none !important; }

        .menu-slider {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 1.5rem;
            padding-bottom: 1rem;
        }
        .menu-card {
            flex: 0 0 300px; /* Lebar kartu di mobile */
            scroll-snap-align: start;
        }
        @media (min-width: 768px) {
            .menu-card { flex: 0 0 350px; } /* Lebar kartu di desktop */
        }
        .menu-slider::-webkit-scrollbar { display: none; }
    </style>
</head>
<body x-data="{ profileOpen: false, orderOpen: false, selectedMenu: '', selectedHarga: 0, idMenu: '' }" class="bg-gray-50">

    <nav class="fixed w-full z-50 glass border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-orange-600">Catering<span class="text-gray-800">Rizky</span></h1>
            
            <div class="hidden md:flex space-x-8 font-medium">
                <a href="#menu" class="hover:text-orange-500 transition">Jelajah Menu</a>
                <a href="user/histori_pesanan.php" class="hover:text-orange-500 transition">Pesanan Saya</a>
            </div>

            <div class="relative">
                <button @click="profileOpen = !profileOpen" class="flex items-center focus:outline-none group">
                    <div class="text-right mr-3 hidden md:block">
                        <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($user['NAMA'] ?? 'Pelanggan') ?></p>
                        <p class="text-xs text-gray-500 italic">Pelanggan</p>
                    </div>
                    
                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white shadow-sm group-hover:ring-2 group-hover:ring-orange-50 transition">
                        <?php if (!empty($user['FOTO_PROFIL'])): ?>
                            <img src="../assets/img/profile/<?= $user['FOTO_PROFIL'] ?>" 
                                 class="w-full h-full object-cover" 
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            
                            <div class="hidden w-full h-full bg-orange-600 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($user['NAMA'] ?? 'P', 0, 1)) ?>
                            </div>
                        <?php else: ?>
                            <div class="w-full h-full bg-orange-600 flex items-center justify-center text-white font-bold">
                                <?= strtoupper(substr($user['NAMA'] ?? 'P', 0, 1)) ?>
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

    <section class="pt-32 pb-12 px-6">
        <div class="max-w-7xl mx-auto bg-orange-600 rounded-[40px] p-10 md:p-20 text-white relative overflow-hidden shadow-2xl">
            <div class="relative z-10" data-aos="fade-right">
                <h2 class="text-4xl md:text-5xl font-bold mb-4">Mau Makan Apa <br> Hari Ini, <?= explode(' ', $user['NAMA'] ?? 'Pelanggan')[0] ?>?</h2>
                <p class="text-orange-100 mb-8 max-w-md">Pesan sekarang dan nikmati layanan catering terbaik langsung ke alamat Anda.</p>
                <a href="#menu" class="bg-white text-orange-600 px-8 py-3 rounded-full font-bold shadow-lg hover:bg-orange-50 transition inline-block">Cek Menu</a>
            </div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full -mr-20 -mt-20 opacity-50"></div>
        </div>
    </section>

    <section id="menu" class="py-16 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6">
            <div class="flex justify-between items-end mb-10" data-aos="fade-up">
                <div>
                    <h3 class="text-3xl font-bold">Daftar Menu Tersedia</h3>
                    <p class="text-gray-500 mt-2">Geser untuk melihat menu lezat lainnya</p>
                </div>
                <div class="hidden md:flex space-x-2">
                    <div class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-400 italic text-xs">Scroll -></div>
                </div>
            </div>
            
            <div class="menu-slider" data-aos="fade-up">
                <?php while ($row = oci_fetch_array($stmt_menu, OCI_ASSOC)): ?>
                <div class="menu-card bg-white rounded-[32px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 group">
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

                <div class="menu-card bg-orange-600 rounded-[32px] overflow-hidden shadow-lg border border-orange-500 group flex flex-col justify-center p-8 text-white relative">
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-6 text-3xl">👨‍🍳</div>
                        <h4 class="text-2xl font-bold mb-2">Punya Menu Sendiri?</h4>
                        <p class="text-orange-100 text-sm mb-8">Request menu custom untuk acara spesial Anda. Diskusikan langsung dengan Chef kami via WhatsApp.</p>
                        <a href="https://wa.me/6281262581027?text=Halo%20Catering%20Rizky,%20saya%20ingin%20diskusi%20tentang%20pesanan%20menu%20custom." 
                           target="_blank"
                           class="inline-block w-full text-center bg-white text-orange-600 py-4 rounded-2xl font-bold hover:bg-orange-50 transition shadow-xl">
                             Diskusi via WhatsApp
                        </a>
                    </div>
                    <div class="absolute -bottom-10 -right-10 w-40 h-40 bg-orange-500 rounded-full opacity-30"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6">
            <h3 class="text-3xl font-bold mb-8 text-center">Diskusi & <span class="text-orange-600">Review</span></h3>
            <div id="disqus_thread"></div>
            <script>
                var disqus_config = function () {
                    this.page.url = window.location.href;  
                    this.page.identifier = 'catering_rizky_main'; 
                };
                (function() { 
                    var d = document, s = d.createElement('script');
                    s.src = 'https://catering-2.disqus.com/embed.js';
                    s.setAttribute('data-timestamp', +new Date());
                    (d.head || d.body).appendChild(s);
                })();
            </script>
        </div>
    </section>

    <section id="contact" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-12">
            <div data-aos="fade-up">
                <h3 class="text-3xl font-bold mb-6">Kritik & Saran</h3>
                <div class="space-y-4">
                    <input type="text" id="wa_nama" placeholder="Nama Lengkap" class="w-full p-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500">
                    <textarea id="wa_pesan" placeholder="Pesan Anda" class="w-full p-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500 h-32"></textarea>
                    <button onclick="sendToWA()" class="bg-orange-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-orange-700 transition">
                        Kirim ke WhatsApp
                    </button>
                </div>
            </div>
            <div data-aos="fade-up" class="rounded-3xl overflow-hidden shadow-xl h-80">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.103002626496!2d98.65345731475924!3d3.56586419740523!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x30312fe3e48e02e1%3A0x6b668d29b0a68d0d!2sPoliteknik%20Negeri%20Medan!5e0!3m2!1sid!2sid!4v1649123456789!5m2!1sid!2sid" 
                        width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
    </section>

    <div x-show="orderOpen" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-cloak x-transition.opacity>
        <div @click.away="orderOpen = false" 
             x-data="{ qty: 1 }" 
             class="bg-white w-full max-w-lg p-10 rounded-[40px] shadow-2xl relative">
            
            <button @click="orderOpen = false" class="absolute top-6 right-6 text-gray-400 hover:text-gray-800 text-2xl">×</button>
            
            <h3 class="text-3xl font-bold mb-2 text-gray-800">Konfirmasi Pesanan</h3>
            <p class="text-gray-500 mb-8">Menu: <span x-text="selectedMenu" class="font-bold text-orange-600"></span></p>
            
            <form action="proses_pesan.php" method="POST" class="space-y-4">
                <input type="hidden" name="id_menu" :value="idMenu">
                <input type="hidden" name="harga_satuan" :value="selectedHarga">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Jumlah Porsi</label>
                    <input type="number" name="jumlah" min="1" x-model.number="qty" 
                           class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 transition" required>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1 ml-1">Alamat Pengantaran</label>
                    <textarea name="alamat" placeholder="Masukkan alamat lengkap pengantaran..." 
                              class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500 h-28" required></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pesanan</label>
                    <select name="tipe_pesanan" class="w-full px-5 py-4 rounded-2xl bg-gray-100 border-none focus:ring-2 focus:ring-orange-500">
                        <option value="Harian">Harian (Sekali Pesan)</option>
                        <option value="Bulanan">Langganan Bulanan (30 Hari)</option>
                        <option value="Event">Acara / Event (Jumlah Besar)</option>
                    </select>
                </div>

                <div class="bg-orange-50 p-6 rounded-3xl flex justify-between items-center mb-4 border border-orange-100">
                    <div>
                        <p class="text-xs text-orange-600 font-bold uppercase tracking-wider">Total yang Harus Dibayar</p>
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
    <script>AOS.init({ duration: 800, once: true });</script>
    <script>
        function sendToWA() {
            const nama = document.getElementById('wa_nama').value;
            const pesan = document.getElementById('wa_pesan').value;
            const nomorWA = "6281262581027";

            if (nama === "" || pesan === "") {
                alert("Mohon isi nama dan pesan terlebih dahulu!");
                return;
            }

            const teks = `Halo Admin Catering Rizky,%0A%0ANama: *${nama}*%0APesan: ${pesan}`;
            window.open(`https://wa.me/${nomorWA}?text=${teks}`, '_blank');
        }
    </script>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    // Trik otomatis: Jika halaman ini terbuka setelah redirect pembayaran sukses
    // Kita bantu sinkronisasi status PAID langsung dari browser user ke Oracle Cloud
    const urlParams = new URLSearchParams(window.location.search);
    const invoiceUrl = urlParams.get('external_id');

    if (invoiceUrl) {
        // Tembak langsung REST API Oracle Cloud via Fetch Browser (Pasti Lolos Anti-403)
        fetch('https://oracleapex.com/ords/rizky_catering/catering/webhook_pembayaran', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                external_id: invoiceUrl,
                status: 'PAID'
            })
        })
        .then(res => res.json())
        .then(data => console.log("Sinkronisasi Cloud Berhasil:", data))
        .catch(err => console.error("Gagal sinkron cloud:", err));
    }
});
</script>
</body>
</html>