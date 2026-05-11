<?php
include '../config/auth.php';
include '../config/database.php';
include '../config/xendit_config.php'; // Pastikan file ini berisi $xendit_key

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_user      = $_SESSION['id_user'];
    $harga_satuan = $_POST['harga_satuan'];
    $jumlah       = $_POST['jumlah'];
    $email_user   = $_SESSION['email'] ?? 'pelanggan@example.com'; // Untuk Xendit
    
    // 1. Hitung Total Bayar
    $total_bayar  = $harga_satuan * $jumlah;

    // 2. Buat External ID unik untuk Xendit
    $external_id  = 'INV-' . time() . '-' . $id_user;

    // 3. Request ke API Xendit untuk buat Invoice
    $data_xendit = [
        'external_id' => $external_id,
        'amount' => (int)$total_bayar,
        'payer_email' => $email_user,
        'description' => 'Pemesanan Catering - ' . $external_id,
        'invoice_duration' => 86400, // Aktif 24 jam
       'success_redirect_url' => 'http://localhost/catering_native/pembayaran_sukses.php?external_id=' . $external_id
    ];

    $payload = json_encode($data_xendit);

    $ch = curl_init('https://api.xendit.co/v2/invoices');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_USERPWD, $xendit_key . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

    $response = curl_exec($ch);
    $result = json_decode($response, true);

    // 4. Cek apakah Xendit berhasil memberikan URL
    if (isset($result['invoice_url'])) {
        $checkout_url = $result['invoice_url'];

        // 5. Insert ke Database Oracle (Sesuai kolom di image_e53afa.png)
        $query = "INSERT INTO pesanan (ID_USER, TOTAL_BAYAR, EXTERNAL_ID, CHECKOUT_URL, STATUS_PEMBAYARAN) 
                  VALUES (:id_user, :total, :ext_id, :url, 'PENDING')";

        $stmt = oci_parse($conn, $query);

        oci_bind_by_name($stmt, ":id_user", $id_user);
        oci_bind_by_name($stmt, ":total", $total_bayar);
        oci_bind_by_name($stmt, ":ext_id", $external_id);
        oci_bind_by_name($stmt, ":url", $checkout_url);

        $execute = oci_execute($stmt);

        if ($execute) {
            // Langsung arahkan ke halaman pembayaran Xendit
            echo "<script>
                    alert('Pesanan dibuat! Selesaikan pembayaran Anda.');
                    window.location.href='" . $checkout_url . "';
                  </script>";
        } else {
            $e = oci_error($stmt);
            echo "Gagal simpan ke Database: " . $e['message'];
        }
    } else {
        echo "Gagal terhubung ke Xendit: " . ($result['message'] ?? 'Unknown Error');
    }
}
?>