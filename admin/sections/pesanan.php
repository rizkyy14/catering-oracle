<?php
// File: admin/sections/pesanan.php
if (!isset($conn)) {
    die("Akses database tidak ditemukan.");
}

$url_apex_pesanan = "https://oracleapex.com/ords/rizky_catering/catering/pesanan?limit=500";
$ch = curl_init($url_apex_pesanan);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

// --- POTONG KODE DI SINI UNTUK DEBUGGING ---
echo "<div class='bg-slate-900 text-emerald-400 p-6 rounded-2xl font-mono text-xs overflow-scroll h-64 mb-6'>";
echo "<strong>DEBUG MODE - RESPONSE DARI CLOUD APEX:</strong><br><br>";
echo htmlspecialchars($response);
echo "</div>";
// -------------------------------------------

$data_apex = json_decode($response, true);
$list_pesanan_raw = $data_apex['items'] ?? [];

// --- PROSES NORMALISASI DATA (Paksa Semua Key Menjadi Lowercase) ---
$list_pesanan = [];
if (!empty($list_pesanan_raw)) {
    foreach ($list_pesanan_raw as $raw_item) {
        $list_pesanan[] = array_change_key_case($raw_item, CASE_LOWER);
    }
}

// Balik urutan agar pesanan paling baru langsung tampil di paling atas tabel admin
$list_pesanan = array_reverse($list_pesanan);
?>

<div class="animate-in fade-in duration-500">
    <div class="mb-8">
        <h3 class="text-2xl font-bold text-slate-800">Daftar Transaksi Pesanan (Cloud Data)</h3>
        <p class="text-sm text-slate-500">Monitoring status pembayaran dan invoice pelanggan secara real-time.</p>
    </div>

    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">ID / External ID</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pelanggan</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tipe Pesanan</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Bayar</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
    <?php if (empty($list_pesanan)): ?>
    <tr>
        <td colspan="6" class="px-6 py-8 text-sm text-center text-slate-400 italic">Data kosong atau gagal terhubung ke Oracle Cloud.</td>
    </tr>
    <?php else: ?>
        <?php foreach ($list_pesanan as $row): ?>
        <?php
            $current_id_user = $row['id_user'] ?? 0;
            $query_user = "SELECT NAMA FROM users WHERE ID_USER = :id_user";
            $stmt_user = oci_parse($conn, $query_user);
            oci_bind_by_name($stmt_user, ":id_user", $current_id_user);
            oci_execute($stmt_user);
            
            $user_data = oci_fetch_array($stmt_user, OCI_ASSOC);
            $nama_pelanggan = $user_data['NAMA'] ?? 'User tidak ditemukan';
        ?>
        <tr class="hover:bg-slate-50/50 transition">
            <td class="px-6 py-4">
                <p class="font-bold text-slate-800">#<?= $row['id_pesanan'] ?></p>
                <p class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($row['external_id'] ?? '-') ?></p>
            </td>
            <td class="px-6 py-4">
                <p class="font-medium text-slate-700"><?= htmlspecialchars($nama_pelanggan) ?></p>
                <p class="text-[10px] text-slate-400">ID Cloud User: <?= $current_id_user ?></p>
            </td>
            <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                <?= htmlspecialchars($row['tipe_pesanan'] ?? 'Paket Katering') ?>
            </td>
            <td class="px-6 py-4 font-bold text-indigo-600">
                Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?>
            </td>
            <td class="px-6 py-4">
                <?php 
                $status = isset($row['status_pembayaran']) ? strtoupper($row['status_pembayaran']) : 'PENDING';
                $badgeClass = match($status) {
                    'PAID'    => 'text-emerald-600 bg-emerald-50',
                    'PENDING' => 'text-amber-600 bg-amber-50',
                    'EXPIRED' => 'text-red-600 bg-red-50',
                    default   => 'text-slate-500 bg-slate-50',
                };
                ?>
                <span class="px-3 py-1 rounded-full text-[10px] font-bold <?= $badgeClass ?>">
                    <?= $status ?>
                </span>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex justify-center space-x-2">
                    <?php if ($status == 'PENDING' && !empty($row['checkout_url'])): ?>
                    <a href="<?= $row['checkout_url'] ?>" target="_blank" class="p-2 text-indigo-600 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition" title="Bayar Sekarang">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($status == 'PAID'): ?>
                    <a href="cetak_pdf.php?id=<?= $row['id_pesanan'] ?>" target="_blank" class="p-2 text-emerald-600 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition" title="Cetak Invoice PDF">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                        </svg>
                    </a>
                    <?php else: ?>
                    <button class="p-2 text-slate-300 bg-slate-50 rounded-xl cursor-not-allowed" title="Belum dibayar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</tbody>
        </table>
    </div>
</div>