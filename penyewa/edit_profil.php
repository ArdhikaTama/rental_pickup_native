<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$pesan = "";

// Ambil data profil lama
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query_user);

// Proses Update Profile
if (isset($_POST['update_profil'])) {
    $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $no_telp      = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $password_baru= $_POST['password_baru'];

    if (!empty($password_baru)) {
        // Jika password diisi, ikut update password
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update = "UPDATE users SET nama_lengkap='$nama_lengkap', no_telp='$no_telp', alamat='$alamat', password='$password_hash' WHERE id_user='$id_user'";
    } else {
        // Jika password kosong, update data lainnya saja
        $update = "UPDATE users SET nama_lengkap='$nama_lengkap', no_telp='$no_telp', alamat='$alamat' WHERE id_user='$id_user'";
    }

    if (mysqli_query($koneksi, $update)) {
        $_SESSION['nama_lengkap'] = $nama_lengkap; // Update nama di session web
        $pesan = "<div class='alert alert-success small py-2'>Profil berhasil diperbarui!</div>";
        echo "<meta http-equiv='refresh' content='1;url=dashboard.php'>";
    } else {
        $pesan = "<div class='alert alert-danger small py-2'>Gagal memperbarui profil: " . mysqli_error($koneksi) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #333333; color: white; padding-top: 15px; z-index: 1000; }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 15px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.85); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        .sidebar .nav-link i { margin-right: 10px; font-size: 1.1rem; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        .text-orange { color: #fd7e14 !important; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="brand fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-truck-flatbed me-2 fs-4 text-orange"></i>
                <div>
                    <span class="d-block lh-1 small opacity-75 text-white">PREMIUM</span>
                    <span class="fs-6 text-uppercase text-orange">Pickup Rental</span>
                </div>
            </div>
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Beranda</a>
            <div class="menu-section">Transaksi Logistik</div>
            <a href="pesan_mobil.php" class="nav-link"><i class="bi bi-file-earmark-plus"></i> Form Sewa Mobil</a>
            <a href="#" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10"><i class="bi bi-box-arrow-right text-danger"></i> Keluar</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <span class="text-muted fw-medium small">Pengaturan Akun Penyewa</span>
            <span class="fw-semibold text-dark small"><i class="bi bi-person-circle text-orange"></i> <?= htmlspecialchars($user['nama_lengkap']); ?></span>
        </div>

        <div class="container-fluid p-4">
            <h4 class="mb-4 text-dark fw-normal">Pengaturan Profil</h4>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <?= $pesan; ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Alamat Email (Username)</label>
                                <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email']); ?>" readonly disabled>
                                <div class="form-text small text-muted">Email Utama tidak dapat diubah demi validitas relasi data transaksi.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nomor WhatsApp / Telepon</label>
                                <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($user['no_telp']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Alamat Rumah Lengkap</label>
                                <textarea name="alamat" class="form-control" rows="3" required><?= htmlspecialchars($user['alamat']); ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Ubah Password Baru</label>
                                <input type="password" name="password_baru" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="update_profil" class="btn btn-orange px-4">Simpan Perubahan</button>
                                <a href="dashboard.php" class="btn btn-light border">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>