<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form modal Alpine.js
    $id_menu   = $_POST['id_menu'];
    $nama_menu = $_POST['nama_menu'];
    $harga     = $_POST['harga'];
    $deskripsi = $_POST['deskripsi'];

    // 1. Ambil nama foto lama dari database
    // Ini penting buat proses unlink (hapus file) biar storage Laragon gak bengkak
    $query_foto = "SELECT FOTO_MENU FROM menu_catering WHERE ID_MENU = :id";
    $stmt_foto = oci_parse($conn, $query_foto);
    oci_bind_by_name($stmt_foto, ":id", $id_menu);
    oci_execute($stmt_foto);
    $row_foto = oci_fetch_array($stmt_foto, OCI_ASSOC);
    $foto_lama = $row_foto['FOTO_MENU'];

    // 2. Logika Update Foto
    if (!empty($_FILES['foto_menu']['name'])) {
        $file_name = $_FILES['foto_menu']['name'];
        $tmp_name  = $_FILES['foto_menu']['tmp_name'];
        $ext       = pathinfo($file_name, PATHINFO_EXTENSION);
        
        // Nama file baru: timestamp + nama menu (biar unik)
        $foto_final = time() . "_" . str_replace(" ", "_", strtolower($nama_menu)) . "." . $ext;
        $tujuan     = "../assets/img/menu/" . $foto_final;

        if (move_uploaded_file($tmp_name, $tujuan)) {
            // Hapus file fisik foto lama jika ada di folder
            if ($foto_lama && file_exists("../assets/img/menu/" . $foto_lama)) {
                unlink("../assets/img/menu/" . $foto_lama);
            }
        }
    } else {
        // Kalau admin gak upload foto baru, tetap pakai foto yang lama
        $foto_final = $foto_lama;
    }

    // 3. Eksekusi UPDATE ke Oracle SQL Developer
    $query_update = "UPDATE menu_catering 
                     SET NAMA_MENU = :nama, 
                         HARGA = :harga, 
                         DESKRIPSI = :desk, 
                         FOTO_MENU = :foto 
                     WHERE ID_MENU = :id";

    $stmt_update = oci_parse($conn, $query_update);

    // Bind parameter untuk keamanan (mencegah SQL Injection)
    oci_bind_by_name($stmt_update, ":nama", $nama_menu);
    oci_bind_by_name($stmt_update, ":harga", $harga);
    oci_bind_by_name($stmt_update, ":desk", $deskripsi);
    oci_bind_by_name($stmt_update, ":foto", $foto_final);
    oci_bind_by_name($stmt_update, ":id", $id_menu);

    $result = oci_execute($stmt_update);

    if ($result) {
        // Berhasil, balik ke halaman menu
        echo "<script>
                alert('Menu berhasil diupdate!');
                window.location='index.php?page=menu';
              </script>";
    } else {
        $e = oci_error($stmt_update);
        echo "Aduh, error pas update: " . $e['message'];
    }
}
?>