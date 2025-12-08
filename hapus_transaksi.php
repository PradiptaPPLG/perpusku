<?php
session_start(); // Mulai session buat akses data login admin
include 'koneksi.php'; // Hubungkan ke database

// Cek apakah yang akses adalah admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Akses ditolak."); // Kalau bukan admin, langsung stop
}

// Cek kalau form dikirim dan ada ID transaksi yang dikirim juga
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_transaksi'])) {
    $id = intval($_POST['id_transaksi']); // Ubah ID jadi integer (biar aman)

    // Cek dulu status transaksi berdasarkan ID-nya
    $cek = $conn->prepare("SELECT status FROM transactions WHERE id = ?");
    $cek->bind_param("i", $id);
    $cek->execute();
    $res = $cek->get_result();
    $data = $res->fetch_assoc();

    // Kalau transaksi ada dan statusnya udah "Dikembalikan", baru boleh dihapus
    if ($data && $data['status'] === 'Dikembalikan') {
        $hapus = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $hapus->bind_param("i", $id);
        $hapus->execute();

        $_SESSION['notif'] = "Transaksi berhasil dihapus.";
    } else {
        // Kalau belum dikembalikan, tampilkan pesan gagal
        $_SESSION['notif'] = "Transaksi tidak bisa dihapus. Pastikan status sudah Dikembalikan.";
    }
}

// Setelah proses, arahkan balik ke halaman transaksi admin
header("Location: transactions.php");
exit();
