<?php
include 'koneksi.php';

// Calculate the date for 7 days ahead in the correct format
$seven_days_ahead = date('Y-m-d', strtotime('+7 days'));

// Prepare the statement to get issued giro records due in 7 days
$stmt = $conn->prepare("
    SELECT d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.bank_penerima, dg.nogiro, dg.nominal as Nominal, 
               SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.PVRNo, dg.keterangan 
    FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        WHERE dg.StatGiro = 'Issued' 
        AND dg.tanggal_jatuh_tempo BETWEEN NOW() AND ?
        GROUP BY dg.tanggal_jatuh_tempo, d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.nogiro, dg.PVRNo, dg.keterangan
        ORDER BY dg.tanggal_jatuh_tempo ASC
");

if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $seven_days_ahead);
$stmt->execute();
$result = $stmt->get_result();
$no=1;

$issued_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_giro_records[] = $row;
}

// Prepare the statement to count issued giro records due in 7 days
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS jt_count 
    FROM detail_giro 
    WHERE Statgiro = 'Issued' 
    AND DATEDIFF(tanggal_jatuh_tempo, NOW()) BETWEEN 0 AND 7
");

if (!$count_stmt) {
    die("Preparation failed: " . $conn->error);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$jt_count = (int)$count_row['jt_count'];

$count_stmt->close();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Issued</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: linear-gradient(to right, #ffeb3b, #ffc107);
            color: #333;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        p {
            text-align: center;
            font-size: 1.2em;
            color: #666;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background: linear-gradient(to right, #ffeb3b, #ffc107);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background-color 0.3s;
        }

        a:hover {
            background: linear-gradient(to right, #ffc107, #ffca28);
        }
    </style>
</head>
<body>
    <h1>Daftar Giro Issued yang Jatuh Tempo dalam 7 Hari</h1>
    <p>Total Giro yang Jatuh Tempo: <?php echo $jt_count; ?></p>
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>No Giro</th>
            <th>No Rek Asal</th>
            <th>Bank Asal</th>
            <th>Rekening Tujuan</th>
            <th>Atas Nama</th>
            <th>Bank Tujuan</th>
            <th>No PVR</th>
            <th>Keterangan</th>         
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_giro_records as $giro): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                <td><?php echo htmlspecialchars($giro['ac_name']); ?></td>
                <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                <td><?php echo htmlspecialchars($giro['ac_penerima']); ?></td>
                <td><?php echo htmlspecialchars($giro['nama_penerima']); ?></td>
                <td><?php echo htmlspecialchars($giro['bank_penerima']); ?></td>
                <td><?php echo htmlspecialchars($giro['PVRNo']); ?></td>
                <td><?php echo htmlspecialchars($giro['keterangan']); ?></td>
                <td><?php echo number_format($giro['Nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
