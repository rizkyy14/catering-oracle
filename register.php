<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Memproses Registrasi...</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f9fafb; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #ea580c; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .box { text-align: center; background: white; padding: 40px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border-radius: 16px; }
    </style>
</head>
<body>

<?php
if (isset($_POST['register'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    // Amankan password asli yang diketik untuk diteruskan ke verifikasi_sesi.php saat auto-login
    $password_asli = $_POST['password']; 
    $password = password_hash($password_asli, PASSWORD_DEFAULT);
    
    // 1. LOGIKA UPLOAD FOTO (Tetap berjalan lokal aman di server PHP kamu)
    $nama_file = $_FILES['foto']['name'];
    $tmp_file  = $_FILES['foto']['tmp_name'];
    $foto_final = 'default.png';

    if ($nama_file != "") {
        $ekstensi_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
        $foto_final = time() . '_' . str_replace(' ', '_', $nama) . '.' . $ekstensi_file;
        move_uploaded_file($tmp_file, 'assets/img/profile/' . $foto_final);
    }

    // Tampilkan box loading selagi JavaScript mendaftarkan ke cloud
    echo "<div class='box'>
            <div class='loader'></div>
            <p style='color: #4b5563; font-weight: bold;'>Mendaftarkan akun baru ke Cloud...</p>
          </div>";
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        // Amankan variabel PHP ke JavaScript
        const dataRegistrasi = {
            nama: <?= json_encode($nama) ?>,
            email: <?= json_encode($email) ?>,
            password_hash: <?= json_encode($password) ?>,
            foto_profil: <?= json_encode($foto_final) ?>
        };

        try {
            // 2. Kirim data akun ke Oracle Cloud via Fetch Browser
            const response = await fetch('https://oracleapex.com/ords/rizky_catering/catering/registrasi', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(dataRegistrasi)
            });

            if (!response.ok) throw new Error('Gagal terhubung ke server cloud.');

            const result = await response.json();

            // Sembunyikan loader begitu respon didapat
            const loaderBox = document.querySelector('.box');
            if (loaderBox) loaderBox.style.display = 'none';

            if (result.status === 'sukses') {
                // Tampilkan Popup Sukses
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: 'Akun kamu sudah aktif, mengalihkan ke sistem...',
                    confirmButtonColor: '#ea580c',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    // 3. JEMBATAN AUTO-LOGIN: Lempar data ke verifikasi_sesi.php secara aman lewat POST form simulasi
                    // Memanfaatkan ID_USER dari Oracle Cloud agar sinkron (jika tidak ada, fallback ke data bawaan)
                    const idUserBaru = result.id_user || '999'; 
                    
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'verifikasi_sesi.php';

                    const inputs = {
                        id_user: idUserBaru,
                        nama: dataRegistrasi.nama,
                        role: 'customer', // default role pelanggan baru
                        password_hash: dataRegistrasi.password_hash,
                        password_input: <?= json_encode($password_asli) ?>
                    };

                    for (const key in inputs) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = inputs[key];
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Sudah Terdaftar!',
                    text: result.message || 'Silakan gunakan email lain atau langsung login.',
                    confirmButtonColor: '#ea580c'
                }).then(() => { window.history.back(); });
            }

        } catch (error) {
            console.error(error);
            const loaderBox = document.querySelector('.box');
            if (loaderBox) loaderBox.style.display = 'none';
            
            Swal.fire({
                icon: 'error',
                title: 'Registrasi Gagal',
                text: error.message + '. Cek koneksi internet kamu.',
                confirmButtonColor: '#dc2626'
            }).then(() => { window.history.back(); });
        }
    });
    </script>

<?php
}
?>
</body>
</html>