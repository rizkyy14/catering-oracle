<?php
include '../config/database.php';
include '../config/xendit_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form checkout (misal dari dashboard pelanggan)
    $id_user = $_POST['id_user']; // ID User yang login
    $total_bayar = $_POST['total_bayar']; // Total belanja
    $tipe_pesanan = $_POST['tipe_pesanan']; // Tipe pesanan
    
    // 1. Buat External ID unik (Misal: INV-171448xxxx)
    $external_id = 'INV-' . time();

    // 2. Request ke Xendit menggunakan cURL
    $data = [
        'external_id' => $external_id,
        'amount' => (int)$total_bayar,
        'payer_email' => $email_user,
        'description' => 'Pemesanan Catering - ' . $external_id,
        'invoice_duration' => 86400, // Aktif 24 jam
       'success_redirect_url' => $protocol . $host . '/catering_native/pembayaran_sukses.php?external_id=' . $external_id
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
        $query = "INSERT INTO pesanan (ID_USER, TOTAL_BAYAR, EXTERNAL_ID, CHECKOUT_URL, STATUS_PEMBAYARAN, TIPE_PESANAN) 
              VALUES (:id_user, :total, :ext_id, :url, 'PENDING', :tipe)";

    $stmt = oci_parse($conn, $query);

    oci_bind_by_name($stmt, ":id_user", $id_user);
    oci_bind_by_name($stmt, ":total", $total_bayar);
    oci_bind_by_name($stmt, ":ext_id", $external_id);
    oci_bind_by_name($stmt, ":url", $checkout_url);
    oci_bind_by_name($stmt, ":tipe", $tipe_pesanan);

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