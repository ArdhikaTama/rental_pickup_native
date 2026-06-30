<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// 1. TAMBAH MOBIL BARU
if (isset($_POST['simpan_mobil'])) {
    $id_kat      = $_POST['id_kategori'];
    $nama_mobil  = mysqli_real_escape_string($koneksi, $_POST['nama_mobil']);
    $plat        = mysqli_real_escape_string($koneksi, $_POST['plat_nomor']);
    $warna       = mysqli_real_escape_string($koneksi, $_POST['warna']);
    $tahun       = $_POST['tahun_pembuatan'];

    $insert = "INSERT INTO mobil (id_kategori, nama_mobil, plat_nomor, warna, tahun_pembuatan, status_ketersediaan) 
               VALUES ('$id_kat', '$nama_mobil', '$plat', '$warna', '$tahun', 'tersedia')";

    if (mysqli_query($koneksi, $insert)) {
        $pesan = "<div class='alert alert-success small py-2'>Armada baru berhasil ditambahkan!</div>";
    } else {
        $pesan = "<div class='alert alert-danger small py-2'>Gagal menambahkan armada: " . mysqli_error($koneksi) . "</div>";
    }
}

// 2. HAPUS MOBIL
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM mobil WHERE id_mobil = '$id_hapus'")) {
        header("Location: master_mobil.php");
        exit();
    }
}

// Ambil Data Kategori & Mobil untuk Tabel dan Dropdown
$list_kategori = mysqli_query($koneksi, "SELECT * FROM kategori");
$list_mobil    = mysqli_query($koneksi, "SELECT m.*, k.nama_kategori FROM mobil m LEFT JOIN kategori k ON m.id_kategori = k.id_kategori ORDER BY m.id_mobil DESC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Mobil - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar Layout - Gray & Orange Accent */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2b2c2d;
            color: white;
            padding-top: 15px;
            z-index: 1000;
        }

        .sidebar .brand {
            padding: 10px 20px;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar .menu-section {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fd7e14;
            font-weight: bold;
            padding: 18px 20px 5px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 10px;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #fd7e14;
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
        }

        /* Main Content Wrapper */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar styling */
        .topbar {
            background: white;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .btn-orange {
            background-color: #fd7e14;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e8590c;
            color: white;
        }
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
            <span class="text-muted small fw-medium">Sistem Utama Kendali Logistik & Sewa</span>
            <div class="d-flex align-items-center gap-2 small">
                <i class="bi bi-person-gear text-orange fs-5"></i>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
            </div>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Manajemen Master Mobil</h4>
            <?= $pesan; ?>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-secondary text-uppercase">Tambah Armada</h6>
                        <form action="" method="POST">
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Kategori</label>
                                <select name="id_kategori" class="form-select form-select-sm" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <?php while ($kat = mysqli_fetch_assoc($list_kategori)): ?>
                                        <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Nama Mobil</label>
                                <input type="text" name="nama_mobil" class="form-control form-control-sm" placeholder="Contoh: L300 Bak Tinggi" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Plat Nomor</label>
                                <input type="text" name="plat_nomor" class="form-control form-control-sm" placeholder="B 1234 ABC" required>
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold mb-1">Warna</label>
                                <input type="text" name="warna" class="form-control form-control-sm" placeholder="Hitam / Putih" required>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold mb-1">Tahun Buat</label>
                                <input type="number" name="tahun_pembuatan" class="form-control form-control-sm" placeholder="2022" required>
                            </div>
                            <button type="submit" name="simpan_mobil" class="btn btn-orange btn-sm w-100 fw-bold py-2">SIMPAN UNIT</button>
                        </form>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-secondary text-uppercase">Daftar Armada Pickup</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle small text-center m-0">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Kategori</th>
                                        <th>Nama Unit</th>
                                        <th>Plat No</th>
                                        <th>Warna</th>
                                        <th>Tahun</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($list_mobil) == 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-muted py-3">Belum ada armada mobil yang diinput.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($m = mysqli_fetch_assoc($list_mobil)): ?>
                                            <tr>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($m['nama_kategori'] ?? 'N/A'); ?></span></td>
                                                <td class="fw-bold text-start text-dark"><?= htmlspecialchars($m['nama_mobil']); ?></td>
                                                <td><span class="badge bg-dark"><?= htmlspecialchars($m['plat_nomor']); ?></span></td>
                                                <td><?= htmlspecialchars($m['warna']); ?></td>
                                                <td><?= $m['tahun_pembuatan']; ?></td>
                                                <td>
                                                    <span class="badge <?= ($m['status_ketersediaan'] == 'tersedia') ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?= htmlspecialchars($m['status_ketersediaan']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="master_mobil.php?hapus=<?= $m['id_mobil']; ?>" class="text-danger fs-6" onclick="return confirm('Apakah Anda yakin ingin menghapus unit ini?')">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </a>
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