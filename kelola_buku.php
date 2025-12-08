<?php
// Aktifkan semua error untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Cek apakah user adalah admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// ============ TAMBAH BUKU ============
if (isset($_POST['tambah'])) {
    // Ambil dan sanitasi input
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $penerbit = trim($_POST['penerbit']);
    $tahun = intval($_POST['tahun']);
    $harga = intval($_POST['harga']);

    // Gunakan prepared statement untuk mencegah SQL Injection
    $stmt = $conn->prepare("INSERT INTO books (judul_buku, pengarang, penerbit, tahun_terbit, harga_per_hari) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $judul, $pengarang, $penerbit, $tahun, $harga);
    $stmt->execute();

    $_SESSION['notif'] = "Buku berhasil ditambahkan.";
    header("Location: kelola_buku.php");
    exit;
}

// ============ HAPUS BUKU ============
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['notif'] = "Buku berhasil dihapus.";
    header("Location: kelola_buku.php");
    exit;
}

// ============ AMBIL DATA UNTUK EDIT ============
$edit_buku = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $id_edit);
    $stmt->execute();
    $result_edit = $stmt->get_result();
    $edit_buku = $result_edit->fetch_assoc();
}

// ============ UPDATE BUKU ============
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $judul = trim($_POST['judul']);
    $pengarang = trim($_POST['pengarang']);
    $penerbit = trim($_POST['penerbit']);
    $tahun = intval($_POST['tahun']);
    $harga = intval($_POST['harga']);

    $stmt = $conn->prepare("UPDATE books SET judul_buku = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, harga_per_hari = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $judul, $pengarang, $penerbit, $tahun, $harga, $id);
    $stmt->execute();

    $_SESSION['notif'] = "Buku berhasil diperbarui.";
    header("Location: kelola_buku.php");
    exit;
}

// Ambil semua data buku untuk ditampilkan
$result = mysqli_query($conn, "SELECT * FROM books");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kelola Buku</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
        h2 { text-align: center; }
        form input, button { margin-bottom: 10px; padding: 8px; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #eee; }
        .btn-warning { background: orange; color: white; padding: 6px 12px; text-decoration: none; }
        .btn-danger { background: red; color: white; padding: 6px 12px; text-decoration: none; }
        .btn-secondary { background: #007bff; color: white; padding: 8px 16px; text-decoration: none; display: inline-block; margin-bottom: 20px; }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="btn-secondary">← Kembali ke Dashboard</a>
<h2>Kelola Buku</h2>

<!-- Form Tambah atau Edit Buku -->
<form method="post">
    <?php if ($edit_buku) : ?>
        <!-- Jika sedang edit, kirimkan ID buku -->
        <input type="hidden" name="id" value="<?= $edit_buku['id'] ?>">
    <?php endif; ?>
    
    <!-- Field untuk judul, pengarang, dst -->
    <input type="text" name="judul" placeholder="Judul Buku" required value="<?= htmlspecialchars($edit_buku['judul_buku'] ?? '') ?>">
    <input type="text" name="pengarang" placeholder="Pengarang" required value="<?= htmlspecialchars($edit_buku['pengarang'] ?? '') ?>">
    <input type="text" name="penerbit" placeholder="Penerbit" value="<?= htmlspecialchars($edit_buku['penerbit'] ?? '') ?>">
    <input type="number" name="tahun" placeholder="Tahun Terbit" value="<?= htmlspecialchars($edit_buku['tahun_terbit'] ?? '') ?>">
    <input type="number" name="harga" placeholder="Harga Sewa per Hari (Rp)" required value="<?= htmlspecialchars($edit_buku['harga_per_hari'] ?? '') ?>">

    <!-- Tombol Simpan -->
    <button type="submit" name="<?= $edit_buku ? 'update' : 'tambah' ?>">
        <?= $edit_buku ? 'Update Buku' : 'Tambah Buku' ?>
    </button>
</form>

<!-- Tabel Data Buku -->
<table>
    <tr>
        <th>Judul</th>
        <th>Pengarang</th>
        <th>Penerbit</th>
        <th>Tahun Terbit</th>
        <th>Harga per Hari</th>
        <th>Aksi</th>
    </tr>
    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= htmlspecialchars($row['judul_buku']) ?></td>
            <td><?= htmlspecialchars($row['pengarang']) ?></td>
            <td><?= htmlspecialchars($row['penerbit']) ?></td>
            <td><?= htmlspecialchars($row['tahun_terbit']) ?></td>
            <td>Rp <?= number_format($row['harga_per_hari'], 0, ',', '.') ?></td>
            <td>
                <a href="?edit=<?= $row['id'] ?>" class="btn-warning">Edit</a>
                <a href="?hapus=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')" class="btn-danger">Hapus</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
