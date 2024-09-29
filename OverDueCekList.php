<?php
include 'koneksi.php';

// Calculate the current date
$current_date = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT nocek, tanggal_jatuh_tempo, nominal 
    FROM detail_cek 
    WHERE Statcek = 'Issued' AND tanggal_jatuh_tempo < ?
");
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

$issued_cek_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_cek_records[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar cek Lewat Jatuh Tempo</title>
</head>
<body>
    <h1>Daftar cek yang Lewat Jatuh Tempo</h1>
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
