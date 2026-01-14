<?php
session_start();
include 'koneksi.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$maks_pinjam = 3;

// Cek jumlah transaksi aktif user
$cek_transaksi = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total 
     FROM transactions 
     WHERE id_user = '$id_user' 
     AND status = 'dipinjam'"
);

$data_transaksi = mysqli_fetch_assoc($cek_transaksi);

if ($data_transaksi['total'] >= $maks_pinjam) {
    echo "<script>
        alert('Maksimal peminjaman adalah $maks_pinjam buku. Kembalikan dulu buku sebelumnya.');
        window.location.href='user_dashboard.php';
    </script>";
    exit;
}

// Ambil data buku
$query_books = mysqli_query($conn, "SELECT * FROM books");

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_book = $_POST['id_book']; // dari <select>
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $status = 'dipinjam';

    // Validasi tanggal
    $lama_pinjam = (strtotime($tanggal_kembali) - strtotime($tanggal_pinjam)) / (60 * 60 * 24);

    if ($lama_pinjam < 1) {
        echo "<script>alert('Tanggal kembali harus lebih dari tanggal pinjam');</script>";
    } else {

        // INSERT SESUAI STRUKTUR DATABASE
        $insert = mysqli_query(
            $conn,
            "INSERT INTO transactions 
            (id_user, id_buku, tanggal_pinjam, tanggal_kembali, status) 
            VALUES 
            ('$id_user', '$id_book', '$tanggal_pinjam', '$tanggal_kembali', '$status')"
        );

        if ($insert) {
            echo "<script>
                alert('Transaksi berhasil ditambahkan!');
                window.location.href='lihat_transaksi_saya.php';
            </script>";
        } else {
            echo "<script>alert('Gagal menambahkan transaksi');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Transaksi</title>
    <link rel="stylesheet" href="tambah_transaksi_user.css">

    <script>
        const hargaBuku = {};
<?php
mysqli_data_seek($query_books, 0);
while ($b = mysqli_fetch_assoc($query_books)) {
    echo "hargaBuku[{$b['id']}] = {$b['harga_per_hari']};\n";
}
?>

        function hitungTotal() {
            const idBuku = document.getElementById('id_book').value;
            const tglPinjam = new Date(document.getElementById('tanggal_pinjam').value);
            const tglKembali = new Date(document.getElementById('tanggal_kembali').value);

            if (idBuku && tglKembali > tglPinjam) {
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
                <label>Judul Buku</label>
                <select name="id_book" id="id_book" onchange="hitungTotal()" required>
                    <option value="">Pilih Buku</option>
                    <?php
                    mysqli_data_seek($query_books, 0);
                    while ($book = mysqli_fetch_assoc($query_books)) {
                        echo "<option value='{$book['id']}'>
                                {$book['judul_buku']} - Rp" .
                                number_format($book['harga_per_hari'], 0, ',', '.') .
                                "/hari
                              </option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label>Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" onchange="hitungTotal()" required>
            </div>

            <div class="form-group">
                <label>Tanggal Kembali</label>
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
