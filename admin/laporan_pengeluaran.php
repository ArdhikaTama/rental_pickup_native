<?php
require_once '../koneksi.php';
/** @var mysqli $koneksi */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') { header("Location: ../login.php"); exit(); }

$bulan_pilihan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun_pilihan = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Simulasi mengambil data pengeluaran (misal dari catatan denda/perawatan mobil)
// Catatan: Anda bisa mengembangkan tabel master khusus pengeluaran jika diperlukan nanti.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengeluaran - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    </head>
<body>
    <div class="container-fluid p-4">
        <h4 class="mb-4">Laporan Pengeluaran Operasional</h4>
        <div class="card border-0 shadow-sm p-4 bg-white rounded-3">
            <table class="table table-striped table-hover small text-center">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Keterangan Pengeluaran</th>
                        <th>Tanggal</th>
                        <th>Total Biaya Out</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>Ganti Oli & Servis Rutin Mitsubishi L300 (B 1234 ABC)</td>
                        <td>05/06/2026</td>
                        <td class="fw-bold text-danger">Rp 450.000</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td colspan="3" class="text-end">Total Pengeluaran :</td>
                        <td class="text-danger">Rp 450.000</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>