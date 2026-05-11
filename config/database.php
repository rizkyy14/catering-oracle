<?php

$username = "webcatering"; 
$password = "ris1234";      

$database   = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=localhost)(PORT=1521))(CONNECT_DATA=(SERVICE_NAME=FREE)))";

$conn = oci_connect($username, $password, $database);

if (!$conn) {
    $e = oci_error();
    die("Koneksi ke Oracle Gagal: " . htmlentities($e['message']));
}

// Fungsi helper untuk eksekusi query agar tidak ngetik ulang oci_parse
function execute_query($conn, $sql, $params = []) {
    $stmt = oci_parse($conn, $sql);
    foreach ($params as $key => $val) {
        oci_bind_by_name($stmt, $key, $params[$key]);
    }
    oci_execute($stmt);
    return $stmt;
}
?>