<?php
include '../config/auth.php';
// include '../config/database.php'; // <--- Dimatikan karena beralih ke Cloud REST API

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$id_user = $_SESSION['id_user'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Histori Pesanan - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50" x-data="riwayatCatering()">

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

        <template x-if="loading">
            <div class="grid gap-6">
                <div class="bg-white p-6 rounded-[32px] animate-pulse border border-gray-100 flex justify-between items-center h-28">
                    <div class="h-12 bg-gray-200 rounded-xl w-2/3"></div>
                    <div class="h-10 bg-gray-200 rounded-xl w-1/4"></div>
                </div>
            </div>
        </template>

        <template x-if="!loading">
            <div class="grid gap-6">
                <template x-for="item in listPesanan" :key="item.id_pesanan">
                    <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-6 hover:shadow-md transition">
                        <div class="flex items-center space-x-5">
                            <div class="w-16 h-16 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mb-1" x-text="'ID Pesanan: #' + item.id_pesanan"></p>
                                <h4 class="text-lg font-bold text-gray-800" x-text="'Total: Rp ' + formatRupiah(item.total_bayar)"></h4>
                                <p class="text-sm text-gray-500 font-medium text-orange-600/80" x-text="item.tipe_pesanan"></p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <span class="px-4 py-2 rounded-full text-xs font-bold"
                                  :class="item.status_pembayaran === 'PAID' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                  x-text="item.status_pembayaran">
                            </span>
                            
                            <template x-if="item.status_pembayaran === 'PENDING'">
                                <a :href="item.checkout_url" 
                                   target="_blank"
                                   class="bg-orange-600 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-orange-100 hover:bg-orange-700 transition">
                                    Bayar Sekarang
                                </a>
                            </template>
                            
                            <template x-if="item.status_pembayaran === 'PAID'">
                                <a :href="'../admin/cetak_pdf.php?id=' + item.id_pesanan" 
                                   target="_blank"
                                   class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition">
                                    Cetak PDF
                                </a>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="listPesanan.length === 0">
                    <div class="text-center py-20 bg-gray-50 rounded-[40px] border-2 border-dashed border-gray-200">
                        <p class="text-gray-400 font-medium">Belum ada riwayat pesanan.</p>
                        <a href="index.php" class="text-orange-600 font-bold mt-2 inline-block">Mulai Pesan Sekarang</a>
                    </div>
                </template>
            </div>
        </template>
    </main>

    <script>
        function riwayatCatering() {
            return {
                listPesanan: [],
                loading: true,
                idUser: <?= json_encode($id_user) ?>,
                
                async init() {
                    try {
                        // Tambahkan mode cors otomatis di level browser
                        const response = await fetch(`https://oracleapex.com/ords/rizky_catering/catering/riwayat/${this.idUser}`, {
                            method: 'GET',
                            headers: { 'Accept': 'application/json' }
                        });
                        
                        if (!response.ok) throw new Error("Gagal mengambil data riwayat.");
                        
                        const data = await response.json();
                        this.listPesanan = data.filter(item => item.status !== 'end');
                    } catch (error) {
                        console.error(error);
                    } finally {
                        this.loading = false;
                    }
                },

                formatRupiah(angka) {
                    return new Intl.NumberFormat('id-ID').format(angka);
                }
            }
        }
    </script>
</body>
</html>