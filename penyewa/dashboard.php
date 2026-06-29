<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];

// Ambil data profil lengkap user dari database
$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query_user);

// Ambil data transaksi sewa untuk tabel Status Transaksi
$query_booking = "SELECT p.*, m.nama_mobil, m.plat_nomor 
                  FROM pemesanan p
                  JOIN mobil m ON p.id_mobil = m.id_mobil
                  WHERE p.id_user = '$id_user'
                  ORDER BY p.id_pemesanan DESC LIMIT 5";
$result_booking = mysqli_query($koneksi, $query_booking);

// Ambil data transaksi jaminan untuk tabel Status Jaminan
$query_jaminan = "SELECT p.id_pemesanan, m.nama_mobil, j.nama_jaminan, dj.nomor_dokumen_jaminan 
                  FROM detail_jaminan dj
                  JOIN pemesanan p ON dj.id_pemesanan = p.id_pemesanan
                  JOIN mobil m ON p.id_mobil = m.id_mobil
                  JOIN jenis_jaminan j ON dj.id_jaminan = j.id_jaminan
                  WHERE p.id_user = '$id_user'
                  ORDER BY p.id_pemesanan DESC LIMIT 5";
$result_jaminan = mysqli_query($koneksi, $query_jaminan);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penyewa - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        /* Sidebar layout matching image_b1527f.png but with Landing Page Orange/Grey Theme */
        .sidebar {
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #333333;
            color: white;
            padding-top: 15px;
            z-index: 1000;
        }

        .sidebar .brand {
            padding: 10px 20px;
            font-size: 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .menu-section {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #fd7e14;
            font-weight: bold;
            padding: 15px 20px 5px;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 10px 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #fd7e14;
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.1rem;
        }

        /* Main Content Wrapper */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Topbar matching image_b1527f.png */
        .topbar {
            background: white;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* Cards & Buttons custom adjustments */
        .card-custom {
            background: white;
            border: none;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .card-profile img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 3px solid #fd7e14;
        }

        .profile-info p {
            margin-bottom: 8px;
            font-size: 0.85rem;
            color: #555;
            display: flex;
            align-items: center;
        }

        .profile-info i {
            color: #fd7e14;
            width: 25px;
            font-size: 1rem;
        }

        .btn-orange {
            background-color: #fd7e14;
            color: white;
        }

        .btn-orange:hover {
            background-color: #e8590c;
            color: white;
        }

        .text-orange {
            color: #fd7e14 !important;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 3px 10px;
            border-radius: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR WITH ORANGE ACCENTS -->
    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="brand fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-truck-flatbed me-2 fs-4 text-orange"></i>
                <div>
                    <span class="d-block lh-1 small opacity-75 text-white">PREMIUM</span>
                    <span class="fs-6 text-uppercase text-orange">Pickup Rental</span>
                </div>
            </div>

            <a href="dashboard.php" class="nav-link active"><i class="bi bi-grid-1x2-fill"></i> Beranda</a>

            <div class="menu-section">Transaksi Logistik</div>
            <a href="pesan_mobil.php" class="nav-link"><i class="bi bi-file-earmark-plus"></i> Form Sewa Mobil</a>
            <a href="#" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a>

            <div class="menu-section">Verifikasi Jaminan</div>
            <a href="#" class="nav-link"><i class="bi bi-shield-check"></i> Status Serah Jaminan</a>

            <div class="menu-section">Layanan Pelanggan</div>
            <a href="#" class="nav-link"><i class="bi bi-chat-square-text"></i> Komplain & Saran</a>
            <a href="#" class="nav-link"><i class="bi bi-question-circle"></i> FAQ</a>
        </div>

        <!-- OUTSIDE ACTION / LOGOUT BUTTON AT THE SIDEBAR BOTTOM -->
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-right text-danger"></i> Keluar (Sign Out)
            </a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <span class="text-muted fw-medium small">Sistem Informasi Manajemen Armada Rental Pickup</span>
            <div class="d-flex align-items-center gap-3 small">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-person-circle fs-5 text-orange"></i>
                    <span class="fw-semibold text-dark"><?= htmlspecialchars($user['nama_lengkap']); ?></span>
                </div>
                <div class="border-start ps-3">
                    <a href="../logout.php" class="text-danger text-decoration-none" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                        <i class="bi bi-power"></i> Exit
                    </a>
                </div>
            </div>
        </div>

        <!-- DASHBOARD CONTAINER -->
        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Beranda</h4>

            <div class="row">
                <!-- SISI KIRI: KARTU PROFIL PENYEWA -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card card-custom card-profile p-4">
                        <div class="text-center mb-3">
                            <img src="https://via.placeholder.com/150/cccccc/ffffff?text=User" alt="Foto Profil" class="mb-3">
                            <h5 class="fw-bold text-orange mb-1"><?= htmlspecialchars($user['nama_lengkap']); ?></h5>
                            <span class="text-muted small">Penyewa / Anggota</span>
                        </div>
                        <hr class="text-muted opacity-25">
                        <div class="profile-info ps-2">
                            <p><i class="bi bi-calendar3"></i> Terdaftar sejak: <?= date('d M Y', strtotime($user['created_at'])); ?></p>
                            <p><i class="bi bi-card-text"></i> ID User: #USR-<?= $user['id_user']; ?></p>
                            <p><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($user['alamat']); ?></p>
                            <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($user['email']); ?></p>
                            <p><i class="bi bi-telephone"></i> <?= htmlspecialchars($user['no_telp']); ?></p>
                            <div class="mt-3">
                                <a href="edit_profil.php" class="btn btn-sm btn-outline-secondary w-100"><i class="bi bi-gear-fill me-1"></i> Ubah Profil Anda</a>
                            </div>
                        </div>
                        <div class="text-center mt-3 pt-2 border-top">
                            <small class="text-muted opacity-75">Sistem Autentikasi Menggunakan Role Berelasi</small>
                        </div>
                    </div>
                </div>

                <!-- SISI KANAN: TABEL STATUS TRANSAKSI & JAMINAN -->
                <div class="col-xl-8 col-lg-7">

                    <!-- TABEL 1: STATUS TRANSAKSI -->
                    <div class="card card-custom p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 fw-bold text-secondary">Status Transaksi Rental Terakhir</h6>
                            <a href="pesan_mobil.php" class="btn btn-sm btn-orange py-1 px-3 shadow-sm"><i class="bi bi-plus-lg"></i> Sewa Baru</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle text-center small">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Kode</th>
                                        <th>Armada</th>
                                        <th>Total Biaya</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result_booking) == 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-muted py-3">Belum ada transaksi sewa.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($row = mysqli_fetch_assoc($result_booking)): ?>
                                            <tr>
                                                <td class="fw-bold text-dark">#PKP-<?= $row['id_pemesanan']; ?></td>
                                                <td><?= htmlspecialchars($row['nama_mobil']); ?> (<span class="text-muted"><?= $row['plat_nomor']; ?></span>)</td>
                                                <td class="fw-bold text-orange">Rp <?= number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <?php
                                                    if ($row['status_pemesanan'] == 'pending') echo '<span class="status-badge bg-warning text-dark">Pending</span>';
                                                    elseif ($row['status_pemesanan'] == 'dikonfirmasi') echo '<span class="status-badge bg-success text-white">Selesai</span>';
                                                    else echo '<span class="status-badge bg-danger text-white">Batal</span>';
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TABEL 2: STATUS JAMINAN FISIK -->
                    <div class="card card-custom p-4">
                        <h6 class="mb-3 fw-bold text-secondary">Status Dokumen Jaminan Fisik</h6>
                        <div class="table-responsive">
                            <table class="table align-middle text-center small">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th>Nota Sewa</th>
                                        <th>Unit Pickup</th>
                                        <th>Bentuk Jaminan</th>
                                        <th>Nomor Identitas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result_jaminan) == 0): ?>
                                        <tr>
                                            <td colspan="4" class="text-muted py-3">Belum ada jaminan fisik yang diserahkan.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($jam = mysqli_fetch_assoc($result_jaminan)): ?>
                                            <tr>
                                                <td class="fw-bold">#PKP-<?= $jam['id_pemesanan']; ?></td>
                                                <td><?= htmlspecialchars($jam['nama_mobil']); ?></td>
                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($jam['nama_jaminan']); ?></span></td>
                                                <td class="text-muted fw-medium"><?= htmlspecialchars($jam['nomor_dokumen_jaminan']); ?></td>
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

        <!-- FOOTER BAR -->
        <footer class="bg-white text-center py-3 text-muted small border-top mt-auto">
            Copyright © Rental Pickup JKT 2026
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>