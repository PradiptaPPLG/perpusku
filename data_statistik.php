<?php
require 'koneksi.php';

// Ambil 5 buku terpopuler berdasarkan jumlah transaksi
$top_buku = mysqli_query($conn, "
    SELECT books.judul_buku, COUNT(transactions.id) AS total 
    FROM transactions
    JOIN books ON transactions.id_book = books.id
    GROUP BY books.judul_buku 
    ORDER BY total DESC 
    LIMIT 5
");

// Ambil jumlah peminjam per bulan (format bulan dan tahun)
$peminjam_per_bulan = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_pinjam, '%M %Y') AS bulan, COUNT(*) AS total
    FROM transactions
    GROUP BY DATE_FORMAT(tanggal_pinjam, '%Y-%m')
    ORDER BY tanggal_pinjam ASC
");

// Buat array kosong untuk menampung hasil data
$data = [
    'top_buku' => [],
    'peminjam_bulanan' => [],
];

// Masukkan data top buku ke dalam array
while ($row = mysqli_fetch_assoc($top_buku)) {
    $data['top_buku'][] = $row;
}

// Masukkan data peminjam bulanan ke dalam array
while ($row = mysqli_fetch_assoc($peminjam_per_bulan)) {
    $data['peminjam_bulanan'][] = $row;
}

// Set header agar browser tahu bahwa ini adalah data JSON
header('Content-Type: application/json');
echo json_encode($data);
