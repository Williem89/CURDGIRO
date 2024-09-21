<?php
include 'koneksi.php';

// Prepare the statement
$stmt = $conn->prepare("SELECT nogiro, tanggal_jatuh_tempo, Nominal FROM detail_giro WHERE StatGiro = 'Issued'");
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold issued giro records
$issued_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_giro_records[] = $row;
}

// Close the statement and connection
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
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>Daftar Giro Issued</h1>
    <table>
        <tr>
            <th>No Giro</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php if (empty($issued_giro_records)): ?>
            <tr>
                <td colspan="3">Tidak ada data giro.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($issued_giro_records as $giro): ?>
                <tr>
                    <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                    <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                    <td><?php echo number_format($giro['Nominal']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <br>
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
