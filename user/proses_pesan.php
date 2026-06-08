<?php
// File: user/proses_pesan.php

include '../config/auth.php';
include '../config/xendit_config.php'; // Pastikan file ini berisi $xendit_key

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user      = $_SESSION['id_user'];
    $harga_satuan = $_POST['harga_satuan'];
    $jumlah       = $_POST['jumlah'];
    $email_user   = $_SESSION['email'] ?? 'pelanggan@example.com'; 
    $tipe_pesanan = $_POST['tipe_pesanan'];
    
    // 1. Hitung Total Bayar
    $total_bayar  = $harga_satuan * $jumlah;

    // 2. Buat External ID unik untuk Xendit
    $external_id  = 'INV-' . time() . '-' . $id_user;

    // 3. Bangun URL Sukses Dinamis Menggunakan Trik Domain Global (Mendukung Ngrok / localhost)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    
    if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $domain = $_SERVER['HTTP_X_FORWARDED_HOST'];
        $protocol = "https://"; // Ngrok wajib HTTPS
    } else {
        $domain = $_SERVER['HTTP_HOST'];
    }
    
    // PERBAIKAN TOTAL: Kita buang variabel $id_pesanan_terakhir yang ghaib itu.
    // Jalur sukses langsung ditembak ke file root utama dengan membawa ?external_id bawaan asli Xendit
    $success_url = $protocol . $domain . "/pembayaran_sukses.php";

    // 4. Request ke API Xendit untuk buat Invoice
    $data_xendit = [
        'external_id' => $external_id,
        'amount' => (int)$total_bayar,
        'payer_email' => $email_user,
        'description' => 'Pemesanan Catering - ' . $external_id,
        'invoice_duration' => 86400,
        'success_redirect_url' => $success_url // Xendit otomatis akan menempelkan ?external_id=... di belakang URL ini saat redirect balik
    ];

    $payload = json_encode($data_xendit);

    $ch = curl_init('https://api.xendit.co/v2/invoices');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, $xendit_key . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    
    // 5. Cek apakah Xendit berhasil memberikan URL Checkout
    if (isset($result['invoice_url'])) {
        $checkout_url = $result['invoice_url'];

        $id_user_int  = (int)$id_user;
        $total_int    = (int)$total_bayar;
        
        // 6. Cetak Halaman HTML + JS Fetch untuk Amankan Data ke Oracle Cloud
        echo "<!DOCTYPE html>
        <html lang='id'>
        <head>
            <meta charset='UTF-8'>
            <title>Memproses Pesanan...</title>
            <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
            <style>
                body { font-family: Arial, sans-serif; background: #f9fafb; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
                .loader { border: 4px solid #f3f3f3; border-top: 4px solid #ea580c; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
                @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                .box { text-align: center; background: white; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 16px; width: 100%; max-width: 400px; margin: 20px; }
            </style>
        </head>
        <body>

        <div class='box'>
            <div class='loader'></div>
            <p style='color: #4b5563; font-weight: bold;'>Mengamankan data pesanan ke Oracle Cloud...</p>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', async function() {
            const dataOracle = {
                id_user: $id_user_int,
                total_bayar: $total_int,
                external_id: '$external_id',
                checkout_url: '$checkout_url',
                tipe_pesanan: '$tipe_pesanan'
            };

            try {
                // Eksekusi POST ke REST API Oracle Cloud untuk simpan pesanan baru baru
                const response = await fetch('https://oracleapex.com/ords/rizky_catering/catering/pesanan', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(dataOracle)
                });

                if (response.status === 200 || response.status === 201) {
    const jsonResult = await response.json(); // Ambil respon dari Oracle APEX
    const id_pesanan_baru = jsonResult.id_pesanan; // Ambil ID dari cloud

    // --- TRIK BARU: Titipkan ID ke Session Lokal via Fetch ---
    await fetch('set_session.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_pesanan: id_pesanan_baru })
    });

    const loaderBox = document.querySelector('.box');
    if(loaderBox) { loaderBox.style.display = 'none'; }

    Swal.fire({
        icon: 'success',
        title: 'Pesanan Berhasil Disimpan!',
        text: 'Klik tombol di bawah untuk lanjut ke halaman pembayaran aman Xendit.',
        confirmButtonText: 'Bayar Sekarang',
        confirmButtonColor: '#ea580c',
        allowOutsideClick: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '$checkout_url'; // Pergi ke Xendit
        }
    });
} else {
                    throw new Error('Server Oracle merespon dengan kode: ' + response.status);
                }

            } catch (error) {
                console.error('Koneksi Gagal:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Waduh, Gagal Menyimpan!',
                    text: error.message + '. Silakan coba beberapa saat lagi.',
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#dc2626'
                }).then(() => {
                    window.history.back();
                });
            }
        });
        </script>
        </body>
        </html>";
        exit();

    } else {
        echo "Gagal terhubung ke Xendit: " . htmlspecialchars($result['message'] ?? 'Unknown Error');
    }
}
?>