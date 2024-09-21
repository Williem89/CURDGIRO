<?php
include 'koneksi.php';

// Prepare the statement to get unused giro records
$stmt = $conn->prepare("SELECT * FROM data_giro WHERE StatusGiro = 'Unused'");
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold unused giro records
$unused_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $unused_giro_records[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Group records by bank
$grouped_records = [];
foreach ($unused_giro_records as $giro) {
    $grouped_records[$giro['namabank']][] = $giro;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Unused</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .bank-header {
            background-color: #e0e0e0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Daftar Giro Unused</h1>
    
    <?php if (empty($unused_giro_records)): ?>
        <p>Tidak ada data giro.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Bank</th>
                    <th>No Giro</th>
                    <th>AC Number</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped_records as $bank => $giroList): ?>
                    <tr class="bank-header">
                        <td colspan="3"><?php echo htmlspecialchars($bank); ?></td>
                    </tr>
                    <?php foreach ($giroList as $giro): ?>
                        <tr>
                            <td></td> <!-- Leave the first cell empty to align with the bank header -->
                            <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                            <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <br>
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
