<?php
include 'config/database.php';

if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash untuk keamanan
    
    // Konfigurasi Upload Foto
    $nama_file = $_FILES['foto']['name'];
    $tmp_file  = $_FILES['foto']['tmp_name'];
    $ukuran    = $_FILES['foto']['size'];
    $error     = $_FILES['foto']['error'];
    
    // Set foto default jika user tidak upload apa-apa
    $foto_final = 'default.png';

    if ($nama_file != "") {
        $ekstensi_valid = ['jpg', 'jpeg', 'png'];
        $ekstensi_file  = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        // Validasi: Cek ekstensi dan ukuran (maks 2MB)
        if (in_array($ekstensi_file, $ekstensi_valid) && $ukuran <= 2000000) {
            // Rename file agar unik (misal: 1714486200_rizky.jpg)
            $foto_final = time() . '_' . str_replace(' ', '_', $nama) . '.' . $ekstensi_file;
            $path_tujuan = 'assets/img/profile/' . $foto_final;

            // Pindahkan file ke folder Laragon kamu
            move_uploaded_file($tmp_file, $path_tujuan);
        } else {
            echo "<script>alert('Format file salah atau ukuran terlalu besar!'); window.history.back();</script>";
            exit;
        }
    }

    // Insert ke Database Oracle
    $query = "INSERT INTO users (nama, email, password, foto_profil, role) 
              VALUES (:nama, :email, :pass, :foto, 'pelanggan')";
    
    $stmt = oci_parse($conn, $query);

    oci_bind_by_name($stmt, ":nama", $nama);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":pass", $password);
    oci_bind_by_name($stmt, ":foto", $foto_final);

    $eksekusi = oci_execute($stmt);

    if ($eksekusi) {
        echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='index.php';</script>";
    } else {
        $e = oci_error($stmt);
        echo "Gagal mendaftar: " . $e['message'];
    }
}
?>