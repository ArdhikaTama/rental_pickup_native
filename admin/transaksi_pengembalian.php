<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

// Proteksi Halaman Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$pesan = "";

// 1. PROSES INPUT PENGEMBALIAN & DENDA
if (isset($_POST['proses_kembali'])) {
    $id_pemesanan         = $_POST['id_pemesanan'];
    $tanggal_dikembalikan = $_POST['tanggal_dikembalikan'];
    $kondisi_mobil_kembali= mysqli_real_escape_string($koneksi, $_POST['kondisi_mobil_kembali']);
    $status_pengembalian  = $_POST['status_pengembalian'];
    
    // Data Denda (Optional)
    $jumlah_denda     = !empty($_POST['jumlah_denda']) ? $_POST['jumlah_denda'] : 0;
    $keterangan_denda = mysqli_real_escape_string($koneksi, $_POST['keterangan_denda']);

    // Mulai Transaction Database secara prosedural agar aman
    mysqli_query($koneksi, "START TRANSACTION");

    // Ambil id_mobil terkait untuk mengembalikan statusnya nanti
    $data_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id_mobil FROM pemesanan WHERE id_pemesanan = '$id_pemesanan'"));
    $id_mobil = $data_p['id_mobil'];

    // Insert ke tabel pengembalian
    $query_kembali = "INSERT INTO pengembalian (id_pemesanan, tanggal_dikembalikan, kondisi_mobil_kembali, status_pengembalian) 
                      VALUES ('$id_pemesanan', '$tanggal_dikembalikan', '$kondisi_mobil_kembali', '$status_pengembalian')";
    
    if (mysqli_query($koneksi, $query_kembali)) {
        $id_pengembalian = mysqli_insert_id($koneksi);

        // Jika ada denda, insert ke tabel denda
        $sukses_denda = true;
        if ($jumlah_denda > 0) {
            $query_denda = "INSERT INTO denda (id_pengembalian, keterangan_denda, jumlah_denda, status_denda) 
                            VALUES ('$id_pengembalian', '$keterangan_denda', '$jumlah_denda', 'belum_bayar')";
            $sukses_denda = mysqli_query($koneksi, $query_denda);
        }

        // Update status pemesanan menjadi 'selesai'
        $update_pesan = mysqli_query($koneksi, "UPDATE pemesanan SET status_pemesanan = 'selesai' WHERE id_pemesanan = '$id_pemesanan'");
        
        // Kembalikan status ketersediaan mobil menjadi 'tersedia'
        $update_mobil = mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'tersedia' WHERE id_mobil = '$id_mobil'");

        if ($sukses_denda && $update_pesan && $update_mobil) {
            mysqli_query($koneksi, "COMMIT");
            $pesan = "<div class='alert alert-success small py-2'>Mobil Berhasil Dikembalikan! Status armada telah diperbarui menjadi Tersedia.</div>";
        } else {
            mysqli_query($koneksi, "ROLLBACK");
            $pesan = "<div class='alert alert-danger small py-2'>Gagal memproses pengembalian data.</div>";
        }
    } else {
        mysqli_query($koneksi, "ROLLBACK");
        $pesan = "<div class='alert alert-danger small py-2'>Error: " . mysqli_error($koneksi) . "</div>";
    }
}

// 2. AMBIL DATA MOBIL YANG SEDANG DISEWA (STATUS = 'berjalan')
$query_aktif = mysqli_query($koneksi, "SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor 
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id_user 
    JOIN mobil m ON p.id_mobil = m.id_mobil 
    WHERE p.status_pemesanan = 'berjalan' OR p.status_pemesanan = 'dikonfirmasi'
    ORDER BY p.id_pemesanan ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengembalian & Denda - Admin</title>
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
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
        .text-orange { color: #fd7e14 !important; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
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
            <div class="menu-section">Alur Transaksi (5)</div>
            <a href="#" class="nav-link"><i class="bi bi-receipt"></i> Pemesanan Baru</a>
            <a href="#" class="nav-link"><i class="bi bi-wallet2"></i> Pembayaran</a>
            <a href="transaksi_pengembalian.php" class="nav-link active"><i class="bi bi-arrow-counterclockwise"></i> Pengembalian & Denda</a>
        </div>
        <div class="px-2">
            <hr class="text-white opacity-25">
            <a href="../logout.php" class="nav-link text-danger fw-bold rounded bg-light bg-opacity-10"><i class="bi bi-box-arrow-right text-danger"></i> Sign Out</a>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="topbar">
            <span class="text-muted small fw-medium">Manajemen Pengembalian Unit Armada</span>
            <span class="fw-semibold text-dark small"><i class="bi bi-person-gear text-orange"></i> <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span>
        </div>

        <div class="container-fluid p-4 flex-grow-1">
            <h4 class="mb-4 text-dark fw-normal">Pengembalian Mobil & Input Denda</h4>
            <?= $pesan; ?>

            <div class="row g-4">
                <!-- FORM INPUT PEMPROSESAN -->
                <div class="col-xl-5">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Form Input Pengembalian</h6>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Pilih Nota Sewa Aktif</label>
                                <select name="id_pemesanan" class="form-select" required>
                                    <option value="">-- Pilih Transaksi Berjalan --</option>
                                    <?php while ($row = mysqli_fetch_assoc($query_aktif)): ?>
                                        <option value="<?= $row['id_pemesanan'] ?>">
                                            #PKP-<?= $row['id_pemesanan'] ?> | <?= htmlspecialchars($row['nama_lengkap']) ?> - <?= htmlspecialchars($row['nama_mobil']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Tanggal & Waktu Kembali Nyata</label>
                                <input type="datetime-local" name="tanggal_dikembalikan" class="form-control" required value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Kondisi Fisik Pickup Saat Kembali</label>
                                <textarea name="kondisi_mobil_kembali" class="form-control" rows="2" placeholder="Contoh: Mulus bersih / Lecet halus pintu kanan" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Ketepatan Waktu</label>
                                <select name="status_pengembalian" class="form-select" required>
                                    <option value="tepat_waktu">Tepat Waktu / Lepas Kunci Aman</option>
                                    <option value="terlambat">Terlambat Kembali</option>
                                </select>
                            </div>

                            <!-- INPUT DATA DENDA (OPSIONAL) -->
                            <div class="bg-light p-3 rounded-3 mb-4 border">
                                <h6 class="fw-bold text-dark mb-2 small"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Biaya Tambahan / Denda (Opsional)</h6>
                                <div class="mb-2">
                                    <label class="form-label small text-muted mb-1">Nominal Denda (Rp)</label>
                                    <input type="number" name="jumlah_denda" class="form-control form-control-sm" placeholder="0" min="0">
                                </div>
                                <div>
                                    <label class="form-label small text-muted mb-1">Alasan/Keterangan Denda</label>
                                    <input type="text" name="keterangan_denda" class="form-control form-control-sm" placeholder="Contoh: Telat 4 jam / Pintu penyok sedikit">
                                </div>
                            </div>

                            <button type="submit" name="proses_kembali" class="btn btn-orange w-100 fw-bold py-2 shadow-sm">PROSES PENGEMBALIAN</button>
                        </form>
                    </div>
                </div>

                <!-- DAFTAR HISTORI MOBIL YANG SUDAH KEMBALI -->
                <div class="col-xl-7">
                    <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
                        <h6 class="fw-bold mb-3 text-uppercase text-secondary">Log Pengembalian Terakhir</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle small text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nota</th>
                                        <th>Tanggal Kembali</th>
                                        <th>Kondisi Mobil</th>
                                        <th>Status</th>
                                        <th>Denda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $log_kembali = mysqli_query($koneksi, "SELECT kb.*, p.id_pemesanan, d.jumlah_denda 
                                        FROM pengembalian kb 
                                        JOIN pemesanan p ON kb.id_pemesanan = p.id_pemesanan
                                        LEFT JOIN denda d ON kb.id_pengembalian = d.id_pengembalian 
                                        ORDER BY kb.id_pengembalian DESC LIMIT 5");
                                    
                                    if(mysqli_num_rows($log_kembali) == 0):
                                    ?>
                                        <tr><td colspan="5" class="text-muted py-3">Belum ada riwayat pengembalian.</td></tr>
                                    <?php else: ?>
                                        <?php while ($log = mysqli_fetch_assoc($log_kembali)): ?>
                                        <tr>
                                            <td class="fw-bold">#PKP-<?= $log['id_pemesanan'] ?></td>
                                            <td><?= date('d/m/Y H:i', strtotime($log['tanggal_dikembalikan'])) ?></td>
                                            <td class="text-start"><?= htmlspecialchars($log['kondisi_mobil_kembali']) ?></td>
                                            <td>
                                                <span class="badge <?= ($log['status_pengembalian'] == 'tepat_waktu') ? 'bg-success' : 'bg-danger' ?>">
                                                    <?= $log['status_pengembalian'] ?>
                                                </span>
                                            </td>
                                            <td class="fw-bold text-orange">
                                                Rp <?= number_format($log['jumlah_denda'] ?? 0, 0, ',', '.') ?>
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