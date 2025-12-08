<?php
session_start();
include 'koneksi.php'; // Koneksi ke database, penting agar $conn tersedia

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Tangani permintaan POST saat user klik "kembalikan"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_transaksi'])) {
    $id_transaksi = intval($_POST['id_transaksi']); // Ambil dan amankan ID transaksi
    $user_id = $_SESSION['id_user']; // Ambil ID user dari session

    // Cek apakah transaksi ini milik user yang login dan masih 'Dipinjam'
    $cek = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND id_user = ? AND status = 'Dipinjam'");
    $cek->bind_param("ii", $id_transaksi, $user_id);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows === 1) {
        // Jika ditemukan, update status jadi 'Dikembalikan'
        $update = $conn->prepare("UPDATE transactions SET status = 'Dikembalikan' WHERE id = ?");
        $update->bind_param("i", $id_transaksi);

        if ($update->execute()) {
            $_SESSION['notif'] = "Buku berhasil dikembalikan.";
        } else {
            $_SESSION['notif'] = "Terjadi kesalahan saat mengembalikan buku.";
        }

        $update->close(); // Tutup statement update
    } else {
        // Jika transaksi tidak ditemukan atau sudah dikembalikan sebelumnya
        $_SESSION['notif'] = "Transaksi tidak ditemukan atau sudah dikembalikan.";
    }

    $cek->close(); // Tutup statement cek
} else {
    // Jika metode bukan POST atau ID tidak diset
    $_SESSION['notif'] = "Permintaan tidak valid.";
}

// Kembali ke halaman transaksi saya
header("Location: lihat_transaksi_saya.php");
exit();
