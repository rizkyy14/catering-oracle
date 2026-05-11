<?php
include '../config/auth.php';
include '../config/database.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil halaman dari URL, default-nya adalah 'overview'
$page = $_GET['page'] ?? 'overview';
$id_admin = $_SESSION['id_user'];

// Query Nama Admin (Sesuai diskusi sebelumnya)
$s_admin = oci_parse($conn, "SELECT NAMA FROM USERS WHERE ID_USER = :id");
oci_bind_by_name($s_admin, ":id", $id_admin);
oci_execute($s_admin);
$admin_data = oci_fetch_array($s_admin, OCI_ASSOC);
$nama_admin = $admin_data['NAMA'] ?? 'Administrator';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Catering Rizky</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-50 flex" x-data="{ addModal: false }">

    <!-- SIDEBAR (Tetap Tampil) -->
    <aside class="w-64 min-h-screen bg-slate-900 text-white p-6 sticky top-0">
        <h1 class="text-xl font-bold mb-10 text-indigo-400">Admin<span class="text-white">Panel</span></h1>
        <nav class="space-y-4">
            <a href="index.php?page=overview" class="block p-4 rounded-2xl transition <?= $page == 'overview' ? 'bg-indigo-600 font-bold' : 'hover:bg-slate-800' ?>">Dashboard</a>
            <a href="index.php?page=menu" class="block p-4 rounded-2xl transition <?= $page == 'menu' ? 'bg-indigo-600 font-bold' : 'hover:bg-slate-800' ?>">Kelola Menu</a>
            <a href="index.php?page=pesanan" class="block p-4 rounded-2xl transition <?= $page == 'pesanan' ? 'bg-indigo-600 font-bold' : 'hover:bg-slate-800' ?>">Daftar Pesanan</a>
            <hr class="border-slate-800 my-6">
            <a href="../logout.php" class="block p-4 text-red-400 hover:bg-red-500/10 rounded-2xl transition font-bold">Keluar</a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-10">
        <!-- Header Profil (Tetap Tampil) -->
        <header class="flex justify-between items-center mb-10">
            <div>
                <h2 class="text-3xl font-bold text-slate-800">Admin Area</h2>
                <p class="text-slate-500">Log masuk sebagai: <span class="font-bold text-indigo-600"><?= $nama_admin ?></span></p>
            </div>
        </header>

        <!-- KONTEN DINAMIS BERDASARKAN CASE -->
        <?php 
        switch ($page) {
            case 'menu':
                include 'sections/menu.php'; // Kita pisahkan isinya ke folder sections agar tidak berantakan
                break;
            
            case 'pesanan':
                include 'sections/pesanan.php';
                break;

            case 'overview':
            default:
                include 'sections/overview.php';
                break;
        }
        ?>
    </main>

</body>
</html>