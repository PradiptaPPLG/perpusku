<?php
// Mulai session agar kita tahu siapa user yang sedang login
session_start();
include 'koneksi.php';

// Cek apakah user sudah login, jika tidak alihkan ke login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$maks_pinjam = 3; // Maksimal buku yang bisa dipinjam oleh 1 user

// Cek jumlah buku yang sedang dipinjam user saat ini
$cek_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE id_user = '$id_user' AND status = 'Dipinjam'");
$data_transaksi = mysqli_fetch_assoc($cek_transaksi);

if ($data_transaksi['total'] >= $maks_pinjam) {
    // Jika sudah mencapai batas, tampilkan pesan
    echo "<script>alert('Maksimal peminjaman adalah $maks_pinjam buku. Kembalikan dulu buku sebelumnya.');window.location.href='user_dashboard.php';</script>";
    exit;
}

// Ambil data buku dari database untuk ditampilkan di pilihan
$query_books = mysqli_query($conn, "SELECT * FROM books");

// Saat form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tangkap data dari form
    $id_book = $_POST['id_book'];
    $tanggal_pinjam = !empty($_POST['tanggal_pinjam']) ? $_POST['tanggal_pinjam'] : date('Y-m-d'); // Default hari ini jika kosong
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $status = 'Dipinjam';

    // Validasi tanggal harus diisi
    if (!$tanggal_pinjam || !$tanggal_kembali) {
        echo "<script>alert('Tanggal pinjam dan tanggal kembali wajib diisi');</script>";
        exit;
    }

    // Hitung selisih hari peminjaman
    $lama_pinjam = (strtotime($tanggal_kembali) - strtotime($tanggal_pinjam)) / (60 * 60 * 24);

    if ($lama_pinjam < 1) {
        echo "<script>alert('Tanggal kembali harus lebih dari tanggal pinjam');</script>";
    } else {
        // Ambil harga sewa buku per hari dari database (supaya aman dari manipulasi user)
        $book_query = mysqli_query($conn, "SELECT harga_per_hari FROM books WHERE id = '$id_book'");
        $book_data = mysqli_fetch_assoc($book_query);
        $harga_per_hari = $book_data['harga_per_hari'];

        // Hitung total harga
        $total_harga = $lama_pinjam * $harga_per_hari;

        // Simpan transaksi ke database
        $insert = mysqli_query($conn, "INSERT INTO transactions 
            (id_user, id_book, tanggal_pinjam, tanggal_kembali, lama_pinjam, total_harga, status) 
            VALUES 
            ('$id_user', '$id_book', '$tanggal_pinjam', '$tanggal_kembali', '$lama_pinjam', '$total_harga', '$status')");

        if ($insert) {
            echo "<script>alert('Transaksi berhasil ditambahkan!');window.location.href='lihat_transaksi_saya.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan transaksi. Silakan coba lagi.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Transaksi</title>
    <style>
        /* --- Styling Form Peminjaman --- */
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
            color: #333;
        }

        .container {
            max-width: 600px;
            background: white;
            margin: auto;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #007bff;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: 500;
        }

        select, input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        input[type="submit"] {
            margin-top: 20px;
            width: 100%;
            padding: 14px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background: #218838;
        }

        .estimasi {
            margin-top: 15px;
            background: #e9f7ef;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 6px;
            color: #155724;
            font-weight: bold;
            text-align: center;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>

    <script>
        // JavaScript untuk hitung estimasi harga
        const hargaBuku = {};
<?php
// Kirim harga per hari tiap buku ke JavaScript
mysqli_data_seek($query_books, 0);
while ($b = mysqli_fetch_assoc($query_books)) {
    echo "hargaBuku[{$b['id']}] = {$b['harga_per_hari']};\n";
}
?>

function hitungTotal() {
    const idBuku = document.getElementById('id_book').value;
    const tglPinjam = new Date(document.getElementById('tanggal_pinjam').value);
    const tglKembali = new Date(document.getElementById('tanggal_kembali').value);

    if (idBuku && !isNaN(tglPinjam) && !isNaN(tglKembali) && tglKembali > tglPinjam) {
        const selisihHari = Math.ceil((tglKembali - tglPinjam) / (1000 * 60 * 60 * 24));
        const total = selisihHari * hargaBuku[idBuku];
        document.getElementById('estimasi').innerText = 
            "Estimasi Total Harga: Rp" + total.toLocaleString('id-ID');
    } else {
        document.getElementById('estimasi').innerText = "";
    }
}
    </script>
</head>
<body>
    <div class="container">
        <h2>Form Peminjaman Buku</h2>
        <form method="POST">
            <!-- Pilihan buku -->
            <label for="id_book">Pilih Buku:</label>
            <select name="id_book" id="id_book" onchange="hitungTotal()" required>
                <option value="">-- Pilih Buku --</option>
                <?php
                mysqli_data_seek($query_books, 0);
                while ($book = mysqli_fetch_assoc($query_books)) { ?>
                    <option value="<?= $book['id'] ?>">
                        <?= htmlspecialchars($book['judul_buku']) ?> - Rp<?= number_format($book['harga_per_hari'], 0, ',', '.') ?>/hari
                    </option>
                <?php } ?>
            </select>

            <!-- Tanggal pinjam -->
            <label for="tanggal_pinjam">Tanggal Pinjam:</label>
            <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" onchange="hitungTotal()" required>

            <!-- Tanggal kembali -->
            <label for="tanggal_kembali">Tanggal Kembali:</label>
            <input type="date" name="tanggal_kembali" id="tanggal_kembali" onchange="hitungTotal()" required>

            <!-- Estimasi harga -->
            <div class="estimasi" id="estimasi"></div>

            <!-- Tombol submit -->
            <input type="submit" value="Pinjam Buku">

            <!-- Link kembali -->
            <a href="user_dashboard.php" class="back-link">← Kembali ke Dashboard</a>
        </form>
    </div>
</body>
</html>
