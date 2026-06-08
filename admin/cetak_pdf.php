<?php
// Ambil Detail Transaksi dari Oracle Cloud APEX via cURL Aman
$url_apex_detail = "https://oracleapex.com/ords/rizky_catering/catering/pesanan?limit=500";
$ch = curl_init($url_apex_detail);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$data_apex = json_decode($response, true);
$items_raw = $data_apex['items'] ?? [];

$data_pesanan = null;
foreach ($items_raw as $raw_p) {
    $p = array_change_key_case($raw_p, CASE_LOWER); // Paksa ke lowercase
    if (isset($p['id_pesanan']) && $p['id_pesanan'] == $id_pesanan) {
        $data_pesanan = $p;
        break;
    }
}

if (!$data_pesanan) {
    die("Data pesanan tidak ditemukan di Cloud server!");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?= $data_pesanan['id_pesanan'] ?></title>
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
                <p class="text-sm text-gray-500">#<?= htmlspecialchars($data_pesanan['external_id'] ?? 'INV-CLOUD') ?></p>
            </div>
        </div>

        <hr class="mb-6">

        <div class="grid grid-cols-2 gap-6 mb-8">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Ditagihkan Kepada:</p>
                <p class="font-bold text-gray-800"><?= htmlspecialchars($nama_pelanggan) ?></p>
                <p class="text-sm text-gray-500">Pelanggan Setia Catering Rizky</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-400 uppercase font-bold">ID Transaksi Cloud:</p>
                <p class="text-sm text-gray-800">#<?= $data_pesanan['id_pesanan'] ?></p>
                <p class="mt-2 text-xs text-gray-400 uppercase font-bold">Status Pembayaran:</p>
                <p class="text-sm font-bold text-green-600"><?= strtoupper($data_pesanan['status_pembayaran']) ?></p>
            </div>
        </div>

        <table class="w-full mb-8">
            <thead>
                <tr class="border-b-2 border-gray-100 text-left">
                    <th class="py-3 text-sm font-bold text-gray-600">Deskripsi Paket</th>
                    <th class="py-3 text-sm font-bold text-gray-600 text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-gray-50">
                    <td class="py-4 text-gray-800 text-sm"><?= htmlspecialchars($data_pesanan['tipe_pesanan'] ?? 'Pemesanan Paket Katering') ?></td>
                    <td class="py-4 text-gray-800 text-sm text-right font-bold">Rp <?= number_format($data_pesanan['total_bayar'], 0, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <div class="flex justify-end">
            <div class="w-1/2">
                <div class="flex justify-between py-2 border-t-2 border-gray-800">
                    <span class="font-bold text-gray-800">Total Bayar</span>
                    <span class="font-bold text-orange-600 text-lg">Rp <?= number_format($data_pesanan['total_bayar'], 0, ',', '.') ?></span>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center text-xs text-gray-400">
            <p>Bukti pembayaran ini sah dan dihasilkan otomatis oleh cloud system **Catering Rizky**.</p>
        </div>
    </div>

    <div class="max-w-3xl mx-auto mt-6 flex justify-center space-x-4 no-print">
        <button onclick="window.print()" class="bg-gray-800 text-white px-6 py-2 rounded-xl font-bold hover:bg-gray-900 transition">
            Cetak Sekarang
        </button>
    </div>

</body>
</html>