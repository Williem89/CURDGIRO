<?php
include 'koneksi.php';

// Calculate the date for 7 days ahead
$seven_days_ahead = date('Y-m-d', strtotime('+7 days'));

// Prepare the statement to get issued cek records due in 7 days
$stmt = $conn->prepare("
    SELECT nocek, tanggal_jatuh_tempo, nominal 
    FROM detail_cek 
    WHERE Statcek = 'Issued' AND tanggal_jatuh_tempo BETWEEN NOW() AND ?
");
$stmt->bind_param("s", $seven_days_ahead);
$stmt->execute();
$result = $stmt->get_result();

$issued_cek_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_cek_records[] = $row;
}

// Prepare the statement to count issued cek records due in 7 days
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS jt_count 
    FROM detail_cek 
    WHERE Statcek = 'Issued' 
    AND DATEDIFF(tanggal_jatuh_tempo, NOW()) BETWEEN 0 AND 7
");
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
    <title>Daftar cek Issued</title>
</head>
<body>
    <h1>Daftar cek Issued yang Jatuh Tempo dalam 7 Hari</h1>
    <p>Total cek yang Jatuh Tempo: <?php echo $jt_count; ?></p>
    <table border="1">
        <tr>
            <th>No cek</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_cek_records as $cek): ?>
            <tr>
                <td><?php echo htmlspecialchars($cek['nocek']); ?></td>
                <td><?php echo htmlspecialchars($cek['tanggal_jatuh_tempo']); ?></td>
                <td><?php echo number_format($cek['nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
