<?php
session_start(); // Mulai session untuk akses data user yang login
include 'koneksi.php'; // Sambungkan ke database

// Cek apakah user adalah admin. Kalau bukan, tendang ke halaman login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pastikan URL mengirim parameter id (id transaksi)
if (!isset($_GET['id'])) {
    echo "ID transaksi tidak ditemukan.";
    exit();
}

// Ambil ID dari URL, pastikan dalam bentuk integer
$id = intval($_GET['id']);

// Ambil data transaksi beserta judul bukunya
$stmt = $conn->prepare("SELECT transactions.*, books.judul_buku 
                        FROM transactions 
                        JOIN books ON transactions.id_book = books.id 
                        WHERE transactions.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

// Kalau data transaksi nggak ketemu, tampilkan pesan error
if (!$data) {
    echo "Transaksi tidak ditemukan.";
    exit();
}

// Kalau form disubmit (metode POST), proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_baru = $_POST['status']; // Ambil status baru dari form
    $tanggal_kembali = null;

    // Kalau status diubah jadi "Dikembalikan", otomatis isi tanggal_kembali hari ini
    if ($status_baru === 'Dikembalikan') {
        $tanggal_kembali = date('Y-m-d');
    }

    // Update data transaksi di database
    $update = $conn->prepare("UPDATE transactions SET status = ?, tanggal_kembali = ? WHERE id = ?");
    $update->bind_param("ssi", $status_baru, $tanggal_kembali, $id);

    // Kalau update berhasil, simpan notifikasi dan arahkan ke halaman transaksi
    if ($update->execute()) {
        $_SESSION['notif'] = "Transaksi berhasil diperbarui.";
        header("Location: transactions.php");
        exit();
    } else {
        echo "Gagal memperbarui transaksi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f3;
            padding: 30px;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            margin-top: 15px;
            color: #555;
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Edit Status Transaksi</h2>
    <form method="POST">
        <!-- Tampilkan judul buku -->
        <label>Judul Buku:</label>
        <strong><?= htmlspecialchars($data['judul_buku']); ?></strong>

        <!-- Tampilkan tanggal pinjam -->
        <label>Tanggal Pinjam:</label>
        <strong><?= htmlspecialchars($data['tanggal_pinjam']); ?></strong>

        <!-- Dropdown untuk pilih status baru -->
        <label>Status:</label>
        <select name="status" required>
            <option value="Dipinjam" <?= $data['status'] == 'Dipinjam' ? 'selected' : ''; ?>>Dipinjam</option>
            <option value="Dikembalikan" <?= $data['status'] == 'Dikembalikan' ? 'selected' : ''; ?>>Dikembalikan</option>
        </select>

        <input type="submit" value="Simpan Perubahan">
    </form>

    <!-- Link untuk balik ke halaman daftar transaksi -->
    <a href="transactions.php">← Kembali ke Daftar Transaksi</a>
</div>
</body>
</html>
