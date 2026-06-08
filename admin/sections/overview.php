<?php
// File: admin/sections/overview.php
if (!isset($conn)) {
    die("Akses database tidak ditemukan.");
}

// --- HITUNG TOTAL PELANGGAN (Dari Database Lokal Laragon) ---
$q_users = "SELECT COUNT(*) AS TOTAL FROM users WHERE ROLE = 'pelanggan'";
$s_users = oci_parse($conn, $q_users);
oci_execute($s_users);
$fetch_users = oci_fetch_array($s_users, OCI_ASSOC);
$total_users = $fetch_users['TOTAL'] ?? 0;

$all_pesanan_raw = isset($data_apex['items']) ? $data_apex['items'] : ($data_apex ?? []);


// --- PROSES NORMALISASI DATA (Paksa Semua Key Menjadi Lowercase) ---
$all_pesanan = [];
if (is_array($all_pesanan_raw) && !empty($all_pesanan_raw)) {
    foreach ($all_pesanan_raw as $raw_item) {
        $all_pesanan[] = array_change_key_case($raw_item, CASE_LOWER);
    }
}

// Balik urutan array agar ID paling baru muncul di atas
$all_pesanan = array_reverse($all_pesanan);

// Hitung Statistik
$total_orders = count($all_pesanan);
$total_income = 0;
$recent_orders = [];

foreach ($all_pesanan as $p) {
    $status_pembayaran = isset($p['status_pembayaran']) ? strtoupper($p['status_pembayaran']) : 'PENDING';
    $total_bayar = $p['total_bayar'] ?? 0;

    if ($status_pembayaran === 'PAID') {
        $total_income += $total_bayar;
    }
    // Ambil maksimal 5 pesanan terbaru untuk ringkasan dashboard
    if (count($recent_orders) < 5) {
        $recent_orders[] = $p;
    }
}
?>

<div class="space-y-8 animate-in fade-in duration-500">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Pesanan</p>
            <h3 class="text-4xl font-bold text-slate-800"><?= $total_orders ?></h3>
            <p class="text-indigo-500 text-xs mt-2 font-medium">Semua transaksi masuk cloud</p>
        </div>

        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Pendapatan</p>
            <h3 class="text-4xl font-bold text-slate-800">Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
            <p class="text-emerald-500 text-xs mt-2 font-medium">Dana terkunci status PAID</p>
        </div>

        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Pelanggan Aktif</p>
            <h3 class="text-4xl font-bold text-slate-800"><?= $total_users ?></h3>
            <p class="text-blue-500 text-xs mt-2 font-medium">User terdaftar sistem lokal</p>
        </div>
    </div>

    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
            <h4 class="font-bold text-slate-800">5 Pesanan Terakhir (Cloud Server)</h4>
            <a href="index.php?page=pesanan" class="text-xs font-bold text-orange-600 hover:underline">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">ID</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">External ID Xendit</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if (empty($recent_orders)): ?>
                    <tr>
                        <td colspan="4" class="px-8 py-6 text-sm text-center text-slate-400 italic">Belum ada data transaksi di Cloud server.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $row): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-8 py-4 text-sm font-bold text-slate-700">#<?= $row['id_pesanan'] ?></td>
                            <td class="px-8 py-4 text-sm text-slate-500 font-mono"><?= htmlspecialchars($row['external_id'] ?? '-') ?></td>
                            <td class="px-8 py-4 text-sm font-bold text-slate-800">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                            <td class="px-8 py-4">
                                <?php if (isset($row['status_pembayaran']) && strtoupper($row['status_pembayaran']) == 'PAID'): ?>
                                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase">Paid</span>
                                <?php else: ?>
                                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-600 text-[10px] font-bold uppercase">Pending</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>