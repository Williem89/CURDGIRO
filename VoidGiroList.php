<?php
include 'koneksi.php';
// Prepare the statement
$sql = "SELECT e.nama_entitas, 
       d.namabank, 
       d.ac_number, 
       dg.ac_penerima, 
       dg.nama_penerima, 
       dg.bank_penerima, 
       dg.nogiro, 
       SUM(dg.nominal) AS total_nominal, 
       dg.tanggal_jatuh_tempo, 
       dg.PVRNo, 
       dg.keterangan, 
       dg.nominal as Nominal
FROM detail_giro AS dg
INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
WHERE dg.StatGiro='Void'
GROUP BY e.nama_entitas, 
         d.namabank, 
         d.ac_number, 
         dg.ac_penerima, 
         dg.nama_penerima, 
         dg.bank_penerima, 
         dg.nogiro, 
         dg.tanggal_jatuh_tempo, 
         dg.PVRNo, 
         dg.keterangan
ORDER BY e.nama_entitas, dg.nogiro;
";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold Void giro records
$Void_giro_records = [];
while ($row = $result->fetch_assoc()) {
    $Void_giro_records[] = $row;
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
    <title>Daftar Giro Void</title>
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
            font-size: 12px;
        }

        th {
            background-color: #007bff;
            color: white;
            text-align: center;
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
    <div class="container" style="width:100%; max-width:2000px">
        <h1 class="text-center">Daftar Giro Void</h1>
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>No Giro</th>
                <th>No Rek Asal</th>
                <th>Bank Asal</th>
                <th>Rekening Tujuan</th>
                <th>Atas Nama</th>
                <th>Bank Tujuan</th>
                <th>No PVR</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($Void_giro_records)): ?>
                <tr>
                    <td colspan="7" class="no-data">Tidak ada data giro.</td>
                </tr>
            <?php else: ?>
                <?php 
                $current_entity = '';
                $current_bank = '';
                $subtotal = 0;
                $grand_total = 0;

                foreach ($Void_giro_records as $giro): 
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
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d-M-Y', strtotime($giro['tanggal_jatuh_tempo'])); ?></td>  
                        <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                        <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['nama_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['bank_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['PVRNo']); ?></td>
                        <td><?php echo htmlspecialchars($giro['keterangan']); ?></td>
                        <td><?php echo 'Rp. '. number_format($giro['Nominal'], 2, ',', '.'); ?></td>
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
