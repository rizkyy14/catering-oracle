<?php
include '../config/database.php';

$id_pesanan = $_GET['id'];

// Query Detail Pesanan & User (Join agar dapat nama pelanggan)
$query = "SELECT p.*, u.NAMA 
          FROM pesanan p 
          JOIN users u ON p.ID_USER = u.ID_USER 
          WHERE p.ID_PESANAN = :id";

$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ":id", $id_pesanan);
oci_execute($stmt);
$data = oci_fetch_array($stmt, OCI_ASSOC);

if (!$data) {
    die("Data tidak ditemukan!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $data['ID_PESANAN'] ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 p-10">

    <div class="max-w-3xl mx-auto bg-white p-8 border border-gray-200 shadow-sm" id="printable">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-orange-600">Catering<span class="text-gray-800">Rizky</span></h1>
                <p class="text-sm text-gray-500">Politeknik Negeri Medan</p>
            </div>
            <div class="text-right">
                <h2 class="text-xl font-bold text-gray-800">INVOICE</h2>
                <p class="text-sm text-gray-500">#<?= $data['EXTERNAL_ID'] ?></p>
            </div>
        </div>

        <hr class="mb-6">

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Ditagihkan Kepada:</p>
                <p class="font-bold text-gray-800"><?= $data['NAMA'] ?></p>
                <p class="text-sm text-gray-500">Pelanggan Setia Catering Rizky</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 uppercase font-bold">Tanggal Transaksi:</p>
                <p class="text-sm text-gray-800"><?= $data['TGL_PESAN'] ?></p>
                <p class="mt-2 text-xs text-gray-400 uppercase font-bold">Status:</p>
                <p class="text-sm font-bold text-green-600"><?= $data['STATUS_PEMBAYARAN'] ?></p>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="border-b-2 border-gray-100 text-left">
                    <th class="py-3 text-sm font-bold text-gray-600">Deskripsi Pesanan</th>
                    <th class="py-3 text-sm font-bold text-gray-600 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-50">
                    <td class="py-4 text-gray-800 text-sm">Pemesanan Catering Paket Hemat</td>
                    <td class="py-4 text-gray-800 text-sm text-right font-bold">Rp <?= number_format($data['TOTAL_BAYAR'], 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end">
            <div class="w-1/2">
                <div class="flex justify-between py-2 border-t-2 border-gray-800">
                    <span class="font-bold text-gray-800">Total Bayar</span>
                    <span class="font-bold text-orange-600 text-lg">Rp <?= number_format($data['TOTAL_BAYAR'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center text-xs text-gray-400">
            <p>Bukti pembayaran ini sah dan dihasilkan otomatis oleh sistem **webcatering**.</p>
        </div>
    </div>

    <!-- Tombol Aksi -->
    <div class="max-w-3xl mx-auto mt-6 flex justify-center space-x-4 no-print">
        <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900 transition">
            Cetak Sekarang
        </button>
        <button onclick="window.close()" class="bg-white text-gray-600 px-6 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
            Tutup Halaman
        </button>
    </div>

</body>
</html>