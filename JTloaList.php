<?php
include 'koneksi.php';

// Calculate the date for 7 days ahead in the correct format
$seven_days_ahead = date('Y-m-d', strtotime('+7 days'));

// Prepare the statement to get issued loa records due in 7 days
$stmt = $conn->prepare("
    SELECT d.namabank, d.ac_name, dc.ac_penerima, dc.nama_penerima, dc.bank_penerima, dc.noloa, dc.nominal as Nominal, 
           SUM(dc.Nominal) AS total_nominal, dc.tanggal_jatuh_tempo, dc.PVRNo, dc.keterangan 
    FROM detail_loa AS dc
        INNER JOIN data_loa AS d ON dc.noloa = d.noloa
        WHERE dc.Statloa = 'Issued' 
        AND dc.tanggal_jatuh_tempo >= curdate()
        AND dc.tanggal_jatuh_tempo <= ?
        GROUP BY dc.tanggal_jatuh_tempo, d.namabank, d.ac_name, dc.ac_penerima, dc.nama_penerima, dc.noloa, dc.PVRNo, dc.keterangan
        ORDER BY dc.tanggal_jatuh_tempo ASC
");

if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

$stmt->bind_param("s", $seven_days_ahead);
$stmt->execute();
$result = $stmt->get_result();
$no = 1;

$issued_loa_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_loa_records[] = $row;
}

// Prepare the statement to count issued loa records due in 7 days
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS jt_count 
    FROM detail_loa 
    WHERE Statloa = 'Issued' 
    AND tanggal_jatuh_tempo >= curdate()
    AND tanggal_jatuh_tempo <= DATE_ADD(NOW(), INTERVAL 7 DAY)
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
    <title>Daftar LOA Issued</title>
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
    <h1>Daftar LOA Issued yang Jatuh Tempo dalam 7 Hari</h1>
    <p>Total LOA yang Jatuh Tempo: <?php echo $jt_count; ?></p>
    <table>
        <tr>
            <th>No</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>No LOA</th>
            <th>No Rek Asal</th>
            <th>Bank Asal</th>
            <th>Rekening Tujuan</th>
            <th>Atas Nama</th>
            <th>Bank Tujuan</th>
            <th>No PVR</th>
            <th>Keterangan</th>         
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_loa_records as $loa): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($loa['tanggal_jatuh_tempo']); ?></td>
                <td><?php echo htmlspecialchars($loa['noloa']); ?></td>
                <td><?php echo htmlspecialchars($loa['ac_name']); ?></td>
                <td><?php echo htmlspecialchars($loa['namabank']); ?></td>
                <td><?php echo htmlspecialchars($loa['ac_penerima']); ?></td>
                <td><?php echo htmlspecialchars($loa['nama_penerima']); ?></td>
                <td><?php echo htmlspecialchars($loa['bank_penerima']); ?></td>
                <td><?php echo htmlspecialchars($loa['PVRNo']); ?></td>
                <td><?php echo htmlspecialchars($loa['keterangan']); ?></td>
                <td><?php echo number_format($loa['Nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
