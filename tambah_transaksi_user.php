<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$maks_pinjam = 3; // Maksimal jumlah buku yang dapat dipinjam

// Cek jumlah transaksi aktif user (yang belum dikembalikan)
$cek_transaksi = mysqli_query($conn, "SELECT COUNT(*) as total FROM transactions WHERE id_user = '$id_user' AND status = 'Dipinjam'");
$data_transaksi = mysqli_fetch_assoc($cek_transaksi);

if ($data_transaksi['total'] >= $maks_pinjam) {
    // Jika melebihi batas peminjaman, tampilkan pesan dan kembali ke dashboard
    echo "<script>alert('Maksimal peminjaman adalah $maks_pinjam buku. Kembalikan dulu buku sebelumnya.');window.location.href='user_dashboard.php';</script>";
    exit;
}

// Ambil data buku dari database
$query_books = mysqli_query($conn, "SELECT * FROM books");

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_book = $_POST['id_book'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $status = 'Dipinjam';

    // Hitung selisih hari peminjaman
    $lama_pinjam = (strtotime($tanggal_kembali) - strtotime($tanggal_pinjam)) / (60 * 60 * 24);

    if ($lama_pinjam < 1) {
        // Jika tanggal kembali tidak valid
        echo "<script>alert('Tanggal kembali harus lebih dari tanggal pinjam');</script>";
    } else {
        // Ambil harga per hari dari database (untuk mencegah manipulasi di sisi client)
        $book_query = mysqli_query($conn, "SELECT harga_per_hari FROM books WHERE id = '$id_book'");
        $book_data = mysqli_fetch_assoc($book_query);
        $harga_per_hari = $book_data['harga_per_hari'];

        // Hitung total harga
        $total_harga = $lama_pinjam * $harga_per_hari;

        // Masukkan data transaksi ke database
        $insert = mysqli_query($conn, "INSERT INTO transactions 
            (id_user, id_book, tanggal_pinjam, tanggal_kembali, lama_pinjam, total_harga, status) 
            VALUES 
            ('$id_user', '$id_book', '$tanggal_pinjam', '$tanggal_kembali', '$lama_pinjam', '$total_harga', '$status')");

        if ($insert) {
            echo "<script>alert('Transaksi berhasil ditambahkan!');window.location.href='lihat_transaksi_saya.php';</script>";
        } else {
            echo "<script>alert('Gagal menambahkan transaksi');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="tambah_transaksi_user.css">
    <title>Tambah Transaksi</title>
    <script>
      const hargaBuku = {};
<?php
// Membuat variabel hargaBuku di JavaScript agar bisa dihitung total harga secara langsung di browser
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
        <div class="form-wrapper">
            <form method="POST">
                <div class="form-header">
                    <h2>Form Peminjaman Buku</h2>
                </div>

                <div class="form-group">
                    <label for="id_book">Judul Buku:</label>
                    <select name="id_book" id="id_book" onchange="hitungTotal()" required>
                        <option value="">Pilih Buku Yang Ingin Dipinjam</option>
                        <?php
                        mysqli_data_seek($query_books, 0); // Reset pointer
                        while ($book = mysqli_fetch_assoc($query_books)) { ?>
                            <option value="<?= $book['id'] ?>">
                                <?= htmlspecialchars($book['judul_buku']) ?> - Rp<?= number_format($book['harga_per_hari'], 0, ',', '.') ?>/hari
                            </option>
                     <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal_pinjam">Tanggal Pinjam:</label>
                    <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" onchange="hitungTotal()" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_kembali">Tanggal Kembali:</label>
                    <input type="date" name="tanggal_kembali" id="tanggal_kembali" onchange="hitungTotal()" required>
                </div>

                <div class="estimasi" id="estimasi"></div>

                <input type="submit" value="Pinjam Buku">
                <a href="user_dashboard.php" class="back-btn">Kembali ke Dashboard</a>
            </form>
        </div>
    </div>
</body>
</html>