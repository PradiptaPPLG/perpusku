<?php
// Memulai session, diperlukan agar bisa mengakses dan menghancurkan session yang aktif
session_start();

// Menghapus semua data session (log out user)
session_destroy();

// Mengarahkan user kembali ke halaman login
header("Location: login.php");
exit;
?>
