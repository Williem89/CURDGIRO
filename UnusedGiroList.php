<?php
include 'koneksi.php';

$stmt = $connection->prepare("SELECT nogiro, tanggal_jatuh_tempo, nominal FROM detail_giro WHERE StatGiro = 'Unused'");
$stmt->execute();
$result = $stmt->get_result();

$unused_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $unused_giro_records[] = $row;
}

$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Unused</title>
</head>
<body>
    <h1>Daftar Giro Unused</h1>
    <table border="1">
        <tr>
            <th>No Giro</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php foreach ($unused_giro_records as $giro): ?>
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
