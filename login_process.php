<?php
session_start(); // Mulai sesi, penting untuk menyimpan data user yang login

include 'koneksi.php'; // Hubungkan ke database

// Ambil input dari form login
$username = $_POST['username'];
$password = $_POST['password'];

// Cari user berdasarkan username
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($query);

// Jika user ditemukan
if ($user) {
    // Verifikasi password yang diinput dengan yang ada di database
    if (password_verify($password, $user['password'])) {
        // Simpan informasi penting ke dalam session
        $_SESSION['id_user']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        // Arahkan ke dashboard sesuai peran
        if ($user['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit; // Hentikan script setelah redirect
    } else {
        // Password salah
        header("Location: login.php?pesan=gagal"); // Kirim pesan gagal login
        exit;
    }
} else {
    // Username tidak ditemukan
    header("Location: login.php?pesan=gagal");
    exit;
}
?>
