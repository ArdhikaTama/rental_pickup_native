<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Proses Aksi Validasi (Konfirmasi / Batalkan)
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_pesan = $_GET['id'];
    $status = ($_GET['aksi'] == 'setuju') ? 'dikonfirmasi' : 'dibatalkan';
    
    mysqli_query($koneksi, "UPDATE pemesanan SET status_pemesanan = '$status' WHERE id_pemesanan = '$id_pesan'");
    
    // Jika dibatalkan, kembalikan status mobil menjadi tersedia
    if ($status == 'dibatalkan') {
        $data_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_mobil FROM pemesanan WHERE id_pemesanan = '$id_pesan'"));
        $id_mob = $data_p['id_mobil'];
        mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'tersedia' WHERE id_mobil = '$id_mob'");
    }
    header("Location: dashboard.php");
    exit();
}

// Ambil Semua Transaksi Masuk
$query_transaksi = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor 
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN mobil m ON p.id_mobil = m.id_mobil 
    ORDER BY p.id_pemesanan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .bg-orange { background-color: #fd7e14 !important; color: white; }
        .text-orange { color: #fd7e14; }
        .sidebar { background-color: #ffffff; min-height: 100vh; box-shadow: 2px 0 5px rgba(0,0,0,0.05); }
        .nav-link-admin { color: #333; font-weight: 500; padding: 12px 20px; display: block; text-decoration: none; border-radius: 8px; }
        .nav-link-admin:hover, .nav-link-admin.active { background-color: #fd7e14; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <h5 class="fw-bold text-orange mb-4 text-center"><i class="bi bi-shield-lock-fill"></i> ADMIN PANEL</h5>
            <hr>
            <div class="d-flex flex-column gap-2">
                <a href="dashboard.php" class="nav-link-admin active"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                <a href="master_mobil.php" class="nav-link-admin"><i class="bi bi-truck me-2"></i> Master Mobil</a>
                <a href="../logout.php" class="nav-link-admin text-danger mt-5"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Dashboard Manajemen Sewa</h3>
                <span class="badge bg-secondary p-2">Sistem Prosedural PHP</span>
            </div>

            <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                <h5 class="fw-bold mb-3 text-uppercase">Konfirmasi & Log Transaksi</h5>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nota</th>
                                <th>Penyewa</th>
                                <th>Mobil</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($t = mysqli_fetch_assoc($query_transaksi)): ?>
                            <tr>
                                <td class="fw-bold">#PKP-<?= $t['id_pemesanan']; ?></td>
                                <td><?= htmlspecialchars($t['nama_lengkap']); ?></td>
                                <td><?= htmlspecialchars($t['nama_mobil']); ?> (<span class="small text-muted"><?= $t['plat_nomor']; ?></span>)</td>
                                <td><?= date('d/m/Y', strtotime($t['tanggal_mulai'])); ?></td>
                                <td><?= date('d/m/Y', strtotime($t['tanggal_selesai'])); ?></td>
                                <td class="fw-bold text-orange">Rp <?= number_format($t['total_bayar'], 0, ',', '.'); ?></td>
                                <td>
                                    <?php if ($t['status_pemesanan'] == 'pending'): ?>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    <?php elseif ($t['status_pemesanan'] == 'dikonfirmasi'): ?>
                                        <span class="badge bg-success">Disetujui</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Batal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($t['status_pemesanan'] == 'pending'): ?>
                                        <a href="dashboard.php?aksi=setuju&id=<?= $t['id_pemesanan']; ?>" class="btn btn-sm btn-success me-1" onclick="return confirm('Setujui transaksi ini?')"><i class="bi bi-check-lg"></i></a>
                                        <a href="dashboard.php?aksi=batal&id=<?= $t['id_pemesanan']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Batalkan transaksi ini?')"><i class="bi bi-x-lg"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>