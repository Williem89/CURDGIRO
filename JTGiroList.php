<?php
include 'koneksi.php';

// Calculate the date for 7 days ahead
$seven_days_ahead = date('Y-m-d', strtotime('+7 days'));

// Prepare the statement to get issued giro records due in 7 days
$stmt = $conn->prepare("
    SELECT nogiro, tanggal_jatuh_tempo, nominal 
    FROM detail_giro 
    WHERE StatGiro = 'Issued' AND tanggal_jatuh_tempo BETWEEN NOW() AND ?
");
$stmt->bind_param("s", $seven_days_ahead);
$stmt->execute();
$result = $stmt->get_result();

$issued_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_giro_records[] = $row;
}

// Prepare the statement to count issued giro records due in 7 days
$count_stmt = $conn->prepare("
    SELECT COUNT(*) AS jt_count 
    FROM detail_giro 
    WHERE StatGiro = 'Issued' 
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
    <title>Daftar Giro Issued</title>
</head>
<body>
    <h1>Daftar Giro Issued yang Jatuh Tempo dalam 7 Hari</h1>
    <p>Total Giro yang Jatuh Tempo: <?php echo $jt_count; ?></p>
    <table border="1">
        <tr>
            <th>No Giro</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_giro_records as $giro): ?>
            <tr>
                <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                <td><?php echo number_format($giro['nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
