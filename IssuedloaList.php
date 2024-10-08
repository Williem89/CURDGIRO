<?php
include 'koneksi.php';

// Pastikan $selected_month dan $selected_year sudah di-set sebelumnya
$selected_month = 9; // Contoh: September
$selected_year = 2024; // Contoh: 2024

// Prepare the statement
$sql = "SELECT e.nama_entitas, d.namabank, d.ac_number, dg.noloa, SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo 
        FROM detail_loa AS dg
        INNER JOIN data_loa AS d ON dg.noloa = d.noloa
        INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
        WHERE dg.Statloa = 'Issued' 
        GROUP BY dg.tanggal_jatuh_tempo, e.nama_entitas, d.namabank, d.ac_number, dg.noloa
        ORDER BY dg.tanggal_jatuh_tempo ASC;";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold issued loa records
$issued_loa_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_loa_records[] = $row;
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
    <title>Daftar loa Issued</title>
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
        <h1 class="text-center">Daftar loa Issued</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Entitas</th>
                    <th>No loa</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Bank</th>
                    <th>No. Rekening</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($issued_loa_records)): ?>
                <tr>
                    <td colspan="6" class="no-data">Tidak ada data loa.</td>
                </tr>
            <?php else: ?>
                <?php 
                $current_entity = '';
                $current_bank = '';
                $subtotal = 0;
                $grand_total = 0;

                foreach ($issued_loa_records as $loa): 
                    // Update subtotal
                    $subtotal += $loa['total_nominal'];
                    $grand_total += $loa['total_nominal'];

                    // Check if we need to output a new entity
                    if ($current_entity !== $loa['nama_entitas']) {
                        // Output subtotal for the previous entity
                        if ($current_entity !== '') {
                            echo '<tr class="subtotal"><td colspan="5">Subtotal</td><td>' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                        }

                        // Reset subtotal for new entity
                        $subtotal = $loa['total_nominal'];
                        $current_entity = $loa['nama_entitas'];

                        echo '<tr class="group-header"><td colspan="6">' . htmlspecialchars($current_entity) . '</td></tr>';
                    }

                    // Check if we need to output a new bank
                    if ($current_bank !== $loa['namabank']) {
                        $current_bank = $loa['namabank'];
                        echo '<tr class="group-header"><td colspan="6">' . htmlspecialchars($current_bank) . '</td></tr>';
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loa['nama_entitas']); ?></td>
                        <td><?php echo htmlspecialchars($loa['noloa']); ?></td>
                        <td><?php echo htmlspecialchars($loa['tanggal_jatuh_tempo']); ?></td>
                        <td><?php echo htmlspecialchars($loa['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($loa['ac_number']); ?></td>
                        <td>&#36; <?php echo number_format($loa['total_nominal'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>

                <!-- Output subtotal for the last entity -->
                <tr class="subtotal"><td colspan="5">Subtotal</td><td>&#36; <?php echo number_format($subtotal, 2, ',', '.'); ?></td></tr>
                <tr class="grand-total"><td colspan="5">Grand Total</td><td>&#36; <?php echo number_format($grand_total, 2, ',', '.'); ?></td></tr>
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
