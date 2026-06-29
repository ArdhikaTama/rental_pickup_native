<?php
require_once 'koneksi.php';

$pesan = "";
if (isset($_POST['register'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email        = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password     = $_POST['password']; // Menggunakan password_hash demi keamanan
    $no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    // Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($cek_email) > 0) {
        $pesan = "<div class='alert alert-danger'>Email sudah digunakan! Silakan gunakan email lain.</div>";
    } else {
        // Enkripsi password sebelum disimpan ke database
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (nama_lengkap, email, password, no_telp, alamat, role) 
                  VALUES ('$nama_lengkap', '$email', '$password_hash', '$no_telp', '$alamat', 'penyewa')";
        
        if (mysqli_query($koneksi, $query)) {
            $pesan = "<div class='alert alert-success'>Registrasi berhasil! Silakan <a href='login.php' class='alert-link'>Login di sini</a>.</div>";
        } else {
            $pesan = "<div class='alert alert-danger'>Terjadi kesalahan: " . mysqli_error($koneksi) . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Premium Pickup Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .card-register { border: none; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        .text-orange { color: #fd7e14; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-register p-4 bg-white">
                    <h3 class="fw-bold mb-3 text-center text-uppercase">Buat Akun <span class="text-orange">Penyewa</span></h3>
                    <p class="text-muted text-center small mb-4">Daftarkan diri Anda untuk mulai melakukan penyewaan armada pickup terbaik.</p>
                    
                    <?= $pesan; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" required placeholder="Masukkan nama lengkap Anda">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Alamat Email</label>
                            <input type="email" name="email" class="form-control" required placeholder="contoh@email.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Kata Sandi</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Nomor Telepon/WhatsApp</label>
                            <input type="text" name="no_telp" class="form-control" required placeholder="Contoh: 08123456789">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Alamat Lengkap (Sesuai KTP)</label>
                            <textarea name="alamat" class="form-control" rows="3" required placeholder="Masukkan alamat lengkap rumah Anda"></textarea>
                        </div>
                        <button type="submit" name="register" class="btn btn-orange w-100 py-2 mt-2 rounded-3">SIGN UP</button>
                    </form>
                    
                    <div class="text-center mt-4 small">
                        <span class="text-muted">Sudah memiliki akun?</span> <a href="login.php" class="text-orange fw-bold text-decoration-none">Sign In di sini</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>