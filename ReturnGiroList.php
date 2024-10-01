<?php
include 'koneksi.php';

// Pastikan $selected_month dan $selected_year sudah di-set sebelumnya
$selected_month = 9; // Contoh: September
$selected_year = 2024; // Contoh: 2024

// Prepare the statement
$sql = "SELECT e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, SUM(dg.Nominal) AS total_nominal, 
               dg.tanggal_jatuh_tempo, dg.tglkembalikebank 
        FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
        WHERE dg.StatGiro = 'Return' 
        AND MONTH(dg.tanggal_jatuh_tempo) = ? 
        AND YEAR(dg.tanggal_jatuh_tempo) = ?
        GROUP BY dg.tanggal_jatuh_tempo, e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.tglkembalikebank
        ORDER BY dg.tanggal_jatuh_tempo ASC;";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $selected_month, $selected_year);

if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold Return giro records
$Return_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $Return_giro_records[] = $row;
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
    <title>Daftar Giro Return</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 30px;
        }
        h1 {
            margin-bottom: 20px;
            color: #0056b3;
        }
        table {
            margin-top: 20px;
            border: 1px solid #dee2e6;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        td {
            background-color: white;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
        .group-header {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .subtotal {
            font-weight: bold;
            background-color: #d1ecf1;
        }
        .grand-total {
            font-weight: bold;
            background-color: #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Daftar Giro Return</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Entitas</th>
                    <th>No Giro</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Tanggal Giro Cair</th> <!-- Kolom baru -->
                    <th>Bank</th>
                    <th>No. Rekening</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($Return_giro_records)): ?>
                <tr>
                    <td colspan="7" class="no-data">Tidak ada data giro.</td>
                </tr>
            <?php else: ?>
                <?php 
                $current_entity = '';
                $current_bank = '';
                $subtotal = 0;
                $grand_total = 0;

                foreach ($Return_giro_records as $giro): 
                    // Update subtotal
                    $subtotal += $giro['total_nominal'];
                    $grand_total += $giro['total_nominal'];

                    // Check if we need to output a new entity
                    if ($current_entity !== $giro['nama_entitas']) {
                        // Output subtotal for the previous entity
                        if ($current_entity !== '') {
                            echo '<tr class="subtotal"><td colspan="6">Subtotal</td><td>' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                        }

                        // Reset subtotal for new entity
                        $subtotal = $giro['total_nominal'];
                        $current_entity = $giro['nama_entitas'];

                        echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_entity) . '</td></tr>';
                    }

                    // Check if we need to output a new bank
                    if ($current_bank !== $giro['namabank']) {
                        $current_bank = $giro['namabank'];
                        echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_bank) . '</td></tr>';
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($giro['nama_entitas']); ?></td>
                        <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                        <td><?php echo htmlspecialchars($giro['tglkembalikebank']); ?></td> <!-- Kolom baru -->
                        <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                        <td><?php echo number_format($giro['total_nominal'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Output subtotal for the last entity -->
                <tr class="subtotal"><td colspan="6">Subtotal</td><td><?php echo number_format($subtotal, 2, ',', '.'); ?></td></tr>
                <tr class="grand-total"><td colspan="6">Grand Total</td><td><?php echo number_format($grand_total, 2, ',', '.'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
