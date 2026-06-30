<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// 1. PROSES TAMBAH SOPIR
if (isset($_POST['simpan_sopir'])) {
    $nama_sopir = mysqli_real_escape_string($koneksi, $_POST['nama_sopir']);
    $no_telp    = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $status     = $_POST['status_tersedia'];

    $insert = "INSERT INTO sopir (nama_sopir, no_telp, status_tersedia) VALUES ('$nama_sopir', '$no_telp', '$status')";
    if (mysqli_query($koneksi, $insert)) {
        $pesan = "<div class='alert alert-success small py-2'>Data sopir berhasil ditambahkan!</div>";
    }
}

// 2. PROSES HAPUS SOPIR
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM sopir WHERE id_sopir = '$id_hapus'");
    header("Location: master_sopir.php");
    exit();
}

// Ambil Data Sopir untuk Tabel
$list_sopir = mysqli_query($koneksi, "SELECT * FROM sopir ORDER BY id_sopir DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Data Sopir - Admin Panel</title>
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
        .btn-orange { background-color: #fd7e14; color: white; }
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
            <a href="master_sopir.php" class="nav-link active"><i class="bi bi-person-badge"></i> Data Sopir</a>
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
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Keluar dari panel admin?')"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Sistem Utama Kendali Logistik & Sewa</span>
            <span class="fw-semibold text-dark small"><i class="bi bi-person-gear text-orange"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Data Sopir</h4>
            <?= $pesan; ?>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-secondary text-uppercase">Tambah Sopir</h6>
                        <form action="" method="POST">
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Nama Lengkap Sopir</label>
                                <input type="text" name="nama_sopir" class="form-control form-control-sm" placeholder="Contoh: Ahmad Subarjo" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Nomor Telepon/WA</label>
                                <input type="text" name="no_telp" class="form-control form-control-sm" placeholder="Contoh: 0812xxxxxxxx" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-1">Status Ketersediaan</label>
                                <select name="status_terresedia" class="form-select form-select-sm" required>
                                    <option value="tersedia">Tersedia</option>
                                    <option value="bertugas">Sedang Bertugas</option>
                                </select>
                            </div>
                            <button type="submit" name="simpan_sopir" class="btn btn-orange btn-sm w-100 fw-bold py-2">SIMPAN SOPIR</button>
                        </form>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-secondary text-uppercase">Daftar Sopir Aktif</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle small text-center m-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>ID Sopir</th>
                                        <th>Nama Sopir</th>
                                        <th>No. Telepon</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($list_sopir) == 0): ?>
                                        <tr><td colspan="5" class="text-muted py-3">Belum ada data sopir.</td></tr>
                                    <?php else: ?>
                                        <?php while ($s = mysqli_fetch_assoc($list_sopir)): ?>
                                        <tr>
                                            <td>#SPR-<?= $s['id_sopir']; ?></td>
                                            <td class="fw-bold text-dark text-start"><?= htmlspecialchars($s['nama_sopir']); ?></td>
                                            <td><?= htmlspecialchars($s['no_telp']); ?></td>
                                            <td>
                                                <span class="badge <?= ($s['status_tersedia'] == 'tersedia') ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                                    <?= htmlspecialchars($s['status_tersedia']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="master_sopir.php?hapus=<?= $s['id_sopir']; ?>" class="text-danger" onclick="return confirm('Hapus sopir ini?')"><i class="bi bi-trash-fill"></i></a>
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
    </div>
</body>
</html>