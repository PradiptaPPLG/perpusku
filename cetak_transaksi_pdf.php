<?php
ob_start(); // Nyalakan output buffering supaya tidak ada output yang bocor sebelum proses Dompdf

require 'dompdf/autoload.inc.php'; // Panggil library Dompdf
require 'koneksi.php'; // Koneksi ke database

use Dompdf\Dompdf;
use Dompdf\Options;

// Query gabungan untuk ambil data transaksi sekaligus username dan judul buku dari relasinya
$query = "SELECT 
            transactions.*, 
            users.username, 
            books.judul_buku AS judul_buku 
          FROM transactions 
          JOIN users ON transactions.id_user = users.id 
          JOIN books ON transactions.id_book = books.id";

// Eksekusi query
$result = mysqli_query($conn, $query);
if (!$result) {
    // Kalau query gagal, tampilkan pesan error dari MySQL
    die("Gagal ambil data transaksi: " . mysqli_error($conn));
}

// Mulai bangun konten HTML-nya untuk nanti dijadikan PDF
$html = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
    }
    table {
        border-collapse: collapse;
        width: 100%;
    }
    thead tr {
        background-color: #f0f0f0;
    }
    th, td {
        border: 1px solid #444;
        padding: 8px 5px;
        text-align: center;
    }
    tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
</style>

<h2>Laporan Transaksi Peminjaman Buku</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Judul Buku</th>
            <th>Tgl Pinjam</th>
            <th>Tgl Kembali</th>
            <th>Lama Pinjam</th>
            <th>Total Harga</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

// Looping data dari hasil query untuk diisi ke tabel
while ($row = mysqli_fetch_assoc($result)) {
    $html .= '<tr>
        <td>' . $row['id'] . '</td>
        <td>' . htmlspecialchars($row['username']) . '</td>
        <td>' . htmlspecialchars($row['judul_buku']) . '</td>
        <td>' . $row['tanggal_pinjam'] . '</td>
        <td>' . $row['tanggal_kembali'] . '</td>
        <td>' . $row['lama_pinjam'] . ' hari</td>
        <td>Rp ' . number_format($row['total_harga'], 0, ',', '.') . '</td>
        <td>' . htmlspecialchars($row['status']) . '</td>
    </tr>';
}

// Tutup tag tabel
$html .= '</tbody></table>';

// Konfigurasi Dompdf agar bisa render HTML dengan resource eksternal (kalau ada gambar, dsb)
$options = new Options();
$options->set('isRemoteEnabled', true);

// Buat instance Dompdf dan isi dengan HTML yang sudah kita susun
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape'); // Ukuran kertas A4, orientasi landscape (horizontal)
$dompdf->render(); // Render HTML jadi PDF

// Tampilkan PDF di browser (bukan download)
$dompdf->stream("laporan_transaksi.pdf", ["Attachment" => false]);

ob_end_flush(); // Matikan output buffering dan kirim hasilnya  
exit;
?>