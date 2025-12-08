<?php
// Mulai session untuk memeriksa autentikasi admin
session_start();
include 'koneksi.php';

// Cek apakah user sudah login dan berperan sebagai admin
if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil semua data transaksi, termasuk nama user dan judul buku
$result = mysqli_query($conn, "SELECT transactions.*, users.username, books.judul_buku 
                               FROM transactions 
                               JOIN users ON transactions.id_user = users.id 
                               JOIN books ON transactions.id_book = books.id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        a.button {
            display: inline-block;
            margin-bottom: 15px;
            background-color: #007bff;
            color: white;
            padding: 10px 16px;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.3s ease;
        }

        a.button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .aksi a, .aksi form button {
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            margin: 0 2px;
        }

        .edit {
            background-color: #28a745;
        }

        .edit:hover {
            background-color: #218838;
        }

        .hapus {
            background-color: #dc3545;
        }

        .hapus:hover {
            background-color: #c82333;
        }

        .notif {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Transaksi</h2>
        <!-- Tombol kembali ke dashboard admin -->
        <a href="admin_dashboard.php" class="button">← Kembali ke Dashboard</a>

        <!-- Menampilkan notifikasi jika tersedia -->
        <?php if (isset($_SESSION['notif'])): ?>
            <div class="notif"><?= $_SESSION['notif']; unset($_SESSION['notif']); ?></div>
        <?php endif; ?>

        <!-- Tabel daftar transaksi -->
        <table>
            <tr>
                <th>ID</th>
                <th>Judul Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Tanggal Kembali</th>
                <th>Status</th>
                <th>Username Peminjam</th>
                <th>Aksi</th>
            </tr>

            <!-- Menampilkan data transaksi dari database -->
            <?php while ($data = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?= $data['id']; ?></td>
                <td><?= htmlspecialchars($data['judul_buku']); ?></td>
                <td><?= $data['tanggal_pinjam']; ?></td>
                <td><?= $data['tanggal_kembali'] ?? '-'; ?></td>
                <td><?= ucfirst($data['status']); ?></td>
                <td><?= htmlspecialchars($data['username']); ?></td>
                <td class="aksi">
                    <!-- Tombol edit -->
                    <a href="edit_transaksi.php?id=<?= $data['id']; ?>" class="edit">Edit</a>

                    <!-- Tombol hapus hanya muncul jika status 'Dikembalikan' -->
                    <?php if (strtolower($data['status']) === 'dikembalikan'): ?>
                        <form action="hapus_transaksi.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')" style="display:inline;">
                            <input type="hidden" name="id_transaksi" value="<?= $data['id']; ?>">
                            <button type="submit" class="hapus">Hapus</button>
                        </form>
                    <?php else: ?>
                        <span style="color:#999;">(Belum dikembalikan)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>
</body>
</html>
