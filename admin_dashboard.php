<?php
// Mulai sesi agar kita bisa akses data session seperti username dan role
session_start();

// Cek dulu, kalau belum login atau bukan admin, langsung alihkan ke halaman login
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Admin - Perpusku</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reset margin bawaan dan set font utama */
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            color: #2c3e50;
            min-height: 100vh;
            position: relative;
            display: flex;
        }

        /* Background pattern untuk tema perpustakaan */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(255,255,255,0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(255,255,255,0.05) 0%, transparent 50%);
            background-size: 100px 100px;
            pointer-events: none;
            z-index: -1;
        }

        /* Sidebar styling */
        .sidebar {
            width: 250px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        /* Logo dan judul sidebar */
        .sidebar-header {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .sidebar-header h2 i {
            color: #3498db;
        }

        .sidebar-header p {
            margin: 8px 0 0;
            font-size: 14px;
            color: #7f8c8d;
        }

        /* Menu navigasi sidebar */
        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: #2c3e50;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar-menu a:hover {
            background: rgba(52, 152, 219, 0.1);
            border-left-color: #3498db;
        }

        .sidebar-menu a.active {
            background: rgba(52, 152, 219, 0.15);
            border-left-color: #3498db;
            color: #3498db;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }

        /* Bagian footer sidebar */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-role {
            font-size: 12px;
            color: #7f8c8d;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(231, 76, 60, 0.4);
        }

        /* Main content area */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 30px;
        }

        /* Header di main content */
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .content-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .content-header h1 i {
            color: white;
        }

        /* Container statistik */
        .statistics-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
        }

        .statistics-title {
            text-align: center;
            margin-bottom: 40px;
            font-size: 24px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .statistics-title i {
            color: #3498db;
            font-size: 28px;
        }

        .charts-container {
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }

        .chart-wrapper {
            flex: 1;
            max-width: 500px;
            min-width: 300px;
            background: rgba(255, 255, 255, 0.8);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .chart-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .chart-title i {
            color: #3498db;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .charts-container {
                flex-direction: column;
                gap: 30px;
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-book-open"></i>Perpusku</h2>
        <p>Dashboard Admin</p>
    </div>
    
    <div class="sidebar-menu">
        <ul>
            <li><a href="#" class="active"><i class="fas fa-chart-bar"></i>Dashboard</a></li>
            <li><a href="kelola_buku.php"><i class="fas fa-book"></i>Manajemen Buku</a></li>
            <li><a href="transactions.php"><i class="fas fa-exchange-alt"></i>Data Transaksi</a></li>
            <li><a href="kelola_user.php"><i class="fas fa-users"></i>Manajemen Pengguna</a></li>
        </ul>
    </div>
    
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?= strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 1)) ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?= htmlspecialchars($_SESSION['username']) ?></div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i>Logout</a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div class="content-header">
        <h1><i class="fas fa-chart-bar"></i>Dashboard Admin</h1>
    </div>
    
    <div class="statistics-container">
        <h2 class="statistics-title"><i class="fas fa-chart-bar"></i>Statistik Peminjaman</h2>
        <div class="charts-container">
            <div class="chart-wrapper">
                <h3 class="chart-title"><i class="fas fa-trophy"></i>Buku Terpopuler</h3>
                <canvas id="chartBuku" height="250"></canvas>
            </div>
            <div class="chart-wrapper">
                <h3 class="chart-title"><i class="fas fa-calendar-alt"></i>Jumlah Peminjam per Bulan</h3>
                <canvas id="chartBulan" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
fetch('data_statistik.php')
    .then(res => res.json())
    .then(data => {
        const ctxBuku = document.getElementById('chartBuku').getContext('2d');
        const ctxBulan = document.getElementById('chartBulan').getContext('2d');

        new Chart(ctxBuku, {
            type: 'bar',
            data: {
                labels: data.top_buku.map(d => d.judul_buku),
                datasets: [{
                    label: 'Jumlah Dipinjam',
                    data: data.top_buku.map(d => d.total),
                    backgroundColor: 'rgba(52, 152, 219, 0.7)'
                }]
            }
        });

        new Chart(ctxBulan, {
            type: 'bar',
            data: {
                labels: data.peminjam_bulanan.map(d => d.bulan),
                datasets: [{
                    label: 'Jumlah Peminjam',
                    data: data.peminjam_bulanan.map(d => d.total),
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    fill: true,
                    tension: 0.3
                }]
            }
        });
    });
</script>
</body>
</html>