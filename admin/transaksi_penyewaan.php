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
// LOGIKA PROSES SERAH TERIMA UNIT (UBAH STATUS MENJADI BERJALAN)
// =============================================================================
if (isset($_GET['aksi']) && $_GET['aksi'] === 'mulai' && isset($_GET['id'])) {
    $id_pemesanan = intval($_GET['id']);
    
    // Ubah status dari 'dikonfirmasi' menjadi 'berjalan' (mobil resmi keluar garasi)
    $query_mulai = "UPDATE pemesanan SET status_pemesanan = 'berjalan' WHERE id_pemesanan = '$id_pemesanan'";
    
    if (mysqli_query($koneksi, $query_mulai)) {
        $pesan = "<div class='alert alert-success small py-2'>Unit Armada #PKP-$id_pemesanan resmi diserahterimakan! Status sewa kini: <strong>Berjalan</strong>.</div>";
    } else {
        $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses serah terima: " . mysqli_error($koneksi) . "</div>";
    }
}

// =============================================================================
// QUERY AMBIL DATA PENYEWAAN (STATUS: dikonfirmasi atau berjalan)
// =============================================================================
$query_penyewaan = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor, m.warna, pkt.jenis_paket
    FROM pemesanan p
    JOIN users u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    LEFT JOIN paket_harga pkt ON p.id_paket = pkt.id_paket
    WHERE p.status_pemesanan IN ('dikonfirmasi', 'berjalan')
    ORDER BY p.id_pemesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Penyewaan - Admin Panel</title>
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
            <a href="transaksi_booking.php" class="nav-link"><i class="bi bi-calendar-check"></i> Transaksi Booking</a>
            <a href="transaksi_penyewaan.php" class="nav-link active"><i class="bi bi-receipt"></i> Transaksi Penyewaan</a>
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
            <span class="text-muted small fw-medium">Manajemen Status Serah Terima Unit Logistik Aktif</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Transaksi Penyewaan</h4>
            <?= $pesan; ?>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold text-secondary text-uppercase">Daftar Penyewaan Aktif / Siap Jalan</h6>
                    <span class="badge bg-dark">Monitoring Armada</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center small table-bordered m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Penyewa</th>
                                <th>Armada Mobil PU</th>
                                <th>Durasi Sewa</th>
                                <th>Paket & Tagihan</th>
                                <th>Status Jalan</th>
                                <th>Aksi Operasional</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_penyewaan) == 0): ?>
                                <tr><td colspan="7" class="text-muted py-4">Tidak ada armada logistik yang sedang aktif disewa atau menunggu serah terima saat ini.</td></tr>
                            <?php else: ?>
                                <?php while ($p = mysqli_fetch_assoc($query_penyewaan)): 
                                    // Hitung hari sewa otomatis
                                    $tgl1 = new DateTime($p['tanggal_mulai']);
                                    $tgl2 = new DateTime($p['tanggal_selesai']);
                                    $durasi = $tgl1->diff($tgl2)->days;
                                    $durasi = ($durasi == 0) ? 1 : $durasi;
                                ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $p['id_pemesanan']; ?></td>
                                    <td class="text-start"><strong><?= htmlspecialchars($p['nama_lengkap']); ?></strong></td>
                                    <td class="text-start">
                                        <?= htmlspecialchars($p['nama_mobil']); ?>
                                        <div class="text-muted small">[<?= htmlspecialchars($p['plat_nomor']); ?> - <?= htmlspecialchars($p['warna']); ?>]</div>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= $durasi; ?> Hari</div>
                                        <small class="text-muted text-sm"><?= date('d/m/y', strtotime($p['tanggal_mulai'])); ?> s/d <?= date('d/m/y', strtotime($p['tanggal_selesai'])); ?></small>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-light text-dark border text-uppercase mb-1 d-inline-block"><?= htmlspecialchars($p['jenis_paket'] ?? 'harian'); ?></span>
                                        <div class="fw-bold text-orange">Rp <?= number_format($p['total_bayar'], 0, ',', '.'); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($p['status_pemesanan'] === 'dikonfirmasi'): ?>
                                            <span class="badge bg-info"><i class="bi bi-check-circle"></i> Menunggu Diambil</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary"><i class="bi bi-truck"></i> Sedang Jalan</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['status_pemesanan'] === 'dikonfirmasi'): ?>
                                            <a href="transaksi_penyewaan.php?aksi=mulai&id=<?= $p['id_pemesanan']; ?>" class="btn btn-sm btn-orange py-1 px-3 border-0 fw-semibold" onclick="return confirm('Apakah unit pickup resmi keluar garasi dan diserahkan ke penyewa?')">
                                                <i class="bi bi-box-arrow-right"></i> Lepas Kunci
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-light border small text-muted px-3" disabled>
                                                <i class="bi bi-printer"></i> Cetak Invoice
                                            </button>
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