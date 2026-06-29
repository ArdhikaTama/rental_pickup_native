<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi halaman: Pastikan user sudah login dan rolenya adalah 'penyewa'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];

// Ambil data riwayat pemesanan milik penyewa ini beserta relasi paket harganya
$query_booking = "SELECT p.*, m.nama_mobil, m.plat_nomor, k.jenis_paket, k.harga 
                  FROM pemesanan p
                  JOIN mobil m ON p.id_mobil = m.id_mobil
                  JOIN paket_harga k ON p.id_paket = k.id_paket
                  WHERE p.id_user = '$id_user'
                  ORDER BY p.id_pemesanan DESC";
$result_booking = mysqli_query($koneksi, $query_booking);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background-color: #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .text-orange { color: #fd7e14; }
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        .sidebar-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .status-badge { font-size: 0.8rem; padding: 5px 10px; border-radius: 20px; font-weight: 600; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-orange" href="#"><i class="bi bi-truck-flatbed"></i> PANEL PENYEWA</a>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted small d-none d-sm-inline">Selamat Datang, <strong><?= htmlspecialchars($nama_user); ?></strong></span>
                <a href="../logout.php" class="btn btn-outline-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">
            
            <div class="col-lg-3">
                <div class="sidebar-card text-center mb-4">
                    <div class="text-orange mb-3" style="font-size: 3.5rem;"><i class="bi bi-person-circle"></i></div>
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($nama_user); ?></h5>
                    <p class="text-muted small bg-light py-1 rounded">Status: Penyewa Aktif</p>
                    <hr>
                    <a href="pesan_mobil.php" class="btn btn-orange w-100 py-2"><i class="bi bi-plus-circle-fill me-2"></i> Sewa Mobil Baru</a>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="bg-white p-4 rounded-3 shadow-sm">
                    <h4 class="fw-bold mb-4 text-uppercase">Riwayat Penyewaan Anda</h4>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Booking</th>
                                    <th>Armada</th>
                                    <th>Durasi Sewa</th>
                                    <th>Total Bayar</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_booking) == 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Anda belum pernah melakukan pemesanan mobil.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = mysqli_fetch_assoc($result_booking)): ?>
                                        <tr>
                                            <td class="fw-bold">#PKP-<?= $row['id_pemesanan']; ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_mobil']); ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($row['plat_nomor']); ?></small>
                                            </td>
                                            <td class="small">
                                                <?= date('d M Y', strtotime($row['tanggal_mulai'])); ?> s/d<br>
                                                <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                                            </td>
                                            <td class="fw-bold text-orange">Rp. <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php
                                                if ($row['status_pemesanan'] == 'pending') echo '<span class="status-badge bg-warning text-dark">Menunggu Validasi</span>';
                                                elseif ($row['status_pemesanan'] == 'dikonfirmasi') echo '<span class="status-badge bg-info text-white">Dikonfirmasi</span>';
                                                elseif ($row['status_pemesanan'] == 'berjalan') echo '<span class="status-badge bg-primary text-white">Sedang Disewa</span>';
                                                elseif ($row['status_pemesanan'] == 'selesai') echo '<span class="status-badge bg-success text-white">Selesai</span>';
                                                else echo '<span class="status-badge bg-danger text-white">Dibatalkan</span>';
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($row['status_pemesanan'] == 'pending'): ?>
                                                    <a href="bayar_jaminan.php?id=<?= $row['id_pemesanan']; ?>" class="btn btn-orange btn-sm px-3 shadow-sm">
                                                        <i class="bi bi-cash-coin me-1"></i> Bayar & Jaminan
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-light border btn-sm" title="Lihat Detail"><i class="bi bi-eye"></i> Detail</button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
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