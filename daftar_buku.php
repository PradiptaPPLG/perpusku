<?php
session_start(); // Mulai session untuk akses data user login
include 'koneksi.php'; // Sambungkan ke database

// Cek apakah user adalah admin, kalau bukan tampilkan pesan akses ditolak
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Akses ditolak.");
}

// Ambil semua data buku dari tabel 'buku'
$buku = mysqli_query($conn, "SELECT * FROM buku");
?>

<h2>Daftar Buku</h2>
<a href="tambah_buku.php">+ Tambah Buku</a> <!-- Link ke halaman untuk nambah buku baru -->
<table border="1" cellpadding="8">
    <tr>
        <th>Judul</th>
        <th>Harga</th>
        <th>Aksi</th>
    </tr>
    <?php while ($b = mysqli_fetch_assoc($buku)) : ?>
    <tr>
        <!-- Tampilkan judul buku dan harga per hari (pakai format ribuan) -->
        <td><?= htmlspecialchars($b['judul']) ?></td>
        <td>Rp<?= number_format($b['harga_per_hari'], 0, ',', '.') ?></td>
        <td>
            <!-- Tombol hapus dengan konfirmasi sebelum dijalankan -->
            <a href="hapus_buku.php?id=<?= $b['id'] ?>" onclick="return confirm('Hapus buku ini?')">Hapus</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
