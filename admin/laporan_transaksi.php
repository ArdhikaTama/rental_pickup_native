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

// Default value untuk filter tanggal (awal bulan ini s/d hari ini)
$tgl_awal  = isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$status    = isset($_GET['status']) ? $_GET['status'] : 'semua';

// Membangun Query dengan Kondisi Filter Dinamis
$kondisi = "WHERE p.tanggal_booking BETWEEN '$tgl_awal 00:00:00' AND '$tgl_akhir 23:59:59'";

if ($status !== 'semua') {
    $kondisi .= " AND p.status_pemesanan = '" . mysqli_real_escape_string($koneksi, $status) . "'";
}

$query_laporan = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor
    FROM pemesanan p
    JOIN users u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    $kondisi
    ORDER BY p.id_pemesanan DESC
");

// Hitung Ringkasan Akumulasi Pendapatan Terfilter
$total_pendapatan_filter = 0;
$jumlah_transaksi_filter = mysqli_num_rows($query_laporan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Admin Panel</title>
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
        
        /* CSS Khusus Mode Cetak Printer */
        @media print {
            .sidebar, .topbar, .filter-box, .btn-print-action, footer { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .card { border: none !important; box-shadow: none !important; padding: 0 !important; }
            .table-responsive { overflow: visible !important; }
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
            <a href="laporan_transaksi.php" class="nav-link active"><i class="bi bi-graph-up-arrow"></i> Laporan Transaksi</a>
            <a href="laporan_pendapatan.php" class="nav-link"><i class="bi bi-cash-stack"></i> Laporan Pendapatan</a>
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
            <span class="text-muted small fw-medium">Pusat Rekapitulasi Data Transaksi dan Pencetakan Struk Manajerial</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            
            <div class="print-header text-center mb-4 border-bottom pb-3">
                <h3 class="fw-bold mb-1 text-uppercase">Laporan Rekapitulasi Transaksi Rental</h3>
                <h5 class="fw-normal text-secondary m-0">Premium Pickup System - Jakarta</h5>
                <small class="text-muted">Periode Filter: <?= date('d/m/Y', strtotime($tgl_awal)) ?> s/d <?= date('d/m/Y', strtotime($tgl_akhir)) ?> | Status: <?= strtoupper($status) ?></small>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 btn-print-action">
                <h4 class="m-0 text-dark fw-normal">Laporan Seluruh Transaksi</h4>
                <button type="button" onclick="window.print()" class="btn btn-sm btn-dark px-3 fw-bold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Cetak Dokumen / PDF
                </button>
            </div>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4 filter-box">
                <h6 class="fw-bold mb-3 text-secondary text-uppercase"><i class="bi bi-funnel-fill text-orange me-1"></i>Penyaringan Rentang Data</h6>
                <form action="laporan_transaksi.php" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Tanggal Awal</label>
                        <input type="date" name="tgl_awal" class="form-control form-control-sm" value="<?= $tgl_awal; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Tanggal Akhir</label>
                        <input type="date" name="tgl_akhir" class="form-control form-control-sm" value="<?= $tgl_akhir; ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1 fw-semibold">Status Sewa</label>
                        <select name="status" class="form-select form-select-sm">
                            <option value="semua" <?= $status == 'semua' ? 'selected' : '' ?>>-- Semua Status --</option>
                            <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending (Antrean)</option>
                            <option value="dikonfirmasi" <?= $status == 'dikonfirmasi' ? 'selected' : '' ?>>Dikonfirmasi</option>
                            <option value="berjalan" <?= $status == 'berjalan' ? 'selected' : '' ?>>Berjalan (Aktif)</option>
                            <option value="selesai" <?= $status == 'selesai' ? 'selected' : '' ?>>Selesai (Tutup Buku)</option>
                            <option value="dibatalkan" <?= $status == 'dibatalkan' ? 'selected' : '' ?>>Batal / Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-sm btn-orange w-100 fw-bold py-2"><i class="bi bi-search me-1"></i> FILTER DATA</button>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle text-center small table-bordered m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Tanggal Input</th>
                                <th>Nama Penyewa</th>
                                <th>Armada Unit</th>
                                <th>Durasi Hari</th>
                                <th>Status Akhir</th>
                                <th>Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($jumlah_transaksi_filter == 0): ?>
                                <tr><td colspan="7" class="text-muted py-4">Tidak ditemukan arsip transaksi pada parameter filter tanggal ini.</td></tr>
                            <?php else: ?>
                                <?php 
                                while ($l = mysqli_fetch_assoc($query_laporan)): 
                                    $t1 = new DateTime($l['tanggal_mulai']);
                                    $t2 = new DateTime($l['tanggal_selesai']);
                                    $dur = $t1->diff($t2)->days;
                                    $dur = ($dur == 0) ? 1 : $dur;

                                    // Akumulasi total omset kotor terfilter jika statusnya valid lunas/selesai/berjalan
                                    if ($l['status_pemesanan'] !== 'dibatalkan' && $l['status_pemesanan'] !== 'pending') {
                                        $total_pendapatan_filter += $l['total_bayar'];
                                    }
                                ?>
                                <tr>
                                    <td class="fw-bold font-monospace">#PKP-<?= $l['id_pemesanan'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($l['tanggal_booking'])) ?></td>
                                    <td class="text-start"><strong><?= htmlspecialchars($l['nama_lengkap']) ?></strong></td>
                                    <td class="text-start"><?= htmlspecialchars($l['nama_mobil']) ?> <span class="text-muted">([<?= htmlspecialchars($l['plat_nomor']) ?>])</span></td>
                                    <td><?= $dur ?> Hari</td>
                                    <td>
                                        <?php 
                                        if ($l['status_pemesanan'] === 'pending') echo '<span class="text-warning fw-bold">Pending</span>';
                                        elseif ($l['status_pemesanan'] === 'dikonfirmasi') echo '<span class="text-info fw-bold">Dikonfirmasi</span>';
                                        elseif ($l['status_pemesanan'] === 'berjalan') echo '<span class="text-primary fw-bold">Berjalan</span>';
                                        elseif ($l['status_pemesanan'] === 'selesai') echo '<span class="text-success fw-bold">Selesai</span>';
                                        else echo '<span class="text-danger fw-bold">Batal</span>';
                                        ?>
                                    </td>
                                    <td class="fw-bold text-end text-dark">Rp <?= number_format($l['total_bayar'], 0, ',', '.') ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="table-dark fw-bold text-end">
                                    <td colspan="6" class="text-center text-uppercase">Total Akumulasi Perputaran Kas Terfilter:</td>
                                    <td>Rp <?= number_format($total_pendapatan_filter, 0, ',', '.') ?></td>
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