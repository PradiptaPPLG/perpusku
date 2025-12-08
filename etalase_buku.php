<?php
session_start(); // Mulai sesi supaya bisa cek login dan lainnya
include 'koneksi.php'; // Hubungkan ke database

// Ambil kata kunci pencarian dari parameter GET, kalau nggak ada kosongin
$keyword = $_GET['search'] ?? '';

// Cek apakah ada keyword yang diketik user
if ($keyword) {
    // Amankan keyword dari karakter berbahaya (untuk cegah SQL Injection)
    $keyword = mysqli_real_escape_string($conn, $keyword);

    // Query cari buku berdasarkan judul, pengarang, atau penerbit
    $query = "SELECT * FROM books 
              WHERE judul_buku LIKE '%$keyword%' 
              OR pengarang LIKE '%$keyword%' 
              OR penerbit LIKE '%$keyword%'";
} else {
    // Kalau nggak ada keyword, tampilkan semua buku
    $query = "SELECT * FROM books";
}

// Jalankan query-nya
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Etalase Buku</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        /* Styling dasar untuk tampilan */
        * { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            margin-bottom: 15px;
        }
        a {
            display: inline-block;
            margin-bottom: 20px;
            color: #007BFF;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover { text-decoration: underline; }

        form {
            text-align: center;
            margin-bottom: 30px;
        }
        input[type="text"] {
            padding: 10px;
            width: 60%;
            max-width: 400px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }
        button {
            padding: 10px 16px;
            border: none;
            background-color: #007BFF;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            margin-left: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }

        .etalase {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        .card h3 {
            margin-top: 0;
            font-size: 1.1em;
            color: #333;
        }
        .card p {
            margin: 10px 0 0;
            font-size: 0.95em;
            color: #555;
        }
        .price {
            font-weight: bold;
            color: #000;
        }
    </style>
</head>
<body>

<h2>📚 Etalase Buku Perpustakaan</h2>
<!-- Tombol balik ke dashboard user -->
<a href="user_dashboard.php">← Kembali ke Dashboard</a>

<!-- Form pencarian -->
<form method="GET" action="">
    <input type="text" name="search" placeholder="Cari judul, pengarang, atau penerbit..." value="<?= htmlspecialchars($keyword) ?>">
    <button type="submit">Cari</button>
</form>

<!-- Daftar buku ditampilkan di sini -->
<div class="etalase">
    <?php if (mysqli_num_rows($result) > 0): ?>
        <!-- Looping setiap data buku -->
        <?php while ($buku = mysqli_fetch_assoc($result)) : ?>
            <div class="card">
                <h3><?= htmlspecialchars($buku['judul_buku'] ?? 'Tanpa Judul') ?></h3>
                <p><span class="price">Rp<?= number_format($buku['harga_per_hari'] ?? 0, 0, ',', '.') ?></span> / hari</p>
                <p><small><strong>Pengarang:</strong> <?= htmlspecialchars($buku['pengarang']) ?></small></p>
                <p><small><strong>Penerbit:</strong> <?= htmlspecialchars($buku['penerbit']) ?></small></p>
                <p><small><strong>Tahun:</strong> <?= htmlspecialchars($buku['tahun_terbit']) ?></small></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <!-- Kalau gak ada data yang ditemukan -->
        <p style="text-align:center; color: #888;">Tidak ditemukan buku dengan kata kunci "<strong><?= htmlspecialchars($keyword) ?></strong>".</p>
    <?php endif; ?>
</div>

</body>
</html>
