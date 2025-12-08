<?php
// Mulai sesi untuk autentikasi admin
session_start();

// Hubungkan ke database
include 'koneksi.php';

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Jika bukan admin, tampilkan pesan dan hentikan eksekusi
    die("Akses ditolak.");
}

// Proses form jika method adalah POST (form disubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form input
    $judul = $_POST['judul'];
    $harga = $_POST['harga'];

    // Siapkan query untuk menyisipkan data ke tabel buku (menggunakan prepared statement)
    $query = "INSERT INTO buku (judul, harga_per_hari) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $judul, $harga); // "s" untuk string, "i" untuk integer

    // Eksekusi query
    $stmt->execute();

    // Redirect kembali ke halaman yang sama dengan notifikasi sukses
    header("Location: tambah_buku.php?sukses=1");
    exit;
}
?>

<!-- Form Tambah Buku -->
<form method="POST">
    <label>Judul Buku: 
        <input type="text" name="judul" required>
    </label><br>

    <label>Harga per hari: 
        <input type="number" name="harga" required>
    </label><br>

    <button type="submit">Tambah Buku</button>
</form>

<!-- Tampilkan pesan sukses jika buku berhasil ditambahkan -->
<?php if (isset($_GET['sukses'])): ?>
    <p style="color: green;">Buku berhasil ditambahkan.</p>
<?php endif; ?>
