<?php
// Memulai sesi
session_start();

// Cek apakah user sudah login dan memiliki role 'user'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    // Jika tidak, alihkan ke halaman login
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Pengguna</title>
    <!-- Menghubungkan ke file CSS eksternal -->
    <link rel="stylesheet" href="user_dashboard.css" />
</head>
<body>

<!-- Header dengan salam selamat datang dan tombol logout -->
<header>
    Selamat Datang, <?= htmlspecialchars($_SESSION['username']) ?>!
    <a href="logout.php" class="logout">Logout</a>
</header>

<!-- Kontainer utama dashboard pengguna -->
<div class="container">
    
    <!-- Kartu pertama: Pinjam Buku -->
    <div class="card">
        <h3>Pinjam Buku</h3>
        <p>Lakukan transaksi peminjaman buku dengan mudah dan cepat.</p>
        <a href="tambah_transaksi_user.php" class="button icon">Pinjam Sekarang</a>
    </div>
    
    <!-- Kartu kedua: Lihat Transaksi -->
    <div class="card">
        <h3>Lihat Transaksi Saya</h3>
        <p>Lihat daftar buku yang sudah Anda pinjam beserta statusnya.</p>
        <a href="lihat_transaksi_saya.php" class="button">Lihat Transaksi</a>
    </div>

    <!-- Kartu ketiga: Etalase Buku -->
    <div class="card">
        <h3>Etalase Buku</h3>
        <p>Lihat semua buku yang tersedia untuk dipinjam beserta harganya.</p>
        <a href="etalase_buku.php" class="button">Lihat Etalase</a>
    </div>

</div>

</body>
</html>
