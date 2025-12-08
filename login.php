<?php
// Hubungkan ke database
include 'koneksi.php';

// Proses form saat dikirim POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = 'user';

    // Cek apakah username sudah dipakai
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan. Silakan pilih yang lain.";
    } else {
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$password', '$role')";
        if (mysqli_query($conn, $query)) {
            header("Location: login.php?pesan=register_berhasil");
            exit();
        } else {
            $error = "Registrasi gagal: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Perpusku</title>

    <style>
        /* === Dasar Tampilan Halaman === */
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.43)), 
                        url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1400&q=80') 
                        no-repeat center center fixed;
            background-size: cover;
        }

        /* === Lapisan Blur === */
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            backdrop-filter: blur(6px) brightness(0.7);
        }

        /* === Kontainer Form === */
        .form-container {
            position: relative;
            z-index: 1;
            max-width: 400px;
            margin: 100px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            color: #fff;
            text-align: center;
        }

        /* Input */
        input[type="text"], input[type="password"] {
            width: 80%;
            padding: 12px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
        }

        input::placeholder {
            color: #ccc;
        }

        /* Tombol */
        button {
            padding: 12px 20px;
            margin-top: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-btn {
            background-color: #007BFF;
            color: white;
        }

        .login-btn:hover {
            background-color: rgb(0, 61, 126);
        }

        /* Validasi baru */
        form.submitted input:invalid {
            border-color: #ff4757 !important;
            animation: shake 0.3s;
        }

        form.submitted input:invalid + .error-message {
            display: block;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }

        .error-message {
            display: none;
            color: #ff6b81;
            font-size: 13px;
            margin-top: 5px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            margin-bottom: 10px;
        }

        .error {
            color: #ff6b81;
            margin-top: 15px;
            font-size: 14px;
        }

        a {
            color: #66b3ff;
        }
    </style>
</head>

<body>

<div class="overlay"></div>

<div class="form-container">
    <h2>Buat Akun Baru</h2>

    <form method="POST" action="">
        <!-- Username -->
        <div class="input-group">
            <input type="text" name="username" placeholder="Username" required>
            <div class="error-message">Username wajib diisi</div>
        </div>

        <!-- Password -->
        <div class="input-group">
            <input type="password" name="password" placeholder="Password" required>
            <div class="error-message">Password wajib diisi</div>
        </div>

        <button type="submit" class="login-btn">Daftar Sekarang</button>
    </form>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <p style="margin-top: 10px">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </p>
</div>

<script>
    // Tambahkan class submitted supaya error hanya muncul setelah tombol ditekan
    document.querySelector("form").addEventListener("submit", function () {
        this.classList.add("submitted");
    });
</script>

</body>
</html>
