<?php
session_start();
include 'koneksi.php'; // Menghubungkan ke database MySQL

// Cek apakah user sudah login. Kalau belum, langsung tendang ke halaman login!
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id_user']; // Ambil ID user dari sesi login

// Ambil daftar transaksi milik user yang sedang login
$query = "
    SELECT t.id, b.judul_buku, b.harga_per_hari, t.tanggal_pinjam, t.tanggal_kembali, t.status
    FROM transactions t
    JOIN books b ON t.id_book = b.id
    WHERE t.id_user = ?
    ORDER BY t.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id); // Masukkan id_user ke query
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Transaksi Saya</title>
    <style>
        body { font-family: Arial; margin: 30px; }
        h2 { color: #333; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ccc;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .btn {
            margin-top: 20px;
            padding: 10px 18px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }

        .btn:hover { background-color: #0056b3; }

        .status-dipinjam {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .status-kembali {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .hapus-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .hapus-button:hover { background-color: #c0392b; }

        .no-transaksi {
            margin-top: 20px;
            font-style: italic;
            color: #888;
        }
    </style>
</head>
<body>

<!-- Kalau ada notifikasi dari session, tampilkan dulu -->
<?php if (isset($_SESSION['notif'])): ?>
    <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <?= $_SESSION['notif'] ?>
    </div>
    <?php unset($_SESSION['notif']); // Notif hanya muncul sekali ?>
<?php endif; ?>

<h2>Riwayat Transaksi Saya</h2>

<!-- Tombol untuk mencetak PDF -->
<a href="cetak_transaksi_pdf.php" target="_blank" class="btn">🖨️ Cetak PDF</a>

<?php if ($result->num_rows === 0): ?>
    <!-- Kalau belum pernah minjam buku sama sekali -->
    <p class="no-transaksi">Belum ada transaksi peminjaman buku.</p>
<?php else: ?>
    <!-- Kalau sudah pernah transaksi, tampilkan tabel -->
    <table>
        <tr>
            <th>ID</th>
            <th>Judul Buku</th>
            <th>Tanggal Pinjam</th>
            <th>Tanggal Kembali</th>
            <th>Total Harga</th>
            <th>Status</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
            <?php
                // Hitung jumlah hari peminjaman
                $tanggal_pinjam = new DateTime($row['tanggal_pinjam']);
                $tanggal_kembali = new DateTime($row['tanggal_kembali']);
                $selisih = $tanggal_pinjam->diff($tanggal_kembali)->days;

                // Pastikan selisih tidak negatif (hanya jaga-jaga)
                if ($selisih < 0) $selisih = 0;

                // Kalikan dengan harga per hari
                $total_harga = $selisih * $row['harga_per_hari'];
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['judul_buku']) ?></td>
                <td><?= $row['tanggal_pinjam'] ?></td>
                <td><?= $row['tanggal_kembali'] ?></td>
                <td>Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                <td>
                    <?php if ($row['status'] == 'Dipinjam'): ?>
                        <!-- Kalau masih dipinjam, beri tombol untuk kembalikan -->
                        <span class="status-dipinjam"><?= $row['status'] ?></span><br><br>
                        <form method="POST" action="kembalikan_buku.php" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?');">
                            <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                            <button type="submit" style="padding: 5px 10px; border: none; background-color: orange; color: white; border-radius: 4px;">Kembalikan</button>
                        </form>
                    <?php else: ?>
                        <!-- Kalau sudah dikembalikan, boleh edit atau hapus -->
                        <span class="status-kembali"><?= $row['status'] ?></span><br><br>

                        <a href="edit_tanggal_kembali.php?id=<?= $row['id'] ?>" 
                           style="padding: 5px 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; display: inline-block; margin-bottom: 5px;">
                           Edit Tanggal Kembali
                        </a><br>

                        <form method="POST" action="hapus_transaksi_user.php" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?');">
                            <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                            <button type="submit" class="hapus-button">Hapus</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php endif; ?>

<!-- Tombol balik ke dashboard user -->
<a href="user_dashboard.php" class="btn">← Kembali ke Dashboard</a>

</body>
</html>
