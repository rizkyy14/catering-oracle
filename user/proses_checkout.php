<?php
include '../config/database.php';
include '../config/xendit_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form checkout (misal dari dashboard pelanggan)
    $id_user = $_POST['id_user']; // ID User yang login
    $total_bayar = $_POST['total_bayar']; // Total belanja
    
    // 1. Buat External ID unik (Misal: INV-171448xxxx)
    $external_id = 'INV-' . time();

    // 2. Request ke Xendit menggunakan cURL
    $data = [
        'external_id' => $external_id,
        'amount' => (int)$total_bayar,
        'payer_email' => $_POST['email_pelanggan'], // Ambil dari input atau tabel user
        'description' => 'Pembayaran Catering Rizky - ' . $external_id,
        'invoice_duration' => 86400, // Aktif 24 Jam
        'success_redirect_url' => 'http://localhost/catering_native/pembayaran_sukses.php'
    ];

    $payload = json_encode($data);

    $ch = curl_init('https://api.xendit.co/v2/invoices');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, $xendit_key . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);

    if (isset($result['invoice_url'])) {
        $checkout_url = $result['invoice_url'];

        // 3. Simpan ke Database Oracle
        // Sesuaikan dengan kolom: ID_USER, TGL_PESAN, TOTAL_BAYAR, EXTERNAL_ID, CHECKOUT_URL, STATUS_PEMBAYARAN
        $query = "INSERT INTO pesanan (ID_USER, TGL_PESAN, TOTAL_BAYAR, EXTERNAL_ID, CHECKOUT_URL, STATUS_PEMBAYARAN) 
                  VALUES (:id_u, CURRENT_TIMESTAMP, :total, :ext_id, :url, 'PENDING')";

        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":id_u", $id_user);
        oci_bind_by_name($stmt, ":total", $total_bayar);
        oci_bind_by_name($stmt, ":ext_id", $external_id);
        oci_bind_by_name($stmt, ":url", $checkout_url);

        $exec = oci_execute($stmt);

        if ($exec) {
            // Langsung arahkan pelanggan ke halaman bayar Xendit
            header("Location: " . $checkout_url);
            exit;
        } else {
            echo "Gagal simpan pesanan ke database.";
        }
    } else {
        echo "Gagal terhubung ke Xendit: " . ($result['message'] ?? 'Error Unknown');
    }
}
?>