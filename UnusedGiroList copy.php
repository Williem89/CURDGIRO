<?php
include 'koneksi.php';

// Prepare the statement to get unused giro records with entity names
$stmt = $conn->prepare("
    SELECT dg.*, le.nama_entitas 
    FROM data_giro dg 
    JOIN list_entitas le ON dg.id_entitas = le.id_entitas
    WHERE dg.StatusGiro = 'Unused'
");
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

// Group records by entity name and bank
$grouped_records = [];
foreach ($unused_giro_records as $giro) {
    $grouped_records[$giro['nama_entitas']][$giro['namabank']][] = $giro;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Unused</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            color: #333;
            margin: 20px;
        }
        h1 {
            color: #5a9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #d0e7d0;
            color: #333;
        }
        .bank-header {
            background-color: #c0e0c0;
            font-weight: bold;
        }
        .entity-header {
            background-color: #a0d0a0;
            font-weight: bold;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #5a9;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #487;
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
                    <th>Nama Entitas</th>
                    <th>Bank</th>
                    <th>No Giro</th>
                    <th>AC Number</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($grouped_records as $nama_entitas => $banks): ?>
                    <tr class="entity-header">
                        <td colspan="4"><?php echo htmlspecialchars($nama_entitas); ?></td>
                    </tr>
                    <?php foreach ($banks as $bank => $giroList): ?>
                        
                        <?php foreach ($giroList as $giro): ?>
                            <tr>
                          
                            <td></td>
                                <!-- Leave the first cell empty to align with the bank header -->
                                <td><?php echo htmlspecialchars($bank); ?></td>
                                <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                                <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
