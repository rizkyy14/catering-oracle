<?php
$url = "https://oracleapex.com/ords/rizky_catering/catering/menu";
echo "Mencoba mengambil data dari: " . $url . "<br><br>";

$res = file_get_contents($url);

if ($res === FALSE) {
    echo "Gagal total! file_get_contents juga diblokir oleh jaringan/server.";
} else {
    echo "Berhasil! Data masuk:<br>";
    echo "<pre>" . htmlspecialchars($res) . "</pre>";
}
?>