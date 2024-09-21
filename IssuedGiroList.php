<?php
include 'koneksi.php';

$three_days_ahead = date('Y-m-d', strtotime('+3 days'));
$stmt = $connection->prepare("
    SELECT nogiro, tanggal_jatuh_tempo, nominal 
    FROM detail_giro 
    WHERE StatGiro = 'Issued' AND tanggal_jatuh_tempo <= ?
");
$stmt->bind_param("s", $three_days_ahead);
$stmt->execute();
$result = $stmt->get_result();

$issued_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_giro_records[] = $row;
}

$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Issued</title>
</head>
<body>
    <h1>Daftar Giro Issued yang Jatuh Tempo dalam 3 Hari</h1>
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
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
