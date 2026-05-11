<?php
session_start();
include 'config/database.php';

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
        $_SESSION['role'] = $user['ROLE'];

        if ($user['ROLE'] == 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: user/index.php");
        }
    } else {
        echo "Email atau Password salah!";
    }
}
?>