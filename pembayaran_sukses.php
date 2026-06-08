<?php
// File: pembayaran_sukses.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil ID_PESANAN angka yang aman dari Session internal kita
$id_pesanan = isset($_SESSION['id_pesanan_terakhir']) ? $_SESSION['id_pesanan_terakhir'] : '';

// Bersihkan session setelah diambil agar tidak gantung di pesanan berikutnya
if (!empty($id_pesanan)) {
    unset($_SESSION['id_pesanan_terakhir']);
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
        <div id="status-loading">
            <div class="w-12 h-12 border-4 border-slate-200 border-top-4 border-emerald-500 rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-slate-600 font-medium">Menyinkronkan status pembayaran ke Cloud...</p>
        </div>

        <div id="status-box-sukses" class="hidden">
            <div class="w-24 h-24 bg-emerald-50 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 mb-2">Pembayaran Berhasil!</h1>
            <p class="text-slate-500 mb-8">Terima kasih, pesanan kamu dengan ID <span class="font-mono font-bold text-slate-700">#<span id="txt-id-pesanan"><?= htmlspecialchars($id_pesanan) ?></span></span> telah berhasil di-update menjadi <span class="text-emerald-600 font-bold">PAID</span> di Cloud Server.</p>
        </div>

        <div id="status-box-gagal" class="hidden">
            <div class="w-24 h-24 bg-rose-50 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-rose-600 mb-2">Sinkronisasi Gagal</h1>
            <p class="text-amber-600 mb-8 font-medium">Gagal memperbarui status ke server Cloud Oracle.</p>
        </div>

        <div class="space-y-3 mt-6">
            <a href="user/histori_pesanan.php" class="block w-full bg-slate-800 text-white py-4 rounded-2xl font-bold hover:bg-slate-900 transition">
                Lihat Histori Pesanan
            </a>
            <a href="user/index.php" class="block w-full bg-white text-slate-600 py-4 rounded-2xl font-bold border border-slate-200 hover:bg-slate-50 transition">
                Kembali ke Beranda
            </a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const id_pesanan = "<?= $id_pesanan ?>";
        
        if (!id_pesanan) {
            document.getElementById('status-loading').classList.add('hidden');
            document.getElementById('status-box-gagal').classList.add('hidden');
            document.getElementById('status-box-sukses').classList.remove('hidden');
            document.getElementById('txt-id-pesanan').innerText = "Terbaru";
            return;
        }

        // Tembak langsung REST API UPDATE Cloud Oracle dari browser user (Anti-403)
        fetch('https://oracleapex.com/ords/rizky_catering/catering/webhook_pembayaran', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                external_id: id_pesanan, // Di handler APEX sudah kita set mencari ID_PESANAN atau EXTERNAL_ID
                status: 'PAID'
            })
        })
        .then(response => {
            document.getElementById('status-loading').classList.add('hidden');
            if (response.ok || response.status === 200) {
                document.getElementById('status-box-sukses').classList.remove('hidden');
            } else {
                document.getElementById('status-box-gagal').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('status-loading').classList.add('hidden');
            document.getElementById('status-box-gagal').classList.remove('hidden');
        });
    });
    </script>
</body>
</html>