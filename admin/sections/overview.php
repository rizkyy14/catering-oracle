<?php
// Pastikan file ini dipanggil melalui dashboard.php sehingga variabel $conn sudah tersedia
if (!isset($conn)) {
    die("Akses database tidak ditemukan.");
}

// 1. Hitung Total Pesanan
$q_orders = "SELECT COUNT(*) AS TOTAL FROM pesanan";
$s_orders = oci_parse($conn, $q_orders);
oci_execute($s_orders);
$total_orders = oci_fetch_array($s_orders, OCI_ASSOC)['TOTAL'];

// 2. Hitung Total Pendapatan (Hanya yang statusnya 'PAID')
$q_income = "SELECT SUM(TOTAL_BAYAR) AS TOTAL FROM pesanan WHERE STATUS_PEMBAYARAN = 'PAID'";
$s_income = oci_parse($conn, $q_income);
oci_execute($s_income);
$fetch_income = oci_fetch_array($s_income, OCI_ASSOC);
$total_income = $fetch_income['TOTAL'] ?? 0;

// 3. Hitung Total Pelanggan (Role 'pelanggan')
$q_users = "SELECT COUNT(*) AS TOTAL FROM users WHERE ROLE = 'pelanggan'";
$s_users = oci_parse($conn, $q_users);
oci_execute($s_users);
$total_users = oci_fetch_array($s_users, OCI_ASSOC)['TOTAL'];

// 4. Ambil 5 Pesanan Terbaru untuk Tabel Ringkasan
$q_recent = "SELECT * FROM (SELECT * FROM pesanan ORDER BY TGL_PESAN DESC) WHERE ROWNUM <= 5";
$s_recent = oci_parse($conn, $q_recent);
oci_execute($s_recent);
?>

<div class="space-y-8 animate-in fade-in duration-500">
    <!-- Row Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Total Pesanan -->
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Pesanan</p>
            <h3 class="text-4xl font-bold text-slate-800"><?= $total_orders ?></h3>
            <p class="text-indigo-500 text-xs mt-2 font-medium">Semua transaksi masuk</p>
        </div>

        <!-- Card Pendapatan -->
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Total Pendapatan</p>
            <h3 class="text-4xl font-bold text-slate-800">Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
            <p class="text-emerald-500 text-xs mt-2 font-medium">Dana yang berhasil ditarik</p>
        </div>

        <!-- Card Pelanggan -->
        <div class="bg-white p-8 rounded-[32px] shadow-sm border border-slate-100 hover:shadow-md transition">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mb-1">Pelanggan Aktif</p>
            <h3 class="text-4xl font-bold text-slate-800"><?= $total_users ?></h3>
            <p class="text-blue-500 text-xs mt-2 font-medium">User terdaftar sistem</p>
        </div>
    </div>

    <!-- Row Tabel Pesanan Terbaru -->
    <div class="bg-white rounded-[40px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 flex justify-between items-center">
            <h4 class="font-bold text-slate-800">5 Pesanan Terakhir</h4>
            <a href="dashboard.php?page=pesanan" class="text-xs font-bold text-indigo-600 hover:underline text-orange-600">Lihat Semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">ID</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total</th>
                        <th class="px-8 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while ($row = oci_fetch_array($s_recent, OCI_ASSOC)): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-8 py-4 text-sm font-bold text-slate-700">#<?= $row['ID_PESANAN'] ?></td>
                        <td class="px-8 py-4 text-sm text-slate-500"><?= $row['TGL_PESAN'] ?></td>
                        <td class="px-8 py-4 text-sm font-bold text-slate-800">Rp <?= number_format($row['TOTAL_BAYAR'], 0, ',', '.') ?></td>
                        <td class="px-8 py-4">
                            <?php if ($row['STATUS_PEMBAYARAN'] == 'PAID'): ?>
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-600 text-[10px] font-bold uppercase">Paid</span>
                            <?php else: ?>
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-600 text-[10px] font-bold uppercase">Pending</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>