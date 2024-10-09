<?php
include 'koneksi.php';

// Calculate the date for 7 days ahead in the correct format
$seven_days_ahead = date('Y-m-d', strtotime('+7 days'));

try {
    // Prepare the statement to get issued giro records due in 7 days
    $stmt = $conn->prepare("
        SELECT e.nama_entitas, 
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
               dg.nominal AS Nominal
        FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
        WHERE dg.StatGiro = 'Issued' 
          AND dg.tanggal_jatuh_tempo BETWEEN NOW() AND ?
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
    ");

    if (!$stmt) {
        throw new Exception("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $seven_days_ahead);
    $stmt->execute();
    $result = $stmt->get_result();

    $issued_giro_records = [];
    while ($row = $result->fetch_assoc()) {
        $issued_giro_records[] = $row;
    }

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

$conn->close();

function formatCurrency($amount) {
    return 'Rp. ' . number_format($amount, 2, ',', '.');
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Issued yang Jatuh Tempo dalam 7 Hari</title>
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

        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container" style="width:100%; max-width:2000px">
        <h1 class="text-center">Daftar Giro Issued yang Jatuh Tempo dalam 7 Hari</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th scope="col">No</th>
                    <th scope="col">Tanggal Giro</th>
                    <th scope="col">No Giro</th>
                    <th scope="col">No Rek Asal</th>
                    <th scope="col">Bank Asal</th>
                    <th scope="col">Rekening Tujuan</th>
                    <th scope="col">Atas Nama</th>
                    <th scope="col">Bank Tujuan</th>
                    <th scope="col">No PVR</th>
                    <th scope="col">Keterangan</th>
                    <th scope="col">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($issued_giro_records)): ?>
                    <tr>
                        <td colspan="11" class="no-data">Tidak ada giro yang jatuh tempo dalam 7 hari.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($issued_giro_records as $giro): ?>
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
                            <td><?php echo formatCurrency($giro['Nominal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <a href="dashboard.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
