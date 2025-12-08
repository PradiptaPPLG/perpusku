<?php
// Hubungkan ke database
include 'koneksi.php';

// Proses form saat dikirim POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password_plain = $_POST['password'];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);
    $role = 'user';

    // Cek apakah username sudah digunakan
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan. Silakan pilih yang lain.";
    } else {
        $query = "INSERT INTO users (username, password, role) 
                  VALUES ('$username', '$password', '$role')";
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
        /* === Background + Blur Overlay === */
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background: linear-gradient(rgba(0, 0, 0, 0.43)),
                url('https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=1400&q=80')
                no-repeat center center fixed;
            background-size: cover;
        }

        .overlay {
            position: absolute;
            inset: 0;
            backdrop-filter: blur(6px) brightness(0.7);
        }

        /* === Kontainer Form Register === */
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

        /* === Input === */
        input[type="text"], input[type="password"] {
            width: 80%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            outline: none;
        }

        input::placeholder {
            color: #ccc;
        }

        /* === Tombol Daftar === */
        button {
            padding: 12px 20px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            background-color: #007BFF;
            color: white;
            transition: 0.3s;
        }

        button:hover {
            background-color: rgb(0, 61, 126);
        }

        /* === Error Message === */
        .error {
            color: #ff4757;
            margin-top: 10px;
            font-size: 14px;
        }

        /* Link login */
        .login-link {
            margin-top: 15px;
            font-size: 14px;
        }

        .login-link a {
            color: #4aa3ff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        /* Judul / Logo */
        .logo {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #fff;
        }
    </style>
</head>

<body>

<div class="overlay"></div>

<div class="form-container">

    <div class="logo">Perpusku</div>
    <h2>Buat Akun Baru</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>

        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Daftar Sekarang</button>
    </form>

    <?php if (isset($error)): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <div class="login-link">
        Sudah punya akun? <a href="login.php">Masuk di sini</a>
    </div>
</div>

</body>
</html>
