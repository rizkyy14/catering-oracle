<?php
include '../config/auth.php'; //
include '../config/database.php'; //

if (session_status() === PHP_SESSION_NONE) { session_start(); } //

$id_user = $_SESSION['id_user']; //

// Ambil data terbaru dari Oracle
$query_user = "SELECT * FROM USERS WHERE ID_USER = :id"; //
$stmt_user = oci_parse($conn, $query_user); //
oci_bind_by_name($stmt_user, ":id", $id_user); //
oci_execute($stmt_user); //
$user = oci_fetch_array($stmt_user, OCI_ASSOC); //
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-gray-50" x-data="{ 
    isEdit: false, 
    imageUrl: '../assets/img/profile/<?= $user['FOTO_PROFIL'] ?? 'default.png' ?>' 
}">

    <main class="pt-20 pb-12 px-6 max-w-2xl mx-auto">
        <div class="bg-white rounded-[40px] shadow-lg overflow-hidden border border-gray-100">
            <div class="h-32 bg-gradient-to-r from-orange-500 to-orange-600"></div>
            
            <form action="update_profile_logic.php" method="POST" enctype="multipart/form-data" class="px-10 pb-10">
                
                <!-- Avatar Section -->
                <div class="relative -mt-16 mb-8 flex justify-center">
                    <div class="relative group">
                        <!-- Perhatikan atribut :src yang binding ke imageUrl -->
                        <img :src="imageUrl" 
                             class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-xl">
                        
                        <!-- Icon Pensil -->
                        <label x-show="isEdit" class="absolute bottom-0 right-0 bg-white p-2 rounded-full shadow-lg cursor-pointer hover:bg-gray-100 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            <!-- Tambahkan event @change untuk update imageUrl -->
                            <input type="file" name="foto" class="hidden" 
                                   @change="imageUrl = URL.createObjectURL($event.target.files[0])">
                        </label>
                    </div>
                </div>

                <!-- Input Field tetap sama seperti sebelumnya -->
                <div class="space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Nama Lengkap</label>
                        <input type="text" name="nama" value="<?= $user['NAMA'] ?>" 
                               :readonly="!isEdit"
                               :class="isEdit ? 'bg-white border-orange-500 ring-2 ring-orange-100' : 'bg-gray-50 border-transparent cursor-default'"
                               class="w-full px-5 py-4 rounded-2xl border font-semibold text-gray-800 transition-all outline-none" required>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1 ml-1">Alamat Email</label>
                        <input type="email" name="email" value="<?= $user['EMAIL'] ?>" 
                               :readonly="!isEdit"
                               :class="isEdit ? 'bg-white border-orange-500 ring-2 ring-orange-100' : 'bg-gray-50 border-transparent cursor-default'"
                               class="w-full px-5 py-4 rounded-2xl border font-semibold text-gray-800 transition-all outline-none" required>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-6 flex space-x-4">
                        <button type="button" x-show="isEdit" 
                                @click="isEdit = false; imageUrl = '../assets/img/profile/<?= $user['FOTO_PROFIL'] ?? 'default.png' ?>'" 
                                class="flex-1 py-4 rounded-2xl font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 transition">
                            Batal
                        </button>

                        <button type="button" x-show="!isEdit" @click="isEdit = true"
                                class="flex-1 bg-gray-900 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-black transition">
                            Edit Profil
                        </button>

                        <button type="submit" name="update" x-show="isEdit"
                                class="flex-1 bg-orange-600 text-white py-4 rounded-2xl font-bold text-lg shadow-lg hover:bg-orange-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</body>
</html>