<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['id_user'])) {
    header("Location: user_dashboard.php");
    exit();
}

$error = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id_user'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Perpusku</title>
    <style>
        body {
            margin: 0;
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

        .form-container {
            position: relative;
            z-index: 1;
            max-width: 400px;
            margin: 120px auto;
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.3);
            color: #fff;
            text-align: center;
        }

        input {
            width: 80%;
            padding: 12px;
            margin-top: 10px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.2);
            color: #fff;
            outline: none;
        }

        input::placeholder {
            color: #ccc;
        }

        button {
            margin-top: 15px;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            background-color: #2ecc71;
            color: white;
        }

        button:hover {
            background-color: #27ae60;
        }

        .error {
            margin-top: 15px;
            color: #ff6b81;
        }

        a {
            color: #66b3ff;
        }
    </style>
</head>

<body>

<div class="overlay"></div>

<div class="form-container">
    <h2>Login Perpusku</h2>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <p style="margin-top:10px">
        Belum punya akun? <a href="register.php">Daftar di sini</a>
    </p>
</div>

</body>
</html>
