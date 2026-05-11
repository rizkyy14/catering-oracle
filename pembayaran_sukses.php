<?php
include 'config/database.php';
include 'config/xendit_config.php';

// Xendit biasanya mengirimkan ID Invoice atau External ID lewat URL saat redirect
// Tergantung konfigurasi success_redirect_url kamu
$external_id = $_GET['external_id'] ?? '';

if (!empty($external_id)) {
    // 1. Update status pesanan di database Oracle
    // Kita ubah PENDING jadi PAID berdasarkan EXTERNAL_ID
    $query = "UPDATE pesanan SET STATUS_PEMBAYARAN = 'PAID' 
              WHERE EXTERNAL_ID = :ext_id AND STATUS_PEMBAYARAN = 'PENDING'";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":ext_id", $external_id);
    $execute = oci_execute($stmt);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Berhasil - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">

    <div class="max-w-md w-full bg-white p-10 rounded-[40px] shadow-xl shadow-slate-200/50 text-center border border-slate-100">
        <!-- Icon Sukses -->
        <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Pembayaran Berhasil!</h1>
        <p class="text-slate-500 mb-8">Terima kasih, pesanan kamu dengan ID <span class="font-mono font-bold text-slate-700"><?= htmlspecialchars($external_id) ?></span> telah kami terima dan sedang diproses.</p>

        <div class="space-y-3">
            <a href="user/histori_pesanan.php" class="block w-full bg-slate-800 text-white py-4 rounded-2xl font-bold hover:bg-slate-900 transition">
                Lihat Histori Pesanan
            </a>
            <a href="user/index.php" class="block w-full bg-white text-slate-600 py-4 rounded-2xl font-bold border border-slate-200 hover:bg-slate-50 transition">
                Kembali ke Beranda
            </a>
        </div>
        
        <p class="mt-8 text-xs text-slate-400 italic">Sekarang kamu sudah bisa mencetak invoice PDF di halaman histori.</p>
    </div>

</body>
</html>