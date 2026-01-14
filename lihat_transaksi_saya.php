<?php
session_start();
include 'koneksi.php'; // Menghubungkan ke database

// Pastikan user sudah login, jika tidak, lempar ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['id_user'];

// Ambil semua transaksi milik user yang sedang login
$query = "
    SELECT t.id, b.judul_buku, b.harga_per_hari, t.tanggal_pinjam, t.tanggal_kembali, t.status
    FROM transactions t
    JOIN books b ON t.id_buku = b.id
    WHERE t.id_user = ?
    ORDER BY t.id DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi Saya</title>
    <style>
        /* Background Perpustakaan dengan Blur */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            color: #333;
        }

        /* Overlay gelap transparan agar teks tetap terbaca */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Gelap transparan */
            backdrop-filter: blur(3px);
            z-index: -1;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 25px;
            background-color: rgba(255, 255, 255, 0.92);
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 25px;
            font-weight: 600;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 10px;
            text-align: center;
            border: 1px solid #ddd;
        }
        th {
            background-color: #2c3e50;
            color: white;
        }

        .btn {
            margin-top: 20px;
            padding: 10px 18px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
            transition: background 0.3s;
        }
        .btn:hover {
            background-color: #2980b9;
        }

        .status-dipinjam {
            background-color: #3498db;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
        }

        .status-kembali {
            background-color: #27ae60;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            display: inline-block;
        }

        .hapus-button,
        .kembalikan-button {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9em;
            margin-top: 8px;
        }

        .hapus-button {
            background-color: #e74c3c;
            color: white;
        }
        .hapus-button:hover {
            background-color: #c0392b;
        }

        .kembalikan-button {
            background-color: #f39c12;
            color: white;
        }
        .kembalikan-button:hover {
            background-color: #d35400;
        }

        .edit-link {
            display: inline-block;
            padding: 6px 12px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            margin-top: 8px;
        }
        .edit-link:hover {
            background-color: #2980b9;
        }

        .no-transaksi {
            text-align: center;
            font-style: italic;
            color: #7f8c8d;
            margin-top: 20px;
            font-size: 1.1em;
        }

        /* Notifikasi */
        .notification {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container">

    <?php if (isset($_SESSION['notif'])): ?>
        <div class="notification">
            <?= htmlspecialchars($_SESSION['notif']) ?>
        </div>
        <?php unset($_SESSION['notif']); ?>
    <?php endif; ?>

    <h2>Riwayat Transaksi Saya</h2>

    <!-- Tombol cetak ke PDF -->
    <div style="text-align: center; margin-bottom: 20px;">
        <a href="cetak_transaksi_pdf.php" target="_blank" class="btn">🖨️ Cetak PDF</a>
    </div>

    <?php if ($result->num_rows === 0): ?>
        <p class="no-transaksi">Belum ada transaksi peminjaman buku.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Total Harga</th>
                <th>Status & Aksi</th>
            </tr>

            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    $tanggal_pinjam = new DateTime($row['tanggal_pinjam']);
                    $tanggal_kembali = new DateTime($row['tanggal_kembali']);
                    $selisih = $tanggal_pinjam->diff($tanggal_kembali)->days;
                    if ($selisih < 0) $selisih = 0;
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
                            <span class="status-dipinjam"><?= $row['status'] ?></span><br><br>
                            <form method="POST" action="kembalikan_buku.php" onsubmit="return confirm('Yakin ingin mengembalikan buku ini?');">
                                <input type="hidden" name="id_transaksi" value="<?= $row['id'] ?>">
                                <button type="submit" class="kembalikan-button">Kembalikan</button>
                            </form>
                        <?php else: ?>
                            <span class="status-kembali"><?= $row['status'] ?></span><br><br>
                            <a href="edit_tanggal_kembali.php?id=<?= $row['id'] ?>" class="edit-link">Edit Tanggal Kembali</a><br>
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

    <div style="text-align: center; margin-top: 25px;">
        <a href="user_dashboard.php" class="btn">← Kembali ke Dashboard</a>
    </div>

</div>

</body>
</html>
