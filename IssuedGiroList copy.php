<?php
include 'koneksi.php';

// Prepare the statement to get unused giro records grouped by bank and entity names
$query = "
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
           dg.nominal as Nominal
    FROM detail_giro AS dg
    INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE dg.StatGiro='Issued'
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
    ORDER BY e.nama_entitas, dg.nogiro
";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold the report data
$report_data = [];
$total_nominal =0;
$grand_total = 0;

while ($row = $result->fetch_assoc()) {
    $report_data[$row['nama_entitas']][$row['namabank']][] = $row;
}

// Calculate grand total count of records
foreach ($report_data as $banks) {
    foreach ($banks as $giroList) {
        $grand_total += count($giroList);
    }
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
    <title>GIRO ISSUED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            display: flex;
            align-items: center;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-transform: uppercase;
            font-family: "Roboto Slab", serif;
        }
        .header a.btn {
            margin: 20px;
            padding: 10px 15px;
            transition: background-color 0.3s;
            border-radius: 50px;
            width: 130px;
        }
        .header h1 {
            flex: 0.9;
            text-align: center;
            margin: 0;
            line-height: 1.6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #4a90e2;
            color: white;
            font-weight: bold;
        }
        .bank-header, .entity-header {
            cursor: pointer;
        }
        .giro-list {
            display: none;
            padding-left: 20px;
        }
        .grand-total {
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleGiroList(uniqueId) {
            const giroList = document.getElementById(uniqueId);
            giroList.style.display = giroList.style.display === "none" || giroList.style.display === "" ? "table-row" : "none";
        }
    </script>
</head>
<body>
    <div class="header">
        <a class="btn btn-primary" href="/CurdGiro/dashboard.php#cek">
            <i class="bi bi-backspace" style="margin-right: 8px;"></i> Kembali
        </a>
        <h1>Laporan Jumlah Giro Available</h1>
    </div>

    <?php if (empty($report_data)): ?>
        <p style="text-align: center;">Tidak ada data giro.</p>
    <?php else: ?>
        <table class="mx-auto">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal Giro</th>
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
                <?php $no = 1; ?>
                <?php foreach ($report_data as $nama_entitas => $banks): ?>
                    <tr class="entity-header">
                        <td colspan="11"><?php echo htmlspecialchars($nama_entitas); ?></td>
                    </tr>
                    <?php foreach ($banks as $bank => $giroList): ?>
                        <tr class="bank-header" onclick="toggleGiroList('<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank); ?>')">
                             <td><?php echo htmlspecialchars($giroList[0]['namabank']); ?></td>
                             <td><?php echo count($giroList); ?></td>                        
                        </tr>
                        <tr class="giro-list" id="<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank); ?>">
                            <td colspan="11">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Tanggal Giro</th>
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
                                        <?php foreach ($giroList as $index => $giro): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo date('d-M-Y', strtotime($giro['tanggal_jatuh_tempo'])); ?></td>
                                                <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['ac_penerima']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['nama_penerima']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['bank_penerima']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['PVRNo']); ?></td>
                                                <td><?php echo htmlspecialchars($giro['keterangan']); ?></td>
                                                <td><?php echo 'Rp. ' . number_format($giro['Nominal'], 2, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if ($current_entity !== ''): ?>
                        <tr class="subtotal">
                            <td colspan="10">Subtotal</td>
                            <td><?php echo 'Rp. ' . number_format($subtotals[$current_entity], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="grand-total">
            Grand Total: <?php echo $grand_total; ?> Giro
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
