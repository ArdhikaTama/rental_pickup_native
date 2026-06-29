<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$pesan = "";

// Ambil data nama penyewa untuk topbar
$query_user = mysqli_query($koneksi, "SELECT nama_lengkap FROM users WHERE id_user = '$id_user'");
$user = mysqli_fetch_assoc($query_user);

// MENGAMBIL PILIHAN MOBIL YANG STATUSNYA 'TERSEDIA' DAN SUDAH ADA PAKET HARGANYA
$query_mobil = mysqli_query($koneksi, "SELECT m.*, p.harga, p.jenis_paket 
                                       FROM mobil m 
                                       JOIN paket_harga p ON m.id_mobil = p.id_mobil 
                                       WHERE m.status_ketersediaan = 'tersedia'");

// Proses ketika form sewa disubmit
if (isset($_POST['proses_pesan'])) {
    $id_mobil        = $_POST['id_mobil'];
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    
    // Mengambil paket harga berdasarkan mobil yang dipilih
    $query_paket = mysqli_query($koneksi, "SELECT id_paket, harga FROM paket_harga WHERE id_mobil = '$id_mobil' LIMIT 1");
    $data_paket  = mysqli_fetch_assoc($query_paket);

    if ($data_paket) {
        $id_paket = $data_paket['id_paket'];
        
        // Hitung selisih hari sewa
        $tgl1 = new DateTime($tanggal_mulai);
        $tgl2 = new DateTime($tanggal_selesai);
        $jarak = $tgl1->diff($tgl2);
        $durasi = $jarak->days == 0 ? 1 : $jarak->days;

        $total_bayar = $data_paket['harga'] * $durasi;
        $tgl_booking = date('Y-m-d');

        // Insert transaksi ke tabel pemesanan
        $insert = "INSERT INTO pemesanan (id_user, id_mobil, id_paket, tanggal_booking, tanggal_mulai, tanggal_selesai, total_bayar, status_pemesanan) 
                   VALUES ('$id_user', '$id_mobil', '$id_paket', '$tgl_booking', '$tanggal_mulai', '$tanggal_selesai', '$total_bayar', 'pending')";
        
        if (mysqli_query($koneksi, $insert)) {
            // Ubah status mobil menjadi 'disewa' agar tidak bentrok
            mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'disewa' WHERE id_mobil = '$id_mobil'");
            
            $pesan = "<div class='alert alert-success small py-2'>Pemesanan sukses dibuat! Mengalihkan ke Beranda...</div>";
            echo "<meta http-equiv='refresh' content='2;url=dashboard.php'>";
        } else {
            $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses pemesanan: " . mysqli_error($koneksi) . "</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger small py-2'>Error: Paket harga belum diatur admin.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Sewa Mobil - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* Sidebar Layout (Sesuai Dashboard Penyewa) */
        .sidebar {
            width: 260px; height: 100vh; position: fixed; top: 0; left: 0;
            background-color: #333333; color: white; padding-top: 15px; z-index: 1000;
        }
        .sidebar .brand { padding: 10px 20px; font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .menu-section { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #fd7e14; font-weight: bold; padding: 15px 20px 5px; }
        .sidebar .nav-link { color: rgba(255,255,255,0.85); padding: 10px 20px; font-size: 0.9rem; display: flex; align-items: center; text-decoration: none; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: #fd7e14; color: white; font-weight: 500; }
        .sidebar .nav-link i { margin-right: 10px; font-size: 1.1rem; }
        
        /* Main Content */
        .main-content { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* Topbar */
        .topbar {
            background: white; height: 60px; display: flex; align-items: center;
            justify-content: space-between; padding: 0 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        .text-orange { color: #fd7e14 !important; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column justify-content-between pb-3">
        <div>
            <div class="brand fw-bold mb-3 d-flex align-items-center">
                <i class="bi bi-truck-flatbed me-2 fs-4 text-orange"></i>
                <div>
                    <span class="d-block lh-1 small opacity-75 text-white">PREMIUM</span>
                    <span class="fs-6 text-uppercase text-orange">Pickup Rental</span>
                </div>
            </div>
            
            <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Beranda</a>
            
            <div class="menu-section">Transaksi Logistik</div>
            <a href="pesan_mobil.php" class="nav-link active"><i class="bi bi-file-earmark-plus"></i> Form Sewa Mobil</a>
            <a href="#" class="nav-link"><i class="bi bi-clock-history"></i> Riwayat Transaksi</a>
        </div>

        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10" onclick="return confirm('Apakah Anda yakin ingin keluar?')">
                <i class="bi bi-box-arrow-right text-danger"></i> Keluar
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <span class="text-muted fw-medium small">Formulir Pengajuan Sewa Armada</span>
            <span class="fw-semibold text-dark small"><i class="bi bi-person-circle text-orange"></i> <?= htmlspecialchars($user['nama_lengkap']); ?></span>
        </div>

        <div class="container-fluid p-4">
            <h4 class="mb-4 text-dark fw-normal">Sewa Mobil Baru</h4>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="bg-white p-4 rounded-3 shadow-sm card border-0">
                        <?= $pesan; ?>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Pilih Armada Mobil Pickup</label>
                                <select name="id_mobil" class="form-select" required>
                                    <option value="">-- Pilih Unit Tersedia --</option>
                                    <?php if(mysqli_num_rows($query_mobil) == 0): ?>
                                        <option value="" disabled class="text-danger">Tidak ada mobil dengan tarif aktif yang tersedia saat ini.</option>
                                    <?php else: ?>
                                        <?php while($m = mysqli_fetch_assoc($query_mobil)): ?>
                                            <option value="<?= $m['id_mobil']; ?>">
                                                <?= htmlspecialchars($m['nama_mobil']); ?> [<?= htmlspecialchars($m['plat_nomor']); ?>] - Rp <?= number_format($m['harga'], 0, ',', '.'); ?> / <?= $m['jenis_paket'] ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text small text-muted">Jika pilihan kosong, pastikan Admin telah mengonfigurasi **Tarif Sewa** untuk mobil terkait di panel admin.</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Tanggal Mulai Sewa</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" required min="<?= date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label small fw-bold">Tanggal Selesai Sewa</label>
                                    <input type="date" name="tanggal_selesai" class="form-control" required min="<?= date('Y-m-d'); ?>">
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="dashboard.php" class="btn btn-light border">Batal</a>
                                <button type="submit" name="proses_pesan" class="btn btn-orange px-4">Kirim Permohonan Sewa</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>