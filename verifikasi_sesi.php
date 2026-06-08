<?php
session_start();

if (isset($_POST['password_input']) && isset($_POST['password_hash'])) {
    $pass_input = $_POST['password_input'];
    $pass_hash  = $_POST['password_hash'];

    // Eksekusi verifikasi enkripsi Bcrypt PHP secara aman di backend
    if (password_verify($pass_input, $pass_hash)) {
        $_SESSION['id_user'] = $_POST['id_user'];
        $_SESSION['nama']    = $_POST['nama'];
        $_SESSION['role']    = $_POST['role'];

        $location = ($_POST['role'] == 'admin') ? 'admin/index.php' : 'user/index.php';
        
        // Sesuaikan path jika letak file ini ada di dalam folder 'user/' atau root
        // Jika file ini di folder utama, biarkan $location langsung. 
        // Jika di dalam subfolder user, sesuaikan jadi '../admin/index.php' atau '../user/index.php'
        
        header("Location: " . $location);
        exit();
    }
}

// Jika bypass gagal / password salah, tendang balik
echo "<!DOCTYPE html>
<html>
<head><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script></head>
<body>
<script>
    Swal.fire({
        icon: 'error',
        title: 'Login Gagal',
        text: 'Email atau Password salah!',
        confirmButtonColor: '#ea580c'
    }).then(() => { window.location.href = 'index.php'; });
</script>
</body>
</html>";
?>