<?php
// Konfigurasi database
$host = "localhost";   // Nama host, biasanya 'localhost' jika di XAMPP
$user = "root";        // Username MySQL default di XAMPP adalah 'root'
$pass = "";            // Password default untuk root biasanya kosong
$db   = "perpusku";    // Nama database yang digunakan

// Membuat koneksi ke database
$conn = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi, jika gagal tampilkan pesan error
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
