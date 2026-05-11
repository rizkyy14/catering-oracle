<?php
// Cek apakah session sudah aktif sebelum memanggil session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cek_admin() {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php?pesan=bukan_admin");
        exit();
    }
}

function cek_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: index.php?pesan=belum_login");
        exit();
    }
}
?>