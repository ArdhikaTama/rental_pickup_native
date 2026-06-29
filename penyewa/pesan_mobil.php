<?php
require_once '../koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penyewa') {
    header("Location: ../login.php");
    exit();
}

$id_user = $_SESSION['id_user'];
$pesan = "";

// Mengambil pilihan mobil master yang statusnya tersedia
$query_mobil = mysqli_query($koneksi, "SELECT * FROM mobil WHERE status_ketersediaan = 'tersedia'");

// Proses ketika form disubmit
if (isset($_POST['proses_pesan'])) {
    $id_mobil        = $_POST['id_mobil'];
    $tanggal_mulai   = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    
    // Mengambil id_paket dan harga kalkulasi awal berdasarkan mobil pilihan
    $query_paket = mysqli_query($koneksi, "SELECT id_paket, harga FROM paket_harga WHERE id_mobil = '$id_mobil' LIMIT 1");
    $data_paket  = mysqli_fetch_assoc($query_paket);

    if ($data_paket) {
        $id_paket = $data_paket['id_paket'];
        
        // Hitung selisih hari
        $tgl1 = new DateTime($tanggal_mulai);
        $tgl2 = new DateTime($tanggal_selesai);
        $jarak = $tgl1->diff($tgl2);
        $durasi = $jarak->days == 0 ? 1 : $jarak->days;

        $total_bayar = $data_paket['harga'] * $durasi;
        $tgl_booking = date('Y-m-d');

        // Insert ke tabel transaksi pemesanan
        $insert = "INSERT INTO pemesanan (id_user, id_mobil, id_paket, tanggal_booking, tanggal_mulai, tanggal_selesai, total_bayar, status_pemesanan) 
                   VALUES ('$id_user', '$id_mobil', '$id_paket', '$tgl_booking', '$tanggal_mulai', '$tanggal_selesai', '$total_bayar', 'pending')";
        
        if (mysqli_query($koneksi, $insert)) {
            // Update status mobil menjadi 'disewa' agar tidak diorder orang lain
            mysqli_query($koneksi, "UPDATE mobil SET status_ketersediaan = 'disewa' WHERE id_mobil = '$id_mobil'");
            
            $pesan = "<div class='alert alert-success'>Pemesanan sukses dibuat! Mengalihkan ke dashboard...</div>";
            echo "<meta http-allowed='refresh' content='2;url=dashboard.php'>";
        } else {
            $pesan = "<div class='alert alert-danger'>Gagal memproses pemesanan: " . mysqli_error($koneksi) . "</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'>Paket harga untuk mobil ini belum dikonfigurasi admin.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Mobil - Premium Pickup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #e9ecef; font-family: 'Segoe UI', sans-serif; }
        .btn-orange { background-color: #fd7e14; color: white; font-weight: 600; }
        .btn-orange:hover { background-color: #e8590c; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="bg-white p-4 rounded-3 shadow-sm">
                    <h4 class="fw-bold text-uppercase mb-2">Formulir Booking Pickup</h4>
                    <p class="text-muted small mb-4">Silakan tentukan unit armada logistik dan durasi waktu sewa Anda.</p>
                    
                    <?= $pesan; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Pilih Armada Mobil</label>
                            <select name="id_mobil" class="form-select" required>
                                <option value="">-- Pilih Unit Tersedia --</option>
                                <?php while($m = mysqli_fetch_assoc($query_mobil)): ?>
                                    <option value="<?= $m['id_mobil']; ?>"><?= htmlspecialchars($m['nama_mobil']); ?> (<?= htmlspecialchars($m['plat_nomor']); ?>)</option>
                                <?php endwhile; ?>
                            </select>
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
                            <a href="dashboard.php" class="btn btn-light border px-4">Batal</a>
                            <button type="submit" name="proses_pesan" class="btn btn-orange px-4">Kirim Booking</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>