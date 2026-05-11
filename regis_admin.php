<?php
include 'config/database.php';

// Data admin yang mau dibuat
$nama  = "Muhammad Rizky Ramadhan"; // Nama kamu
$email = "rizky@gmail.com";
$pass  = "ris1234"; 
$role  = "admin";

// Proses Hashing Password (Ini yang bikin password kamu valid di login.php)
$password_hashed = password_hash($pass, PASSWORD_BCRYPT);

if (isset($_GET['gas'])) {
    $sql = "INSERT INTO users (nama, email, password, role) VALUES (:nama, :email, :pass, :role)";
    $stmt = oci_parse($conn, $sql);

    oci_bind_by_name($stmt, ":nama", $nama);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":pass", $password_hashed);
    oci_bind_by_name($stmt, ":role", $role);

    if (oci_execute($stmt)) {
        oci_commit($conn);
        echo "Admin Berhasil Dibuat!<br>";
        echo "Email: $email <br> Password: $pass";
    } else {
        $e = oci_error($stmt);
        echo "Gagal: " . $e['message'];
    }
} else {
    echo "Klik link ini untuk eksekusi: <a href='?gas=1'>Buat Admin Sekarang</a>";
}
?>