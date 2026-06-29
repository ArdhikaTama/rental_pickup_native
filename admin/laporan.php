<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Mengatur filter default ke bulan dan tahun berjalan
$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query mengambil data transaksi selesai berdasarkan bulan dan tahun berelasi
$query_laporan = "SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor 
                  FROM pemesanan p
                  JOIN users u ON p.id_user = u.id_user
                  JOIN mobil m ON p.id_mobil = m.id_mobil
                  WHERE MONTH(p.tanggal_mulai) = '$bulan_pilihan' 
                  AND YEAR(p.tanggal_mulai) = '$tahun_pilihan'
                  AND p.status_pemesanan = 'selesai'
                  ORDER BY p.id_pemesanan ASC";
$result_laporan = mysqli_query($koneksi, $query_laporan);

// Menghitung ringkasan finansial bulanan
$total_omset = 0;
$total_transaksi = mysqli_num_rows($result_laporan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pendapatan Bulanan - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        .sidebar { width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background-color: #2b2c2d; color: white; padding-top: 15px; z-index: 1000; }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 18px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; margin: 0 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; }
        .sidebar .nav-link i { margin-right: 12px; font-size: 1.1rem; }
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { background: white; height: 60px; display: flex; align-items: center; justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); }
        .btn-orange { background-color: #fd7e14; color: white; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
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
            <div class="menu-section">Data Master</div>
            <a href="master_mobil.php" class="nav-link"><i class="bi bi-truck"></i> Master Mobil</a>
            <div class="menu-section">Pelaporan</div>
            <a href="laporan.php" class="nav-link active"><i class="bi bi-file-earmark-bar-graph"></i> Laporan Bulanan</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Sistem Rekapitulasi Pembukuan Omset</span>
            <span class="fw-semibold text-dark small"><i class="bi bi-person-gear text-orange"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Laporan Bulanan</h4>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3 mb-4">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Pilih Bulan</label>
                        <select name="bulan" class="form-select">
                            <?php
                            $nama_bulan = [
                                "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April",
                                "05" => "Mei", "06" => "Juni", "07" => "Agustus", "08" => "Agustus",
                                "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
                            ];
                            foreach ($nama_bulan as $key => $val) {
                                $selected = ($key == $bulan_pilihan) ? "selected" : "";
                                echo "<option value='$key' $selected>$val</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary">Pilih Tahun</label>
                        <select name="tahun" class="form-select">
                            <?php
                            $tahun_sekarang = date('Y');
                            for ($i = $tahun_sekarang; $i >= $tahun_sekarang - 3; $i--) {
                                $selected = ($i == $tahun_pilihan) ? "selected" : "";
                                echo "<option value='$i' $selected>$i</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100"><i class="bi bi-filter"></i> Filter Data</button>
                        <a href="cetak_laporan.php?bulan=<?= $bulan_pilihan ?>&tahun=<?= $tahun_pilihan ?>" target="_blank" class="btn btn-orange w-100 fw-bold"><i class="bi bi-printer"></i> Cetak Laporan</a>
                    </div>
                </form>
            </div>

            <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle small text-center">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>No. Nota</th>
                                <th>Nama Penyewa</th>
                                <th>Armada</th>
                                <th>Plat Nomor</th>
                                <th>Tanggal Selesai</th>
                                <th>Pendapatan Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($total_transaksi == 0): ?>
                                <tr><td colspan="6" class="text-muted py-4">Tidak ada data transaksi sewa yang selesai pada periode bulan ini.</td></tr>
                            <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($result_laporan)): 
                                    $total_omset += $row['total_bayar']; ?>
                                    <tr>
                                        <td class="fw-bold">#PKP-<?= $row['id_pemesanan']; ?></td>
                                        <td><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                        <td><?= htmlspecialchars($row['nama_mobil']); ?></td>
                                        <td><span class="badge bg-dark"><?= $row['plat_nomor']; ?></span></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal_selesai'])); ?></td>
                                        <td class="fw-bold text-orange">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                <tr class="table-light fw-bold text-end">
                                    <td colspan="5" class="text-uppercase text-secondary small">Total Pendapatan Finansial :</td>
                                    <td class="text-center text-success fs-6">Rp <?= number_format($total_omset, 0, ',', '.'); ?></td>
                                endwhile;
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>