<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// ================= LOGIKA PROSES AKSI VALIDASI PESANAN MASUK =================
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_pesan = intval($_GET['id']);
    $status = ($_GET['aksi'] == 'setuju') ? 'dikonfirmasi' : 'dibatalkan';
    
    // Update status transaksi pemesanan
    $update_status = mysqli_query($koneksi, "UPDATE pemesanan SET status_pemesanan = '$status' WHERE id_pemesanan = '$id_pesan'");
    
    // Jika ditolak/dibatal, kembalikan ketersediaan armada mobil menjadi 'tersedia' kembali
    if ($status == 'dibatalkan') {
        $data_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_mobil FROM pemesanan WHERE id_pemesanan = '$id_pesan'"));
        if ($data_p) {
            $id_mob = $data_p['id_mobil'];
            mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'tersedia' WHERE id_mobil = '$id_mob'");
        }
    }
    
    header("Location: dashboard.php");
    exit();
}
// =============================================================================

// Mengambil data ringkasan untuk Info Cards
$total_unit      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mobil"))['total'];
$total_pelanggan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'penyewa'"))['total'];
$total_sewa      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pemesanan WHERE status_pemesanan = 'berjalan'"))['total'];
$total_pendapatan= mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM pemesanan WHERE status_pemesanan = 'selesai'"))['total'];

// Ambil data transaksi pesanan masuk yang butuh validasi atau sedang berjalan
$query_transaksi = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor 
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN mobil m ON p.id_mobil = m.id_mobil 
    ORDER BY p.id_pemesanan DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* Sidebar Layout - Gray & Orange Accent */
        .sidebar {
            width: 260px; height: 100vh; position: fixed; top: 0; left: 0;
            background-color: #2b2c2d; color: white; padding-top: 15px; z-index: 1000;
        }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 18px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; border-radius: 4px; margin: 0 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; font-weight: 500; }
        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        
        /* Main Content Wrapper */
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Topbar styling */
        .topbar {
            background: white; height: 60px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }
        
        /* Modern Info Cards */
        .card-counter {
            background: white; border: none; border-radius: 8px; padding: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between;
        }
        .card-counter .icon-box {
            width: 48px; height: 48px; border-radius: 6px; display: flex;
            align-items: center; justify-content: center; font-size: 1.5rem;
        }
        .bg-light-orange { background-color: rgba(253, 126, 20, 0.15); color: #fd7e14; }
        .bg-light-blue { background-color: rgba(13, 110, 253, 0.15); color: #0d6efd; }
        .bg-light-success { background-color: rgba(25, 135, 84, 0.15); color: #198754; }
        .bg-light-purple { background-color: rgba(111, 66, 193, 0.15); color: #6f42c1; }

        .card-table { background: white; border: none; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); padding: 24px; }
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
            
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            
            <div class="menu-section">Data Master (8)</div>
            <a href="master_mobil.php" class="nav-link"><i class="bi bi-truck"></i> Master Mobil</a>
            <a href="master_kategori.php" class="nav-link"><i class="bi bi-tags"></i> Kategori & Paket</a>
            <a href="master_sopir.php" class="nav-link"><i class="bi bi-person-badge"></i> Data Sopir</a>
            <a href="master_rekening.php" class="nav-link"><i class="bi bi-credit-card"></i> Rekening Bank</a>
            <a href="master_jaminan.php" class="nav-link"><i class="bi bi-collection"></i> Jenis Jaminan</a>
            
            <div class="menu-section">Alur Transaksi (5)</div>
            <a href="transaksi_pemesanan.php" class="nav-link"><i class="bi bi-receipt"></i> Pemesanan Baru</a>
            <a href="transaksi_pembayaran.php" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran</a>
            <a href="transaksi_pengembalian.php" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
            
            <div class="menu-section">Pelaporan</div>
            <a href="laporan_pendapatan.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Laporan Pendapatan</a>
            <a href="laporan_pengeluaran.php" class="nav-link"><i class="bi bi-graph-down-arrow"></i> Laporan Pengeluaran</a>
            <a href="laporan_rekapitulasi.php" class="nav-link"><i class="bi bi-journal-check"></i> Rekapitulasi Total</a>
        </div>

        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Keluar dari panel admin?')">
                <i class="bi bi-box-arrow-right text-danger"></i> Sign Out
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Sistem Utama Kendali Logistik & Sewa</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0 text-dark fw-normal">Dashboard Ringkasan</h4>
                <span class="text-muted small"><?= date('l, d F Y') ?></span>
            </div>
            
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card-counter">
                        <div>
                            <span class="text-muted small d-block mb-1">Total Armada</span>
                            <h3 class="fw-bold m-0"><?= $total_unit ?></h3>
                        </div>
                        <div class="icon-box bg-light-orange"><i class="bi bi-truck"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-counter">
                        <div>
                            <span class="text-muted small d-block mb-1">Total Pelanggan</span>
                            <h3 class="fw-bold m-0"><?= $total_pelanggan ?></h3>
                        </div>
                        <div class="icon-box bg-light-blue"><i class="bi bi-people"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-counter">
                        <div>
                            <span class="text-muted small d-block mb-1">Sedang Disewa</span>
                            <h3 class="fw-bold m-0"><?= $total_sewa ?></h3>
                        </div>
                        <div class="icon-box bg-light-purple"><i class="bi bi-arrow-repeat"></i></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-counter">
                        <div>
                            <span class="text-muted small d-block mb-1">Omset Selesai</span>
                            <h4 class="fw-bold m-0 text-success">Rp <?= number_format($total_pendapatan ?? 0, 0, ',', '.') ?></h4>
                        </div>
                        <div class="icon-box bg-light-success"><i class="bi bi-currency-dollar"></i></div>
                    </div>
                </div>
            </div>

            <div class="card card-table">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold text-secondary text-uppercase">Aktivitas Operasional Terakhir</h6>
                    <span class="badge bg-dark">Realtime Sync</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle text-center small table-hover">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Nama Penyewa</th>
                                <th>Armada</th>
                                <th>Total Tagihan</th>
                                <th>Status Pemesanan</th>
                                <th>Opsi Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($query_transaksi) == 0): ?>
                                <tr><td colspan="6" class="text-muted py-3">Belum ada log transaksi masuk.</td></tr>
                            <?php else: ?>
                                <?php while($t = mysqli_fetch_assoc($query_transaksi)): ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $t['id_pemesanan'] ?></td>
                                    <td><?= htmlspecialchars($t['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($t['nama_mobil']) ?> <span class="text-muted">(<?= $t['plat_nomor'] ?>)</span></td>
                                    <td class="fw-bold text-orange">Rp <?= number_format($t['total_bayar'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php 
                                        if ($t['status_pemesanan'] == 'pending') echo '<span class="badge bg-warning text-dark">Pending</span>';
                                        elseif ($t['status_pemesanan'] == 'dikonfirmasi') echo '<span class="badge bg-info">Dikonfirmasi</span>';
                                        elseif ($t['status_pemesanan'] == 'berjalan') echo '<span class="badge bg-primary">Berjalan</span>';
                                        elseif ($t['status_pemesanan'] == 'selesai') echo '<span class="badge bg-success">Selesai</span>';
                                        else echo '<span class="badge bg-danger">Batal</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($t['status_pemesanan'] == 'pending'): ?>
                                            <a href="dashboard.php?aksi=setuju&id=<?= $t['id_pemesanan']; ?>" class="btn btn-sm btn-success py-1 px-2 border-0" onclick="return confirm('Setujui transaksi rental pickup ini?')">
                                                <i class="bi bi-check-circle-fill"></i> Setuju
                                            </a>
                                            <a href="dashboard.php?aksi=batal&id=<?= $t['id_pemesanan']; ?>" class="btn btn-sm btn-danger py-1 px-2 border-0" onclick="return confirm('Batalkan/Tolak pengajuan sewa ini?')">
                                                <i class="bi bi-x-circle-fill"></i> Tolak
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small fw-medium"><i class="bi bi-shield-check text-success"></i> Selesai Validasi</span>
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