<?php
ob_start();

require 'dompdf/autoload.inc.php';
require 'koneksi.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// QUERY SUDAH DISESUAIKAN DENGAN DATABASE
$query = "
SELECT 
    transactions.id,
    transactions.tanggal_pinjam,
    transactions.tanggal_kembali,
    transactions.status,
    users.username,
    books.judul_buku,
    books.harga_per_hari
FROM transactions
JOIN users ON transactions.id_user = users.id
JOIN books ON transactions.id_buku = books.id
ORDER BY transactions.id DESC
";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("Gagal ambil data transaksi: " . mysqli_error($conn));
}

// HTML untuk PDF
$html = '
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; }
    h2 { text-align: center; margin-bottom: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td {
        border: 1px solid #444;
        padding: 6px;
        text-align: center;
    }
    th { background: #f0f0f0; }
</style>

<h2>Laporan Transaksi Peminjaman Buku</h2>
<table>
<thead>
<tr>
    <th>ID</th>
    <th>Username</th>
    <th>Judul Buku</th>
    <th>Tgl Pinjam</th>
    <th>Tgl Kembali</th>
    <th>Lama Pinjam</th>
    <th>Total Harga</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
';

// LOOP DATA
while ($row = mysqli_fetch_assoc($result)) {

    $tglPinjam = new DateTime($row['tanggal_pinjam']);
    $tglKembali = new DateTime($row['tanggal_kembali']);
    $lama = $tglPinjam->diff($tglKembali)->days;
    if ($lama < 1) $lama = 1;

    $total = $lama * $row['harga_per_hari'];

    $html .= '
    <tr>
        <td>'.$row['id'].'</td>
        <td>'.htmlspecialchars($row['username']).'</td>
        <td>'.htmlspecialchars($row['judul_buku']).'</td>
        <td>'.$row['tanggal_pinjam'].'</td>
        <td>'.$row['tanggal_kembali'].'</td>
        <td>'.$lama.' hari</td>
        <td>Rp '.number_format($total, 0, ',', '.').'</td>
        <td>'.htmlspecialchars($row['status']).'</td>
    </tr>';
}

$html .= '</tbody></table>';

// DOMPDF
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$dompdf->stream("laporan_transaksi.pdf", ["Attachment" => false]);

ob_end_flush();
exit;
?>
