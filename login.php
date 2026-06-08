<?php
session_start();
// include 'config/database.php'; // <--- Dimatikan karena sudah pakai Cloud REST API
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
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
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    // Kita render layar loading selagi JavaScript bekerja di background
    echo "<div class='box'>
            <div class='loader'></div>
            <p style='color: #4b5563; font-weight: bold;'>Memverifikasi akun Anda...</p>
          </div>";
    ?>

    <script>
    document.addEventListener('DOMContentLoaded', async function() {
        const emailInput = '<?= json_encode($email) ?>';
        
        try {
            const response = await fetch('https://oracleapex.com/ords/rizky_catering/catering/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: JSON.parse(emailInput) })
            });

            if (!response.ok) throw new Error('Gagal terhubung ke server cloud.');

            const result = await response.json();

            if (result.status === 'ditemukan') {
                // --- PERBAIKAN: TAMPILKAN SWEETALERT SBLM SUBMIT ---
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: 'Selamat datang kembali, ' + result.nama + '!',
                    showConfirmButton: false,
                    timer: 2000, // Tampilkan selama 2 detik
                    allowOutsideClick: false
                });

                // Bungkus proses submit di dalam timeout agar sinkron dengan timer SweetAlert
                setTimeout(() => {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'verifikasi_sesi.php';

                    const data = {
                        id_user: result.id_user,
                        nama: result.nama,
                        role: result.role,
                        password_hash: result.password_hash,
                        password_input: <?= json_encode($pass) ?>
                    };

                    for (const key in data) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = key;
                        input.value = data[key];
                        form.appendChild(input);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }, 2000); // Nunggu 2000ms (2 detik) baru pindah halaman

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: 'Email atau Password salah!',
                    confirmButtonColor: '#ea580c'
                }).then(() => { window.history.back(); });
            }

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error Jaringan',
                text: 'Gagal merespon ke Oracle Cloud. Cek koneksi internet.',
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