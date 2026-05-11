<?php
include 'config/database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body>
<?php
if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // 1. CEK APAKAH EMAIL SUDAH TERDAFTAR
    $query_cek = "SELECT * FROM users WHERE email = :email";
    $stmt_cek = oci_parse($conn, $query_cek);
    oci_bind_by_name($stmt_cek, ":email", $email);
    oci_execute($stmt_cek);
    
    $user_ada = oci_fetch_array($stmt_cek, OCI_ASSOC);

    if ($user_ada) {
        // Jika email sudah ada di database
        echo "<script>
            Swal.fire({
                icon: 'warning',
                title: 'Email Sudah Terdaftar!',
                text: 'Silakan gunakan email lain atau langsung login.',
                confirmButtonColor: '#ea580c'
            }).then(() => { window.history.back(); });
        </script>";
        exit; // Hentikan proses pendaftaran
    }

    // 2. LOGIKA UPLOAD FOTO (Sama seperti sebelumnya)
    $nama_file = $_FILES['foto']['name'];
    $tmp_file  = $_FILES['foto']['tmp_name'];
    $foto_final = 'default.png';

    if ($nama_file != "") {
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $foto_final = time() . '_' . str_replace(' ', '_', $nama) . '.' . $ekstensi_file;
        move_uploaded_file($tmp_file, 'assets/img/profile/' . $foto_final);
    }

    // 3. INSERT JIKA EMAIL BELUM ADA
    $query = "INSERT INTO users (nama, email, password, foto_profil, role) 
              VALUES (:nama, :email, :pass, :foto, 'pelanggan')";
    
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":nama", $nama);
    oci_bind_by_name($stmt, ":email", $email);
    oci_bind_by_name($stmt, ":pass", $password);
    oci_bind_by_name($stmt, ":foto", $foto_final);

    $eksekusi = oci_execute($stmt);

    if ($eksekusi) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Registrasi Berhasil!',
                text: 'Akun kamu sudah aktif, silakan login.',
                confirmButtonColor: '#ea580c'
            }).then(() => { window.location.href = 'index.php'; });
        </script>";
    } else {
        $e = oci_error($stmt);
        echo "Gagal mendaftar: " . $e['message'];
    }
}
?>
</body>
</html>