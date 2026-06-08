<?php
// File: user/set_session.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ambil ID Pesanan dari JavaScript Fetch
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id_pesanan'])) {
    // Titipkan ID Pesanan ke dalam Session lokal Laragon
    $_SESSION['id_pesanan_terakhir'] = trim($data['id_pesanan']);
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID Kosong']);
}
?>