<?php
session_start(); // Mulai session buat akses data login user
include 'koneksi.php'; // Hubungkan ke database

// Cek apakah user udah login atau belum
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah parameter ID transaksi tersedia di URL
if (!isset($_GET['id'])) {
    echo "ID tidak valid.";
    exit();
}

// Ambil ID transaksi dari parameter URL dan pastikan dalam bentuk integer
$id_transaksi = intval($_GET['id']);

// Ambil data transaksi berdasarkan ID dan milik user yang sedang login
$query = "SELECT * FROM transactions WHERE id = ? AND id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $id_transaksi, $_SESSION['id_user']);
$stmt->execute();
$result = $stmt->get_result();

// Kalau data transaksi nggak ditemukan atau bukan milik user ini, tampilkan pesan
if ($result->num_rows === 0) {
    echo "Transaksi tidak ditemukan.";
    exit();
}

// Ambil datanya dalam bentuk array asosiatif
$data = $result->fetch_assoc();

// Cek apakah form dikirim via metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil tanggal baru dari input user
    $tanggal_baru = $_POST['tanggal_kembali'];

    // Siapkan query untuk update tanggal kembali
    $update = $conn->prepare("UPDATE transactions SET tanggal_kembali = ? WHERE id = ? AND id_user = ?");
    $update->bind_param("sii", $tanggal_baru, $id_transaksi, $_SESSION['id_user']);
    $update->execute();

    // Simpan notifikasi ke session dan arahkan kembali ke halaman transaksi
    $_SESSION['notif'] = "Tanggal kembali berhasil diperbarui.";
    header("Location: lihat_transaksi_saya.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tanggal Kembali</title>
    <style>
        body { font-family: Arial; padding: 30px; }
        label, input, button { display: block; margin-bottom: 15px; }
        button {
            background-color: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <h2>Edit Tanggal Kembali</h2>
    <form method="POST">
        <!-- Input tanggal kembali, udah otomatis diisi dengan data lama -->
        <label>Tanggal Kembali Baru:</label>
        <input type="date" name="tanggal_kembali" value="<?= $data['tanggal_kembali'] ?>" required>
        <button type="submit">Simpan Perubahan</button>
    </form>
    <!-- Tombol kembali ke halaman daftar transaksi -->
    <a href="lihat_transaksi_saya.php" style="text-decoration:none;color:#007bff;">← Kembali</a>
</body>
</html>
