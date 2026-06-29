<?php
require_once 'koneksi.php';

$pesan = "";
if (isset($_POST['login'])) {
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Cari user berdasarkan email
    $query  = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    $user   = mysqli_fetch_assoc($query);

    if ($user) {
        // Memeriksa password (mendukung password_hash baru atau data dummy plain-text sebelumnya)
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            // Set session data pengguna
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];

            // Alihkan halaman sesuai hak akses/role masing-masing
            if ($user['role'] == 'admin') {
                header("Location: admin/dashboard.php");
                exit();
            } else {
                header("Location: penyewa/dashboard.php");
                exit();
            }
        } else {
            $pesan = "<div class='alert alert-danger small py-2'>Kata sandi yang Anda masukkan salah.</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger small py-2'>Email tidak terdaftar dalam sistem.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Premium Pickup Rental</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e9ecef;
        }
        .login-container {
            min-height: 100vh;
        }
        /* Bagian Kiri: Ilustrasi Visual */
        .login-bg {
            background-color: #fd7e14;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: white;
        }
        .login-bg h2 {
            font-weight: 800;
            letter-spacing: 1px;
        }
        /* Bagian Kanan: Formulir Masuk */
        .login-form-section {
            background-color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
        }
        .form-box {
            width: 100%;
            max-width: 400px;
        }
        .btn-orange {
            background-color: #fd7e14;
            color: white;
            font-weight: 600;
            border-radius: 8px;
        }
        .btn-orange:hover {
            background-color: #e8590c;
            color: white;
        }
        .text-orange {
            color: #fd7e14;
        }
        .drop-shadow {
            filter: drop-shadow(0px 10px 15px rgba(0,0,0,0.3));
        }
    </style>
</head>
<body>

    <div class="container-fluid p-0">
        <div class="row g-0 login-container">
            
            <div class="col-lg-6 login-bg d-none d-lg-flex">
                <div class="text-center">
                    <h2 class="text-uppercase mb-2">Sistem Aplikasi</h2>
                    <h1 class="fw-black mb-4 text-dark" style="font-weight: 900;">RENTAL MOBIL PICKUP</h1>
                    <img src="https://via.placeholder.com/450x260?text=Mitsubishi+L300+Pickup" alt="Pickup Ilustrasi" class="img-fluid drop-shadow my-3">
                    <p class="mt-4 px-5 small text-light opacity-75">
                        Kelola administrasi, transaksi sewa armada, kontrol data master logistik, serta buat laporan bulanan secara praktis dan transparan.
                    </p>
                </div>
            </div>

            <div class="col-lg-6 login-form-section">
                <div class="form-box">
                    <div class="text-center mb-4">
                        <div class="text-orange mb-2" style="font-size: 3rem;"><i class="bi bi-truck-flatbed"></i></div>
                        <h3 class="fw-bold m-0">Aplikasi Rental Pickup</h3>
                        <p class="text-muted small">Silakan masuk menggunakan akun yang telah terdaftar</p>
                    </div>

                    <?= $pesan; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Username / Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-person"></i></span>
                                <input type="email" name="email" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan email Anda" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-lock"></i></span>
                                <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan password Anda" required>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn btn-orange w-100 py-2.5 shadow-sm text-uppercase mb-4">Login</button>
                    </form>

                    <div class="text-center border-top pt-4">
                        <span class="text-muted small">Belum punya akun rental?</span> 
                        <a href="register.php" class="text-orange fw-bold text-decoration-none small d-block d-sm-inline ms-1">Sign Up Sekarang</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>