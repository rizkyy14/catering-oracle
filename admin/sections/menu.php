<?php
// Pastikan koneksi tersedia dari dashboard.php
if (!isset($conn)) {
    die("Akses database tidak ditemukan.");
}

// Ambil Data Menu dari Oracle
$query_menu = "SELECT * FROM menu_catering ORDER BY id_menu DESC";
$stmt_menu = oci_parse($conn, $query_menu);
oci_execute($stmt_menu);
?>

<div class="animate-in fade-in duration-500" x-data="{ addModal: false, editModal: false, editData: {} }">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h3 class="text-2xl font-bold text-slate-800">Daftar Menu Catering</h3>
            <p class="text-sm text-slate-500">Kelola hidangan secara real-time.</p>
        </div>
        <button @click="addModal = true" class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-bold shadow-lg hover:bg-indigo-700 transition flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Menu Baru
        </button>
    </div>

    <!-- Tabel Menu -->
    <div class="bg-white rounded-[32px] shadow-sm border border-slate-100 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-100">
                <tr>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Visual</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Info Menu</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Harga Satuan</th>
                    <th class="px-6 py-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php while ($row = oci_fetch_array($stmt_menu, OCI_ASSOC)): ?>
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="px-6 py-4">
                        <img src="../assets/img/menu/<?= $row['FOTO_MENU'] ?>" class="w-16 h-16 rounded-xl object-cover border border-slate-100">
                    </td>
                    <td class="px-6 py-4">
                        <p class="font-bold text-slate-800"><?= $row['NAMA_MENU'] ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-bold text-indigo-600">Rp <?= number_format($row['HARGA'], 0, ',', '.') ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center space-x-2">
                            <!-- Tombol Edit: Kirim data ke object editData Alpine -->
                            <button @click="editData = {
                                id: '<?= $row['ID_MENU'] ?>',
                                nama: '<?= addslashes($row['NAMA_MENU']) ?>',
                                harga: '<?= $row['HARGA'] ?>',
                                deskripsi: '<?= addslashes($row['DESKRIPSI']) ?>',
                                foto: '<?= $row['FOTO_MENU'] ?>'
                            }; editModal = true" 
                            class="p-2 text-amber-600 bg-amber-50 rounded-xl hover:bg-amber-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <a href="hapus_menu.php?id=<?= $row['ID_MENU'] ?>" onclick="return confirm('Hapus hidangan ini?')" class="p-2 text-red-600 bg-red-50 rounded-xl hover:bg-red-100 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL EDIT MENU -->
    <div x-show="editModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition.opacity>
        <div @click.away="editModal = false" class="bg-white w-full max-w-lg p-10 rounded-[40px] shadow-2xl relative">
            <button @click="editModal = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-800 text-2xl">&times;</button>
            
            <h3 class="text-2xl font-bold mb-8 text-slate-800">Edit Hidangan</h3>
            
            <form action="proses_edit_menu.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="id_menu" :value="editData.id">
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Nama Masakan</label>
                    <input type="text" name="nama_menu" :value="editData.nama" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Harga (Rp)</label>
                    <input type="number" name="harga" :value="editData.harga" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Deskripsi Singkat</label>
                    <textarea name="deskripsi" x-text="editData.deskripsi" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 h-28 transition" required></textarea>
                </div>

                <div class="flex items-center space-x-4 bg-slate-50 p-4 rounded-2xl">
                    <img :src="'../assets/img/menu/' + editData.foto" class="w-12 h-12 rounded-lg object-cover">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Ganti Foto (Opsional)</label>
                        <input type="file" name="foto_menu" class="text-xs text-slate-500 file:mr-3 file:py-1 file:px-3 file:rounded-full file:border-0 file:bg-indigo-50 file:text-indigo-600">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-indigo-700 transition transform active:scale-95">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
    <!-- Modal Tambah Menu (Alpine.js) -->
<div x-show="addModal" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak x-transition.opacity>
    <div @click.away="addModal = false" class="bg-white w-full max-w-lg p-10 rounded-[40px] shadow-2xl relative">
        <button @click="addModal = false" class="absolute top-8 right-8 text-slate-400 hover:text-slate-800 text-2xl">&times;</button>
        
        <h3 class="text-2xl font-bold mb-2 text-slate-800">Hidangan Baru</h3>
        <p class="text-sm text-slate-500 mb-8">Masukkan detail menu catering terbaru kamu.</p>
        
        <form action="proses_tambah_menu.php" method="POST" enctype="multipart/form-data" class="space-y-5">
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Nama Masakan</label>
                <input type="text" name="nama_menu" placeholder="Contoh: Nasi Kotak Ayam Bakar" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Harga (Rp)</label>
                    <input type="number" name="harga" placeholder="25000" class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Foto Menu</label>
                    <input type="file" name="foto_menu" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-2 ml-1">Deskripsi Singkat</label>
                <textarea name="deskripsi" placeholder="Jelaskan isi menu (misal: Nasi, Ayam, Sambal, Lalapan)..." class="w-full px-5 py-4 rounded-2xl bg-slate-50 border-none outline-none focus:ring-2 focus:ring-indigo-500 h-28 transition" required></textarea>
            </div>

            <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-indigo-700 transition transform active:scale-95 mt-4">
                Simpan ke Database
            </button>
        </form>
    </div>
</div>
</div>

