<?php
session_start(); // Mulai session untuk akses data login
include 'koneksi.php'; // Hubungkan ke database

// Cek dulu apakah yang akses adalah admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Akses ditolak."); // Kalau bukan admin, tolak akses
}

// Cek apakah ada ID buku yang dikirim lewat URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Pastikan ID yang dikirim berupa angka (untuk keamanan)
    
    // Jalankan query untuk hapus data buku berdasarkan ID
    mysqli_query($conn, "DELETE FROM buku WHERE id = $id");

    // Setelah data dihapus, arahkan balik ke halaman daftar buku
    header("Location: daftar_buku.php");
    exit;
}
?>
