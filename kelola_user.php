<?php
session_start();

// Cek apakah user yang login adalah admin
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil parameter urutan dari URL (jika tidak ada, default ASC)
$order = isset($_GET['urut']) && $_GET['urut'] === 'desc' ? 'DESC' : 'ASC';

// Query untuk ambil data user dari database, diurutkan berdasarkan ID
$query = "SELECT * FROM users ORDER BY id $order";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manajemen Pengguna</title>
    <style>
        /* Styling dasar */
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: #f9f9f9;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
        }

        .icon-title {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            background-color: #3498db;
            color: white;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
        }

        .back-btn:hover {
            background-color: #2980b9;
        }

        .urutkan {
            margin-bottom: 20px;
        }

        select {
            padding: 6px 10px;
            font-size: 14px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #2c3e50;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f7f7f7;
        }

        .badge {
            padding: 5px 10px;
            border-radius: 6px;
            color: white;
            font-size: 13px;
        }

        .admin {
            background-color: #27ae60;
        }

        .user {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>

<!-- Tombol kembali ke dashboard admin -->
<a href="admin_dashboard.php" class="back-btn">← Kembali ke Dashboard</a>

<!-- Judul halaman -->
<div class="icon-title">👥 Daftar Pengguna</div>

<!-- Form untuk mengubah urutan ID -->
<div class="urutkan">
    <form method="get">
        <label>Urutkan berdasarkan ID:</label>
        <select name="urut" onchange="this.form.submit()">
            <option value="asc" <?= $order === 'ASC' ? 'selected' : '' ?>>Dari Terkecil</option>
            <option value="desc" <?= $order === 'DESC' ? 'selected' : '' ?>>Dari Terbesar</option>
        </select>
    </form>
</div>

<!-- Tabel daftar pengguna -->
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = mysqli_fetch_assoc($result)) : ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td>
                <!-- Badge warna tergantung role -->
                <span class="badge <?= $user['role'] === 'admin' ? 'admin' : 'user' ?>">
                    <?= ucfirst($user['role']) ?>
                </span>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
