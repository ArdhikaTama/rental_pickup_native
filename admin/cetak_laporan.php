<?php
require_once '../koneksi.php';

/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Akses ditolak.");
}

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    "01" => "Januari", "02" => "Februari", "03" => "Maret", "04" => "April",
    "05" => "Mei", "06" => "Juni", "07" => "Juli", "08" => "Agustus",
    "09" => "September", "10" => "Oktober", "11" => "November", "12" => "Desember"
];

$query = "SELECT p.*, u.nama_lengkap, m.nama_mobil, m.plat_nomor 
          FROM pemesanan p
          JOIN users u ON p.id_user = u.id_user
          JOIN mobil m ON p.id_mobil = m.id_mobil
          WHERE MONTH(p.tanggal_mulai) = '$bulan' AND YEAR(p.tanggal_mulai) = '$tahun' AND p.status_pemesanan = 'selesai'";
$result = mysqli_query($koneksi, $query);
$total_omset = 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak_Laporan_<?= $bulan ?>_<?= $tahun ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: white; font-family: 'Segoe UI', Arial, sans-serif; padding: 30px; }
        .print-header { border-bottom: 3px double #333; padding-bottom: 10px; margin-bottom: 30px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="text-end no-print mb-4">
        <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Cetak Sekarang</button>
    </div>

    <div class="print-header text-center">
        <h2 class="fw-bold text-uppercase m-0">PREMIUM PICKUP RENTAL</h2>
        <p class="text-muted small m-0">Jl. Mangga dua Rt.1/Rw.1, kec. Grogol selatan, Jakarta Selatan | Telp: 0812-3456-7890</p>
        <h5 class="fw-bold mt-4 text-decoration-underline text-uppercase">LAPORAN REKAPITULASI OMSET BULANAN</h5>
        <p class="small">Periode: <strong><?= $nama_bulan[$bulan] ?> <?= $tahun ?></strong></p>
    </div>

    <table class="table table-bordered align-middle text-center small">
        <thead class="table-light">
            <tr>
                <th>No. Nota</th>
                <th>Nama Pelanggan</th>
                <th>Armada Mobil</th>
                <th>Plat Nomor</th>
                <th>Tanggal Selesai</th>
                <th>Subtotal Pendapatan</th>
            </tr>
        </thead>
        <tbody>
            <?php if(mysqli_num_rows($result) == 0): ?>
                <tr><td colspan="6" class="py-3 text-muted">Tidak ada data transaksi.</td></tr>
            <?php else: ?>
                <?php while($row = mysqli_fetch_assoc($result)): $total_omset += $row['total_bayar']; ?>
                <tr>
                    <td>#PKP-<?= $row['id_pemesanan'] ?></td>
                    <td><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                    <td><?= htmlspecialchars($row['nama_mobil']) ?></td>
                    <td><?= $row['plat_nomor'] ?></td>
                    <td><?= date('d/m/Y', strtotime($row['tanggal_selesai'])) ?></td>
                    <td class="text-end fw-bold">Rp <?= number_format($row['total_bayar'], 0, ',', '.') ?></td>
                </tr>
                <?php endwhile; ?>
                <tr class="fw-bold table-light text-end">
                    <td colspan="5">TOTAL PENDAPATAN BERSIH :</td>
                    <td>Rp <?= number_format($total_omset, 0, ',', '.') ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row mt-5 pt-4">
        <div class="col-8"></div>
        <div class="col-4 text-center small">
            <p class="mb-5">Jakarta, <?= date('d M Y') ?><br>Mengetahui, <br><strong>Head Administrator</strong></p>
            <p class="text-decoration-underline fw-bold">( ........................................ )</p>
        </div>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            // Otomatis memicu jendela cetak ketika halaman selesai dimuat sepenuhnya
            setTimeout(() => { window.print(); }, 500);
        });
    </script>
</body>
</html>