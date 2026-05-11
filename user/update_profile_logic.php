<?php
include '../config/auth.php';
include '../config/database.php';

if (isset($_POST['update'])) {
    $id_user = $_SESSION['id_user'];
    $nama    = $_POST['nama'];
    $email   = $_POST['email'];
    
    // 1. Ambil data lama untuk pengecekan foto
    $query_old = "SELECT FOTO_PROFIL FROM USERS WHERE ID_USER = :id";
    $stmt_old  = oci_parse($conn, $query_old);
    oci_bind_by_name($stmt_old, ":id", $id_user);
    oci_execute($stmt_old);
    $old_data  = oci_fetch_array($stmt_old, OCI_ASSOC);
    $foto_lama = $old_data['FOTO_PROFIL'];

    // 2. Logika Upload Foto Baru
    $nama_file = $_FILES['foto']['name'];
    $tmp_file  = $_FILES['foto']['tmp_name'];
    
    if ($nama_file != "") {
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        
        if (in_array($ekstensi_file, $ekstensi_valid)) {
            // Beri nama unik agar tidak bentrok
            $foto_baru = time() . '_' . str_replace(' ', '_', $nama) . '.' . $ekstensi_file;
            $path_tujuan = '../assets/img/profile/' . $foto_baru;

            if (move_uploaded_file($tmp_file, $path_tujuan)) {
                // Hapus foto lama dari folder (kecuali default.png)
                if ($foto_lama != 'default.png' && file_exists('../assets/img/profile/' . $foto_lama)) {
                    unlink('../assets/img/profile/' . $foto_lama);
                }
                $foto_final = $foto_baru;
            }
        } else {
            echo "<script>alert('Format foto tidak didukung!'); window.history.back();</script>";
            exit;
        }
    } else {
        // Jika tidak upload foto baru, tetap pakai foto yang lama
        $foto_final = $foto_lama;
    }

    // 3. Update ke Database Oracle
    $query_update = "UPDATE USERS SET NAMA = :nama, EMAIL = :email, FOTO_PROFIL = :foto WHERE ID_USER = :id";
    $stmt_update  = oci_parse($conn, $query_update);

    oci_bind_by_name($stmt_update, ":nama", $nama);
    oci_bind_by_name($stmt_update, ":email", $email);
    oci_bind_by_name($stmt_update, ":foto", $foto_final);
    oci_bind_by_name($stmt_update, ":id", $id_user);

    $result = oci_execute($stmt_update);

    if ($result) {
        // Update session agar nama di navbar langsung berubah
        $_SESSION['nama'] = $nama;
        echo "<script>alert('Profil berhasil diperbarui!'); window.location='profil.php';</script>";
    } else {
        $e = oci_error($stmt_update);
        echo "Gagal update: " . $e['message'];
    }
}
?>