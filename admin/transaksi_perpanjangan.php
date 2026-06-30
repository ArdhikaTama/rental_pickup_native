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
// LOGIKA PROSES TAMBAH DURASI PERPANJANGAN
// =============================================================================
if (isset($_POST['proses_perpanjangan'])) {
    $id_pemesanan   = intval($_POST['id_pemesanan']);
    $hari_tambahan  = intval($_POST['hari_tambahan']);

    if ($hari_tambahan > 0) {
        mysqli_query($koneksi, "START TRANSACTION");

        // 1. Ambil data sewa saat ini beserta harga paket per harinya
        $query_detail = mysqli_query($koneksi, "
            SELECT p.tanggal_selesai, p.total_bayar, IFNULL(pkt.harga, 250000) as harga_per_hari
            FROM pemesanan p
            LEFT JOIN paket_harga pkt ON p.id_paket = pkt.id_paket
            WHERE p.id_pemesanan = '$id_pemesanan'
        ");
        
        if (mysqli_num_rows($query_detail) > 0) {
            $data = mysqli_fetch_assoc($query_detail);
            
            // Hitung tanggal selesai baru dan biaya tambahan
            $tgl_selesai_lama = $data['tanggal_selesai'];
            $tgl_selesai_baru = date('Y-m-d H:i:s', strtotime($tgl_selesai_lama . " + $hari_tambahan days"));
            
            $biaya_tambahan   = $hari_tambahan * $data['harga_per_hari'];
            $total_bayar_baru = $data['total_bayar'] + $biaya_tambahan;

            // 2. Update data pemesanan utama
            $update_pemesanan = mysqli_query($koneksi, "
                UPDATE pemesanan 
                SET tanggal_selesai = '$tgl_selesai_baru', total_bayar = '$total_bayar_baru' 
                WHERE id_pemesanan = '$id_pemesanan'
            ");

            // 3. Update nominal tagihan di transaksi_pembayaran jika record lunas/dp sudah ada
            $update_pembayaran = mysqli_query($koneksi, "
                UPDATE transaksi_pembayaran 
                SET total_bayar = '$total_bayar_baru', status_bayar = 'belum_bayar' 
                WHERE id_pemesanan = '$id_pemesanan'
            ");

            if ($update_pemesanan && $update_pembayaran) {
                mysqli_query($koneksi, "COMMIT");
                $pesan = "<div class='alert alert-success small py-2'>Nota #PKP-$id_pemesanan berhasil diperpanjang <strong>+$hari_tambahan Hari</strong>. Tagihan terupdate otomatis.</div>";
            } else {
                mysqli_query($koneksi, "ROLLBACK");
                $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses perpanjangan durasi sewa.</div>";
            }
        }
    } else {
        $pesan = "<div class='alert alert-warning small py-2'>Jumlah hari tambahan harus minimal 1 hari.</div>";
    }
}

// =============================================================================
// QUERY DATA PENYEWAAN YANG SEDANG AKTIF DI JALAN (STATUS: berjalan)
// =============================================================================
$query_aktif = mysqli_query($koneksi, "
    SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor, IFNULL(pkt.harga, 250000) as harga_per_hari
    FROM pemesanan p
    JOIN users u ON p.id_user = u.id_user
    JOIN mobil m ON p.id_mobil = m.id_mobil
    LEFT JOIN paket_harga pkt ON p.id_paket = pkt.id_paket
    WHERE p.status_pemesanan = 'berjalan'
    ORDER BY p.id_pemesanan DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perpanjangan Sewa - Admin Panel</title>
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
            <a href="transaksi_perpanjangan.php" class="nav-link active"><i class="bi bi-clock-history"></i> Perpanjangan Sewa</a>
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
            <span class="text-muted small fw-medium">Penyesuaian Tambah Hari & Kalkulasi Ulang Tagihan Armada</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Perpanjangan Sewa</h4>
            <?= $pesan; ?>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="m-0 fw-bold text-secondary text-uppercase">Daftar Unit Aktif di Jalan (Bisa Diperpanjang)</h6>
                    <span class="badge bg-primary">Armada Operasional</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center small table-bordered m-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Nota</th>
                                <th>Nama Penyewa</th>
                                <th>Armada Mobil</th>
                                <th>Batas Selesai Saat Ini</th>
                                <th>Total Tagihan Saat Ini</th>
                                <th>Aksi Perpanjang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_aktif) == 0): ?>
                                <tr><td colspan="6" class="text-muted py-4">Tidak ada unit pickup status 'Berjalan' yang bisa diperpanjang durasinya saat ini.</td></tr>
                            <?php else: ?>
                                <?php while ($a = mysqli_fetch_assoc($query_aktif)): ?>
                                <tr>
                                    <td class="fw-bold">#PKP-<?= $a['id_pemesanan']; ?></td>
                                    <td class="text-start"><strong><?= htmlspecialchars($a['nama_lengkap']); ?></strong></td>
                                    <td class="text-start">
                                        <?= htmlspecialchars($a['nama_mobil']); ?> 
                                        <div class="text-muted small">[<?= htmlspecialchars($a['plat_nomor']); ?>]</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark border"><?= date('d M Y H:i', strtotime($a['tanggal_selesai'])); ?></span>
                                    </td>
                                    <td class="fw-bold text-orange">Rp <?= number_format($a['total_bayar'], 0, ',', '.'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-orange py-1 px-3 border-0 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPerpanjang<?= $a['id_pemesanan']; ?>">
                                            <i class="bi bi-clock-fill"></i> Tambah Hari
                                        </button>

                                        <div class="modal fade" id="modalPerpanjang<?= $a['id_pemesanan']; ?>" statistical-backdrop="static" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <form action="transaksi_perpanjangan.php" method="POST">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold"><i class="bi bi-clock-history text-orange me-2"></i>Form Perpanjangan Sewa #PKP-<?= $a['id_pemesanan']; ?></h6>
                                                            <button type="button" class="btn-close" data-bs-submit="modal" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body small">
                                                            <input type="hidden" name="id_pemesanan" value="<?= $a['id_pemesanan']; ?>">
                                                            
                                                            <div class="mb-2">
                                                                <label class="text-muted d-block">Nama Penyewa / Unit</label>
                                                                <strong><?= htmlspecialchars($a['nama_lengkap']); ?></strong> / <?= htmlspecialchars($a['nama_mobil']); ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="text-muted d-block">Tarif Paket / Hari</label>
                                                                <strong class="text-success">Rp <?= number_format($a['harga_per_hari'], 0, ',', '.'); ?></strong>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label fw-semibold">Jumlah Hari Tambahan</label>
                                                                <div class="input-group">
                                                                    <input type="number" name="hari_tambahan" class="form-control" placeholder="Contoh: 2" min="1" required>
                                                                    <span class="input-group-text">Hari</span>
                                                                </div>
                                                                <small class="text-muted mt-1 d-block">Sistem akan mengalikan otomatis dengan tarif harian armada.</small>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-sm btn-secondary border-0" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="proses_perpanjangan" class="btn btn-sm btn-orange border-0">Simpan Perpanjangan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

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