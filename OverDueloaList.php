<?php
include 'koneksi.php';

// Calculate the current date
$current_date = date('d-m-y');
$stmt = $conn->prepare("
    SELECT noloa, tanggal_jatuh_tempo, nominal 
    FROM detail_loa 
    WHERE Statloa = 'Issued' AND tanggal_jatuh_tempo < ?
");
$stmt->bind_param("s", $current_date);
$stmt->execute();
$result = $stmt->get_result();

$issued_loa_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_loa_records[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar loa Lewat Jatuh Tempo</title>
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
            background: linear-gradient(to right, #ff9eb3, #ff4d94);
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background: linear-gradient(to right, #ff9eb3, #ff4d94);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }

        a:hover {
            background: linear-gradient(to right, #ff4d94, #ff6f99);
        }
    </style>
</head>
<body>
    <h1>Daftar loa yang Lewat Jatuh Tempo</h1>
    <table>
        <tr>
            <th>No loa</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_loa_records as $loa): ?>
            <tr>
                <td><?php echo htmlspecialchars($loa['noloa']); ?></td>
                <td><?php echo htmlspecialchars($loa['tanggal_jatuh_tempo']); ?></td>
                <td>&#36; <?php echo number_format($loa['nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
