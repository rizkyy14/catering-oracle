<?php
// File: user/webhook_xendit.php

ob_start();

// 1. Ambil koneksi database lokal Laragon
// Karena file ini ada di folder 'user/', kita naik satu tingkat ke folder utama untuk panggil config
include '../config/database.php';

// 2. Tangkap data JSON mentah dari server Xendit
$rawRequest = file_get_contents('php://input');
$requestData = json_decode($rawRequest, true);

// Tetap catat log untuk memantau data masuk
file_put_contents('log_webhook.txt', "[" . date('Y-m-d H:i:s') . "] Webhook Masuk: " . $rawRequest . PHP_EOL, FILE_APPEND);

if (isset($requestData['external_id']) && isset($requestData['status'])) {
    
    $external_id = trim($requestData['external_id']);
    $xendit_status = $requestData['status']; 

    $final_status = 'PAID';
    if ($xendit_status === 'EXPIRED') {
        $final_status = 'EXPIRED';
    }

    // 3. UPDATE LANGSUNG KE DATABASE LOKAL LARAGON (Bypass Oracle Cloud)
    $query = "UPDATE pesanan SET STATUS_PEMBAYARAN = :status WHERE TRIM(EXTERNAL_ID) = :ext_id";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ":status", $final_status);
    oci_bind_by_name($stmt, ":ext_id", $external_id);
    
    $execute = oci_execute($stmt);
    
    if ($execute) {
        oci_commit($conn); // Kunci data di database lokal
        $db_status = "Sukses Lokal";
    } else {
        $e = oci_error($stmt);
        file_put_contents('log_webhook.txt', "[" . date('Y-m-d H:i:s') . "] GAGAL UPDATE LOKAL: " . $e['message'] . PHP_EOL, FILE_APPEND);
        $db_status = "Gagal Lokal";
    }
    
    ob_end_clean();

    // Berikan respon sukses 200 ke Xendit dengan bangga
    header('Content-Type: application/json');
    http_response_code(200);
    echo json_encode([
        "message" => "Webhook berhasil diproses secara lokal", 
        "local_db_status" => $db_status
    ]);
    exit();

} else {
    ob_end_clean();
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["message" => "Format data salah atau tidak valid"]);
    exit();
}
?>