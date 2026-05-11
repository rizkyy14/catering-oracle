<?php
include '../config/auth.php';
include '../config/database.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_user = $_SESSION['id_user'];

// Query untuk mengambil data pesanan milik user yang sedang login
// Kita urutkan dari yang terbaru (DESC)
$query = "SELECT * FROM pesanan WHERE ID_USER = :id ORDER BY TGL_PESAN DESC";
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":id", $id_user);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Histori Pesanan - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50">

    <!-- NAVBAR SEDERHANA -->
    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold text-orange-600">Catering<span class="text-gray-800">Rizky</span></a>
            <a href="index.php" class="text-sm font-semibold text-gray-600 hover:text-orange-600 transition">← Kembali Belanja</a>
        </div>
    </nav>

    <main class="pt-28 pb-12 px-6 max-w-5xl mx-auto">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-800">Riwayat Pesanan</h2>
            <p class="text-gray-500">Pantau status pesanan dan pembayaran kamu di sini.</p>
        </div>

        <div class="grid gap-6">
            <?php 
            $found = false;
            while ($row = oci_fetch_array($stmt, OCI_ASSOC)): 
                $found = true;
                // Logika warna status
                $status = $row['STATUS_PEMBAYARAN'];
                $status_class = ($status == 'PAID') ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
            ?>
            <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-md transition">
                <div class="flex items-center space-x-5">
                    <!-- Icon Box -->
                    <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1">ID Pesanan: #<?= $row['ID_PESANAN'] ?></p>
                        <h4 class="text-lg font-bold text-gray-800">Total: Rp <?= number_format($row['TOTAL_BAYAR'], 0, ',', '.') ?></h4>
                        <p class="text-sm text-gray-500"><?= $row['TGL_PESAN'] ?></p>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
    <span class="px-4 py-2 rounded-full text-xs font-bold <?= $status_class ?>">
        <?= $status ?>
    </span>
    
    <?php if ($status == 'PENDING'): ?>
        <!-- ARAHKAN LANGSUNG KE CHECKOUT_URL DARI DATABASE -->
        <a href="<?= $row['CHECKOUT_URL'] ?>" 
           target="_blank"
           class="bg-orange-600 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-orange-100 hover:bg-orange-700 transition">
            Bayar Sekarang
        </a>
    <?php else: ?>
        <!-- Tombol Cetak PDF hanya muncul jika status PAID -->
        <a href="../admin/cetak_pdf.php?id=<?= $row['ID_PESANAN'] ?>" 
           target="_blank"
           class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition">
            Cetak PDF
        </a>
    <?php endif; ?>
</div>
            </div>
            <?php endwhile; ?>

            <?php if (!$found): ?>
                <div class="text-center py-20 bg-gray-50 rounded-[40px] border-2 border-dashed border-gray-200">
                    <p class="text-gray-400 font-medium">Belum ada riwayat pesanan.</p>
                    <a href="home_user.php" class="text-orange-600 font-bold mt-2 inline-block">Mulai Pesan Sekarang</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>