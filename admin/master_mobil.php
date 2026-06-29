<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// 1. TAMBAH MOBIL BARU
if (isset($_POST['simpan_mobil'])) {
    $id_kat      = $_POST['id_kategori'];
    $nama_mobil  = mysqli_real_escape_string($koneksi, $_POST['nama_mobil']);
    $plat        = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $tahun       = $_POST['tahun_pembuatan'];

    $insert = "INSERT INTO mobil (id_kategori, nama_mobil, plat_nomor, warna, tahun_pembuatan, status_ketersediaan) 
               VALUES ('$id_kat', '$nama_mobil', '$plat', '$warna', '$tahun', 'tersedia')";
    
    if (mysqli_query($koneksi, $insert)) {
        header("Location: master_mobil.php?msg=sukses");
        exit();
    }
}

// 2. HAPUS MOBIL
if (isset($_GET['hapus'])) {
    $id_hapus = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM mobil WHERE id_mobil = '$id_hapus'");
    header("Location: master_mobil.php?msg=terhapus");
    exit();
}

// Ambil Data Kategori & Mobil untuk Tabel
$list_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$list_mobil    = mysqli_query($koneksi, "SELECT m.*, k.nama_kategori FROM mobil m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Mobil - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .text-orange { color: #fd7e14; }
        .sidebar { background-color: #ffffff; min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .nav-link-admin { color: #333; font-weight: 500; padding: 12px 20px; display: block; text-decoration: none; border-radius: 8px; }
        .nav-link-admin:hover, .nav-link-admin.active { background-color: #fd7e14; color: white; }
        .btn-orange { background-color: #fd7e14; color: white; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <h5 class="fw-bold text-orange mb-4 text-center"><i class="bi bi-shield-lock-fill"></i> ADMIN PANEL</h5>
            <hr>
            <div class="d-flex flex-column gap-2">
                <a href="dashboard.php" class="nav-link-admin"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="master_mobil.php" class="nav-link-admin active"><i class="bi bi-truck me-2"></i> Master Mobil</a>
                <a href="../logout.php" class="nav-link-admin text-danger mt-5"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h5 class="fw-bold mb-3 text-uppercase">Tambah Armada</h5>
                        <form action="" method="POST">
                            <div class="mb-2">
                                <label class="small fw-bold">Kategori</label>
                                <select name="id_kategori" class="form-select form-select-sm" required>
                                    <?php while ($kat = mysqli_fetch_assoc($list_kategori)): ?>
                                        <option value="<?= $kat['id_kategori']; ?>"><?= $kat['nama_kategori']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Nama Mobil</label>
                                <input type="text" name="nama_mobil" class="form-control form-control-sm" placeholder="Contoh: L300 Bak Tinggi" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Plat Nomor</label>
                                <input type="text" name="plat_nomor" class="form-control form-control-sm" placeholder="B 1234 ABC" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold">Warna</label>
                                <input type="text" name="warna" class="form-control form-control-sm" placeholder="Hitam / Putih" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold">Tahun Buat</label>
                                <input type="number" name="tahun_pembuatan" class="form-control form-control-sm" placeholder="2022" required>
                            </div>
                            <button type="submit" name="simpan_mobil" class="btn btn-orange btn-sm w-100 fw-bold">SIMPAN UNIT</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h5 class="fw-bold mb-3 text-uppercase">Daftar Armada Pickup</h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle small">
                                <thead class="table-light">
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Nama Unit</th>
                                        <th>Plat No</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($m = mysqli_fetch_assoc($list_mobil)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['nama_kategori']); ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($m['nama_mobil']); ?></td>
                                        <td><span class="badge bg-dark"><?= htmlspecialchars($m['plat_nomor']); ?></span></td>
                                        <td><?= $m['tahun_pembuatan']; ?></td>
                                        <td>
                                            <span class="badge <?= ($m['status_ketersediaan'] == 'tersedia') ? 'bg-success' : 'bg-danger'; ?>">
                                                <?= $m['status_ketersediaan']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="master_mobil.php?hapus=<?= $m['id_mobil']; ?>" class="text-danger" onclick="return confirm('Hapus unit ini?')"><i class="bi bi-trash-fill"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>