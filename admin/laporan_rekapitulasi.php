<?php
require_once '../koneksi.php';
/** @var mysqli $koneksi */

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// 1. Hitung total pendapatan dari pemesanan yang berstatus 'selesai'
$pop_pendapatan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_bayar) as total FROM pemesanan WHERE MONTH(tanggal_mulai) = '$bulan' AND YEAR(tanggal_mulai) = '$tahun' AND status_pemesanan = 'selesai'"));
$total_in = $pop_pendapatan['total'] ?? 0;

// 2. Tentukan pengeluaran statis/operasional (bisa dihubungkan ke tabel biaya jika sudah ada)
$total_out = 450000; // Contoh nominal dummy pengeluaran medis/operasional unit

$keuntungan_bersih = $total_in - $total_out;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Jurnal Kas - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card border-0 shadow p-4 bg-white rounded-3 mx-auto" style="max-width: 600px;">
            <h5 class="fw-bold text-center text-uppercase mb-4">Laporan Rekapitulasi Jurnal Kas</h5>
            <hr>
            <div class="d-flex justify-content-between mb-2">
                <span>Total Pendapatan Sewa (Selesai):</span>
                <span class="text-success fw-bold">+ Rp <?= number_format($total_in, 0, ',', '.') ?></span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span>Total Pengeluaran Operasional:</span>
                <span class="text-danger fw-bold">- Rp <?= number_format($total_out, 0, ',', '.') ?></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                <span class="fw-bold">Laba Bersih (Net Profit):</span>
                <h4 class="fw-bold m-0 <?= ($keuntungan_bersih >= 0) ? 'text-primary' : 'text-danger' ?>">
                    Rp <?= number_format($keuntungan_bersih, 0, ',', '.') ?>
                </h4>
            </div>
            <div class="text-center mt-4">
                <button onclick="window.print()" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Cetak Dokumen Rekap</button>
            </div>
        </div>
    </div>
</body>
</html>