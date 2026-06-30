<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// 1. PROSES TAMBAH KATEGORI MOBIL
if (isset($_POST['simpan_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $keterangan    = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $insert_kat = "INSERT INTO kategori (nama_kategori, keterangan) VALUES ('$nama_kategori', '$keterangan')";
    if (mysqli_query($koneksi, $insert_kat)) {
        $pesan = "<div class='alert alert-success small py-2'>Kategori baru berhasil ditambahkan!</div>";
    }
}

// 2. PROSES TAMBAH TARIF SEWA (PAKET HARGA)
if (isset($_POST['simpan_paket'])) {
    $id_mobil    = $_POST['id_mobil'];
    $jenis_paket = $_POST['jenis_paket'];
    $harga       = $_POST['harga'];

    $insert_paket = "INSERT INTO paket_harga (id_mobil, jenis_paket, harga) VALUES ('$id_mobil', '$jenis_paket', '$harga')";
    if (mysqli_query($koneksi, $insert_paket)) {
        $pesan = "<div class='alert alert-success small py-2'>Tarif paket harga berhasil dikonfigurasi!</div>";
    }
}

// 3. PROSES HAPUS (Kategori / Paket)
if (isset($_GET['hapus_kat'])) {
    $id_hapus = intval($_GET['hapus_kat']);
    mysqli_query($koneksi, "DELETE FROM kategori WHERE id_kategori = '$id_hapus'");
    header("Location: master_kategori.php");
    exit();
}
if (isset($_GET['hapus_pkt'])) {
    $id_hapus = intval($_GET['hapus_pkt']);
    mysqli_query($koneksi, "DELETE FROM paket_harga WHERE id_paket = '$id_hapus'");
    header("Location: master_kategori.php");
    exit();
}

// Ambil data untuk komponen Dropdown & Tabel
$list_mobil     = mysqli_query($koneksi, "SELECT * FROM mobil");
$table_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$table_paket    = mysqli_query($koneksi, "SELECT p.*, m.nama_mobil, m.plat_nomor FROM paket_harga p JOIN mobil m ON p.id_mobil = m.id_mobil ORDER BY p.id_paket DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori & Paket Tarif - Admin Panel</title>
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
        <a href="transaksi_perpanjangan.php" class="nav-link"><i class="bi bi-clock-history"></i> Perpanjangan Sewa</a>
        <a href="transaksi_pengembalian.php" class="nav-link"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
        
        <div class="menu-section">Pelaporan & Data</div>
        <a href="laporan_transaksi.php" class="nav-link"><i class="bi bi-graph-up-arrow"></i> Laporan Seluruhnya</a>
        <a href="utilitas_data.php" class="nav-link"><i class="bi bi-file-earmark-excel"></i> Import & Export Data</a>
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
            <span class="text-muted small fw-medium">Manajemen Struktur Kategori & Tarif Logistik</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Kategori & Tarif Sewa</h4>
            <?= $pesan; ?>

            <div class="row g-4">
                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Tambah Kategori Pickup</h6>
                        <form action="" method="POST" class="row g-2">
                            <div class="col-md-5">
                                <input type="text" name="nama_kategori" class="form-control form-control-sm" placeholder="Nama Kategori (Contoh: L300)" required>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Keterangan Spesifikasi">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="simpan_kategori" class="btn btn-orange btn-sm w-100 fw-bold">Simpan</button>
                            </div>
                        </form>
                    </div>

                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Daftar Kategori</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small text-center table-bordered m-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Nama Kategori</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($table_kategori) == 0): ?>
                                        <tr><td colspan="3" class="text-muted py-3">Belum ada kategori terdaftar.</td></tr>
                                    <?php else: ?>
                                        <?php while($kat = mysqli_fetch_assoc($table_kategori)): ?>
                                        <tr>
                                            <td class="fw-bold text-dark"><?= htmlspecialchars($kat['nama_kategori']) ?></td>
                                            <td class="text-start"><?= htmlspecialchars($kat['keterangan']) ?></td>
                                            <td>
                                                <a href="master_kategori.php?hapus_kat=<?= $kat['id_kategori'] ?>" class="text-danger fs-6" onclick="return confirm('Hapus kategori ini?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Atur Tarif Sewa Armada</h6>
                        <form action="" method="POST" class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="small text-muted mb-1 fw-semibold">Pilih Unit Mobil</label>
                                <select name="id_mobil" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Unit --</option>
                                    <?php while($m = mysqli_fetch_assoc($list_mobil)): ?>
                                        <option value="<?= $m['id_mobil'] ?>"><?= htmlspecialchars($m['nama_mobil']) ?> (<?= $m['plat_nomor'] ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1 fw-semibold">Jenis Paket</label>
                                <select name="jenis_paket" class="form-select form-select-sm" required>
                                    <option value="harian">Harian</option>
                                    <option value="mingguan">Mingguan</option>
                                    <option value="bulanan">Bulanan</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small text-muted mb-1 fw-semibold">Harga Tarif (Rp)</label>
                                <input type="number" name="harga" class="form-control form-control-sm" placeholder="350000" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" name="simpan_paket" class="btn btn-dark btn-sm w-100 fw-bold">Set Tarif</button>
                            </div>
                        </form>
                    </div>

                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Daftar Skema Tarif Sewa Aktif</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small text-center table-bordered m-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Armada Mobil</th>
                                        <th>Paket</th>
                                        <th>Nominal Tarif</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($table_paket) == 0): ?>
                                        <tr><td colspan="4" class="text-muted py-3">Belum ada skema harga sewa yang diatur.</td></tr>
                                    <?php else: ?>
                                        <?php while($pkt = mysqli_fetch_assoc($table_paket)): ?>
                                        <tr>
                                            <td class="text-start"><strong><?= htmlspecialchars($pkt['nama_mobil']) ?></strong> <span class="text-muted text-sm">(<?= htmlspecialchars($pkt['plat_nomor']) ?>)</span></td>
                                            <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($pkt['jenis_paket']) ?></span></td>
                                            <td class="fw-bold text-orange">Rp <?= number_format($pkt['harga'], 0, ',', '.') ?></td>
                                            <td>
                                                <a href="master_kategori.php?hapus_pkt=<?= $pkt['id_paket'] ?>" class="text-danger fs-6" onclick="return confirm('Hapus paket tarif ini?')"><i class="bi bi-trash"></i></a>
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

        <footer class="bg-white text-center py-3 text-muted small border-top mt-auto">
            Admin Console Management © Rental Pickup JKT 2026
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>