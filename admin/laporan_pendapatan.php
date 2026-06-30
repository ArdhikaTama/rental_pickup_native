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

// Default value untuk filter rentang tanggal pendapatan
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');

// Query untuk menarik seluruh dana kas sewa masuk yang sudah terkonfirmasi sah pembayarannya
$query_pendapatan = mysqli_query($koneksi, "
    SELECT p.id_pemesanan, p.tanggal_booking, p.total_bayar, u.nama_lengkap, m.nama_mobil,
           tp.tgl_bayar, tp.status_bayar
    FROM pemesanan p
    JOIN users u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    JOIN transaksi_pembayaran tp ON p.id_pemesanan = tp.id_pemesanan
    WHERE tp.status_bayar = 'lunas' 
      AND tp.tgl_bayar BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'
    ORDER BY tp.tgl_bayar DESC
");

// Kalkulasi Total Akumulasi Kas Omzet Masuk
$total_omzet = 0;
$jumlah_transaksi = mysqli_num_rows($query_pendapatan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan - Admin Panel</title>
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
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        
        @media print {
            .sidebar, .topbar, .filter-box, .btn-print-action, footer { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; }
            .print-header { display: block !important; }
        }
        .print-header { display: none; }
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
            <a href="transaksi_penyewaan.php" class="nav-link"><i class="bi bi-receipt"></i> Transaksi Penyewaan</a>
            <a href="transaksi_pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran & DP</a>
            <a href="transaksi_perpanjangan.php" class="nav-link"><i class="bi bi-clock-history"></i> Perpanjangan Sewa</a>
            <a href="transaksi_pengembalian.php" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
            
            <div class="menu-section">Pelaporan & Analisis</div>
            <a href="laporan_transaksi.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Laporan Transaksi</a>
            <a href="laporan_pendapatan.php" class="nav-link active"><i class="bi bi-cash-stack"></i> Laporan Pendapatan</a>
            <a href="laporan_pengeluaran.php" class="nav-link"><i class="bi bi-cash-coin"></i> Laporan Pengeluaran</a>
            <a href="laporan_rekapitulasi.php" class="nav-link"><i class="bi bi-journal-check"></i> Rekapitulasi Total</a>
            <a href="utilitas_data.php" class="nav-link"><i class="bi bi-file-earmark-excel"></i> Import & Export Data</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Keluar dari panel admin?')"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Audit Arus Kas Masuk & Pembukuan Finansial Omzet Rental</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            
            <div class="print-header text-center mb-4 border-bottom pb-2">
                <h4 class="fw-bold mb-1 text-uppercase">Laporan Finansial Kas Pendapatan (Omzet)</h4>
                <h6 class="text-secondary m-0 fw-normal">Premium Pickup Fleet Management</h6>
                <small class="text-muted">Periode Audit: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?></small>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 btn-print-action">
                <h4 class="m-0 text-dark fw-normal">Laporan Arus Kas Masuk</h4>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-dark px-3 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Kas Pendapatan
                </button>
            </div>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4 filter-box">
                <h6 class="fw-bold mb-3 text-secondary text-uppercase"><i class="bi bi-funnel-fill text-orange me-1"></i>Filter Periode Buku Kas</h6>
                <form action="laporan_pendapatan.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1 fw-semibold">Dari Tanggal Pembayaran</label>
                        <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?= $tgl_awal; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1 fw-semibold">Sampai Tanggal Pembayaran</label>
                        <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= $tgl_akhir; ?>" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-sm btn-orange w-100 fw-bold py-2"><i class="bi bi-check-circle me-1"></i> HITUNG PENDAPATAN</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered align-middle text-center small m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota Sewa</th>
                                <th>Tanggal Bayar</th>
                                <th>Nama Penyewa</th>
                                <th>Armada Mobil</th>
                                <th>Status Pembayaran</th>
                                <th>Nominal Masuk</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jumlah_transaksi == 0): ?>
                                <tr><td colspan="6" class="text-muted py-4">Belum ada kas pendapatan sewa yang terverifikasi lunas pada periode ini.</td></tr>
                            <?php else: ?>
                                <?php 
                                while ($row = mysqli_fetch_assoc($query_pendapatan)): 
                                    $total_omzet += $row['total_bayar'];
                                ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $row['id_pemesanan']; ?></td>
                                    <td class="font-monospace"><?= date('d/m/Y H:i', strtotime($row['tgl_bayar'])); ?></td>
                                    <td class="text-start"><strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong></td>
                                    <td class="text-start"><?= htmlspecialchars($row['nama_mobil']); ?></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success px-2 py-1">LUNAS</span></td>
                                    <td class="fw-bold text-end text-success">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="table-success fw-bold text-end text-dark">
                                    <td colspan="5" class="text-center text-uppercase py-2">Total Kas Masuk Terpembukuan (Omzet Kotor):</td>
                                    <td class="text-success fs-6">Rp <?= number_format($total_omzet, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <footer class="bg-white text-center py-3 text-muted small border-top mt-auto">
        Admin Console Management © Rental Pickup JKT 2026
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>