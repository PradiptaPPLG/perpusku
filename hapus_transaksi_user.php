<?php
session_start(); // Mulai session buat akses user login
include 'koneksi.php'; // Hubungkan ke database

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak."); // Kalau belum login, hentikan akses
}

$id_user = $_SESSION['id_user']; // Ambil ID user dari session

// Cek apakah request-nya method POST dan ada id_transaksi yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_transaksi'])) {
    $id_transaksi = intval($_POST['id_transaksi']); // Pastikan ID-nya angka

    // Ambil status transaksi berdasarkan ID transaksi dan ID user
    $query = "SELECT status FROM transactions WHERE id = ? AND id_user = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $id_transaksi, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    // Kalau transaksi ditemukan dan statusnya "Dikembalikan", boleh dihapus
    if ($data && $data['status'] === 'Dikembalikan') {
        $hapus = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $hapus->bind_param("i", $id_transaksi);
        $hapus->execute();

        $_SESSION['notif'] = "Transaksi berhasil dihapus.";
    } else {
        // Kalau belum dikembalikan, kasih notif penolakan
        $_SESSION['notif'] = "Transaksi tidak bisa dihapus. Pastikan status sudah Dikembalikan.";
    }
}

// Setelah selesai, arahkan kembali ke halaman daftar transaksi user
header("Location: lihat_transaksi_saya.php");
exit();
