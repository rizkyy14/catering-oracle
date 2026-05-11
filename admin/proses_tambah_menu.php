<?php
include '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama   = $_POST['nama_menu'];
    $harga  = $_POST['harga'];
    $desc   = $_POST['deskripsi'];
    
    // 1. Logika Upload Gambar
    $foto_name = $_FILES['foto_menu']['name'];
    $tmp_name  = $_FILES['foto_menu']['tmp_name'];
    $size      = $_FILES['foto_menu']['size'];
    $error     = $_FILES['foto_menu']['error'];

    // Cek apakah ada file yang diupload
    if ($error === 0) {
        $ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));
        $ext_valid = ['jpg', 'jpeg', 'png'];

        // Cek ekstensi file
        if (in_array($ext, $ext_valid)) {
            // Cek ukuran (maks 2MB)
            if ($size < 2000000) {
                // Generate nama unik agar tidak bentrok
                $new_name = time() . "_" . str_replace(" ", "_", strtolower($nama)) . "." . $ext;
                $destination = "../assets/img/menu/" . $new_name;

                if (move_uploaded_file($tmp_name, $destination)) {
    
    // 2. Query Insert ke Oracle (Ubah :desc jadi :desk)
    $query = "INSERT INTO menu_catering (NAMA_MENU, HARGA, DESKRIPSI, FOTO_MENU) 
              VALUES (:nama, :harga, :desk, :foto)";
    
    $stmt = oci_parse($conn, $query);
    
    // Sesuaikan juga nama bind variabel di sini
    oci_bind_by_name($stmt, ":nama", $nama);
    oci_bind_by_name($stmt, ":harga", $harga);
    oci_bind_by_name($stmt, ":desk", $desc); // Di sini tetap pakai variabel $desc tapi bind ke :desk
    oci_bind_by_name($stmt, ":foto", $new_name);
    
    $result = oci_execute($stmt);

    if ($result) {
        echo "<script>
                alert('Menu berhasil ditambahkan!');
                window.location='index.php?page=menu';
              </script>";
    } else {
        $e = oci_error($stmt);
        echo "Gagal simpan ke database: " . $e['message'];
    }

} else {
                    echo "<script>alert('Gagal memindahkan file gambar!'); window.history.back();</script>";
                }
            } else {
                echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Format file harus JPG, JPEG, atau PNG!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Terjadi kesalahan pada upload file!'); window.history.back();</script>";
    }
}
?>