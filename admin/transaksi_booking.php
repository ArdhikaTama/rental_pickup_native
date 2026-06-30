<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// =============================================================================
// LOGIKA OPERASIONAL VALIDASI BOOKING (SETUJU / TOLAK)
// =============================================================================
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_pemesanan = intval($_GET['id']);
    $aksi         = $_GET['aksi'];

    if ($aksi === 'setuju') {
        // Jika disetujui, status pemesanan naik dari 'pending' menjadi 'dikonfirmasi'
        $query_update = "UPDATE pemesanan SET status_pemesanan = 'dikonfirmasi' WHERE id_pemesanan = '$id_pemesanan'";
        if (mysqli_query($koneksi, $query_update)) {
            $pesan = "<div class='alert alert-success small py-2'>Pemesanan #PKP-$id_pemesanan berhasil <strong>Dikonfirmasi</strong>! Menunggu pembayaran/jaminan dari penyewa.</div>";
        }
    } elseif ($aksi === 'tolak') {
        // Mulai database transaction agar aman saat mengembalikan status mobil
        mysqli_query($koneksi, "START TRANSACTION");

        // Ambil ID mobil terlebih dahulu untuk memulihkan statusnya
        $data_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_mobil FROM pemesanan WHERE id_pemesanan = '$id_pemesanan'"));
        $id_mobil = $data_p['id_mobil'];

        // Update status pemesanan menjadi 'dibatalkan'
        $update_status = mysqli_query($koneksi, "UPDATE pemesanan SET status_pemesanan = 'dibatalkan' WHERE id_pemesanan = '$id_pemesanan'");
        
        // Kembalikan status mobil dari 'disewa' menjadi 'tersedia' lagi agar bisa dipesan orang lain
        $update_mobil = mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'tersedia' WHERE id_mobil = '$id_mobil'");

        if ($update_status && $update_mobil) {
            mysqli_query($koneksi, "COMMIT");
            $pesan = "<div class='alert alert-warning small py-2'>Pemesanan #PKP-$id_pemesanan telah <strong>Ditolak</strong>. Status unit mobil dikembalikan menjadi Tersedia.</div>";
        } else {
            mysqli_query($koneksi, "ROLLBACK");
            $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses pembatalan sewa.</div>";
        }
    }
}

// =============================================================================
// QUERY DATA ANTRIAN BOOKING (Status: pending, dikonfirmasi, dibatalkan)
// =============================================================================
$query_booking = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap, u.no_telp, m.nama_mobil, m.plat_nomor 
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN mobil m ON p.id_mobil = m.id_mobil 
    ORDER BY p.id_pemesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Booking - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #2b2c2d; color: white; padding-top: 15px; z-index: 1000; }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 18px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; border-radius: 4px; margin: 0 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; font-weight: 500; }
        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .text-orange { color: #fd7e14 !important; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="brand fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-orange"></i>
                <div>
                    <span class="d-block lh-1 small opacity-75 text-white">ADMINISTRATOR</span>
                    <span class="fs-6 text-uppercase text-orange">Pickup System</span>
                </div>
            </div>
            
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            
            <div class="menu-section">Data Master (8)</div>
            <a href="master_mobil.php" class="nav-link"><i class="bi bi-truck"></i> Master Mobil</a>
            <a href="master_kategori.php" class="nav-link"><i class="bi bi-tags"></i> Kategori & Paket</a>
            <a href="master_sopir.php" class="nav-link"><i class="bi bi-person-badge"></i> Data Sopir</a>
            <a href="master_rekening.php" class="nav-link"><i class="bi bi-credit-card"></i> Rekening Bank</a>
            <a href="master_jaminan.php" class="nav-link"><i class="bi bi-collection"></i> Jenis Jaminan</a>
            
            <div class="menu-section">Alur Transaksi (5)</div>
            <a href="transaksi_booking.php" class="nav-link active"><i class="bi bi-calendar-check"></i> Transaksi Booking</a>
            <a href="transaksi_penyewaan.php" class="nav-link"><i class="bi bi-receipt"></i> Transaksi Penyewaan</a>
            <a href="transaksi_pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran & DP</a>
            <a href="transaksi_perpanjangan.php" class="nav-link"><i class="bi bi-clock-history"></i> Perpanjangan Sewa</a>
            <a href="transaksi_pengembalian.php" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
            
            <div class="menu-section">Pelaporan & Data</div>
            <a href="laporan_transaksi.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Laporan Seluruhnya</a>
            <a href="utilitas_data.php" class="nav-link"><i class="bi bi-file-earmark-excel"></i> Import & Export Data</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Keluar dari panel admin?')"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Validasi Log jadwal & Ketersediaan Awal Armada</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Transaksi Booking</h4>
            <?= $pesan; ?>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold text-secondary text-uppercase">Log Antrean Booking Masuk</h6>
                    <span class="badge bg-orange text-white">Data Real-time</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center small table-bordered m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Data Penyewa</th>
                                <th>Armada Unit</th>
                                <th>Jadwal Sewa</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_booking) == 0): ?>
                                <tr><td colspan="7" class="text-muted py-4">Belum ada pengajuan booking yang masuk ke sistem.</td></tr>
                            <?php else: ?>
                                <?php while ($b = mysqli_fetch_assoc($query_booking)): ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $b['id_pemesanan']; ?></td>
                                    <td class="text-start">
                                        <strong><?= htmlspecialchars($b['nama_lengkap']); ?></strong>
                                        <div class="text-muted style-italic small"><?= htmlspecialchars($b['no_telp']); ?></div>
                                    </td>
                                    <td class="text-start">
                                        <?= htmlspecialchars($b['nama_mobil']); ?>
                                        <div class="text-muted small">[<?= htmlspecialchars($b['plat_nomor']); ?>]</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= date('d/m/Y', strtotime($b['tanggal_mulai'])); ?></span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-light text-dark border"><?= date('d/m/Y', strtotime($b['tanggal_selesai'])); ?></span>
                                    </td>
                                    <td class="fw-bold text-orange">Rp <?= number_format($b['total_bayar'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                        if ($b['status_pemesanan'] === 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        elseif ($b['status_pemesanan'] === 'dikonfirmasi') echo '<span class="badge bg-info">Dikonfirmasi</span>';
                                        elseif ($b['status_pemesanan'] === 'berjalan') echo '<span class="badge bg-primary">Berjalan</span>';
                                        elseif ($b['status_pemesanan'] === 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                                        else echo '<span class="badge bg-danger">Batal</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($b['status_pemesanan'] === 'pending'): ?>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="transaksi_booking.php?aksi=setuju&id=<?= $b['id_pemesanan']; ?>" class="btn btn-sm btn-success py-1 px-2 border-0" onclick="return confirm('Konfirmasi persetujuan jadwal booking unit ini?')">
                                                    <i class="bi bi-ch<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// =============================================================================
// LOGIKA OPERASIONAL VALIDASI BOOKING (SETUJU / TOLAK)
// =============================================================================
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_pemesanan = intval($_GET['id']);
    $aksi         = $_GET['aksi'];

    if ($aksi === 'setuju') {
        // Jika disetujui, status pemesanan naik dari 'pending' menjadi 'dikonfirmasi'
        $query_update = "UPDATE pemesanan SET status_pemesanan = 'dikonfirmasi' WHERE id_pemesanan = '$id_pemesanan'";
        if (mysqli_query($koneksi, $query_update)) {
            $pesan = "<div class='alert alert-success small py-2'>Pemesanan #PKP-$id_pemesanan berhasil <strong>Dikonfirmasi</strong>! Menunggu pembayaran/jaminan dari penyewa.</div>";
        }
    } elseif ($aksi === 'tolak') {
        // Mulai database transaction agar aman saat mengembalikan status mobil
        mysqli_query($koneksi, "START TRANSACTION");

        // Ambil ID mobil terlebih dahulu untuk memulihkan statusnya
        $data_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_mobil FROM pemesanan WHERE id_pemesanan = '$id_pemesanan'"));
        $id_mobil = $data_p['id_mobil'];

        // Update status pemesanan menjadi 'dibatalkan'
        $update_status = mysqli_query($koneksi, "UPDATE pemesanan SET status_pemesanan = 'dibatalkan' WHERE id_pemesanan = '$id_pemesanan'");
        
        // Kembalikan status mobil dari 'disewa' menjadi 'tersedia' lagi agar bisa dipesan orang lain
        $update_mobil = mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'tersedia' WHERE id_mobil = '$id_mobil'");

        if ($update_status && $update_mobil) {
            mysqli_query($koneksi, "COMMIT");
            $pesan = "<div class='alert alert-warning small py-2'>Pemesanan #PKP-$id_pemesanan telah <strong>Ditolak</strong>. Status unit mobil dikembalikan menjadi Tersedia.</div>";
        } else {
            mysqli_query($koneksi, "ROLLBACK");
            $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses pembatalan sewa.</div>";
        }
    }
}

// =============================================================================
// QUERY DATA ANTRIAN BOOKING (Status: pending, dikonfirmasi, dibatalkan)
// =============================================================================
$query_booking = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap, u.no_telp, m.nama_mobil, m.plat_nomor 
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN mobil m ON p.id_mobil = m.id_mobil 
    ORDER BY p.id_pemesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Booking - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #2b2c2d; color: white; padding-top: 15px; z-index: 1000; }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 18px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; border-radius: 4px; margin: 0 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; font-weight: 500; }
        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .text-orange { color: #fd7e14 !important; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="brand fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-shield-lock-fill me-2 fs-4 text-orange"></i>
                <div>
                    <span class="d-block lh-1 small opacity-75 text-white">ADMINISTRATOR</span>
                    <span class="fs-6 text-uppercase text-orange">Pickup System</span>
                </div>
            </div>
            
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            
            <div class="menu-section">Data Master (8)</div>
            <a href="master_mobil.php" class="nav-link"><i class="bi bi-truck"></i> Master Mobil</a>
            <a href="master_kategori.php" class="nav-link"><i class="bi bi-tags"></i> Kategori & Paket</a>
            <a href="master_sopir.php" class="nav-link"><i class="bi bi-person-badge"></i> Data Sopir</a>
            <a href="master_rekening.php" class="nav-link"><i class="bi bi-credit-card"></i> Rekening Bank</a>
            <a href="master_jaminan.php" class="nav-link"><i class="bi bi-collection"></i> Jenis Jaminan</a>
            
            <div class="menu-section">Alur Transaksi (5)</div>
            <a href="transaksi_booking.php" class="nav-link active"><i class="bi bi-calendar-check"></i> Transaksi Booking</a>
            <a href="transaksi_penyewaan.php" class="nav-link"><i class="bi bi-receipt"></i> Transaksi Penyewaan</a>
            <a href="transaksi_pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran & DP</a>
            <a href="transaksi_perpanjangan.php" class="nav-link"><i class="bi bi-clock-history"></i> Perpanjangan Sewa</a>
            <a href="transaksi_pengembalian.php" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
            
            <div class="menu-section">Pelaporan & Data</div>
            <a href="laporan_transaksi.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Laporan Seluruhnya</a>
            <a href="utilitas_data.php" class="nav-link"><i class="bi bi-file-earmark-excel"></i> Import & Export Data</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Keluar dari panel admin?')"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Validasi Log jadwal & Ketersediaan Awal Armada</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Transaksi Booking</h4>
            <?= $pesan; ?>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold text-secondary text-uppercase">Log Antrean Booking Masuk</h6>
                    <span class="badge bg-orange text-white">Data Real-time</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center small table-bordered m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Data Penyewa</th>
                                <th>Armada Unit</th>
                                <th>Jadwal Sewa</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                                <th>Aksi Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_booking) == 0): ?>
                                <tr><td colspan="7" class="text-muted py-4">Belum ada pengajuan booking yang masuk ke sistem.</td></tr>
                            <?php else: ?>
                                <?php while ($b = mysqli_fetch_assoc($query_booking)): ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $b['id_pemesanan']; ?></td>
                                    <td class="text-start">
                                        <strong><?= htmlspecialchars($b['nama_lengkap']); ?></strong>
                                        <div class="text-muted style-italic small"><?= htmlspecialchars($b['no_telp']); ?></div>
                                    </td>
                                    <td class="text-start">
                                        <?= htmlspecialchars($b['nama_mobil']); ?>
                                        <div class="text-muted small">[<?= htmlspecialchars($b['plat_nomor']); ?>]</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= date('d/m/Y', strtotime($b['tanggal_mulai'])); ?></span>
                                        <i class="bi bi-arrow-right text-muted mx-1"></i>
                                        <span class="badge bg-light text-dark border"><?= date('d/m/Y', strtotime($b['tanggal_selesai'])); ?></span>
                                    </td>
                                    <td class="fw-bold text-orange">Rp <?= number_format($b['total_bayar'], 0, ',', '.'); ?></td>
                                    <td>
                                        <?php 
                                        if ($b['status_pemesanan'] === 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        elseif ($b['status_pemesanan'] === 'dikonfirmasi') echo '<span class="badge bg-info">Dikonfirmasi</span>';
                                        elseif ($b['status_pemesanan'] === 'berjalan') echo '<span class="badge bg-primary">Berjalan</span>';
                                        elseif ($b['status_pemesanan'] === 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                                        else echo '<span class="badge bg-danger">Batal</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($b['status_pemesanan'] === 'pending'): ?>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="transaksi_booking.php?aksi=setuju&id=<?= $b['id_pemesanan']; ?>" class="btn btn-sm btn-success py-1 px-2 border-0" onclick="return confirm('Konfirmasi persetujuan jadwal booking unit ini?')">
                                                    <i class="bi bi-check-lg"></i> Setuju
                                                </a>
                                                <a href="transaksi_booking.php?aksi=tolak&id=<?= $b['id_pemesanan']; ?>" class="btn btn-sm btn-danger py-1 px-2 border-0" onclick="return confirm('Tolak/Batalkan antrean pemesanan ini?')">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="bi bi-check2-all text-success"></i> Terproses</span>
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

        <footer class="bg-white text-center py-3 text-muted small border-top mt-auto">
            Admin Console Management © Rental Pickup JKT 2026
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>eck-lg"></i> Setuju
                                                </a>
                                                <a href="transaksi_booking.php?aksi=tolak&id=<?= $b['id_pemesanan']; ?>" class="btn btn-sm btn-danger py-1 px-2 border-0" onclick="return confirm('Tolak/Batalkan antrean pemesanan ini?')">
                                                    <i class="bi bi-x-lg"></i> Tolak
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small"><i class="bi bi-check2-all text-success"></i> Terproses</span>
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

        <footer class="bg-white text-center py-3 text-muted small border-top mt-auto">
            Admin Console Management © Rental Pickup JKT 2026
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>