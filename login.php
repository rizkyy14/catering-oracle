<?php
session_start();
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
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = :email";
    $stmt = oci_parse($conn, $sql);
    oci_bind_by_name($stmt, ":email", $email);
    oci_execute($stmt);

    $user = oci_fetch_array($stmt, OCI_ASSOC);

    if ($user && password_verify($pass, $user['PASSWORD'])) {
        $_SESSION['id_user'] = $user['ID_USER'];
        $_SESSION['nama']    = $user['NAMA'];
        $_SESSION['role']    = $user['ROLE'];

        $location = ($user['ROLE'] == 'admin') ? 'admin/index.php' : 'user/index.php';

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: 'Selamat datang kembali, " . $user['NAMA'] . "!',
                showConfirmButton: false,
                timer: 2000
            }).then(() => { window.location.href = '$location'; });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: 'Email atau Password salah!',
                confirmButtonColor: '#ea580c'
            }).then(() => { window.history.back(); });
        </script>";
    }
}
?>
</body>
</html>