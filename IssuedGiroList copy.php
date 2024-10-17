<?php
include 'koneksi.php';

// Prepare the statement to get unused giro records grouped by bank and entity names
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
    WHERE dg.StatGiro='Issued'
    GROUP BY e.nama_entitas, d.namabank, d.ac_number, dg.ac_penerima, dg.nama_penerima, dg.bank_penerima, dg.nogiro, dg.tanggal_jatuh_tempo, dg.PVRNo, dg.keterangan
    ORDER BY e.nama_entitas, dg.nogiro
");

if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

$report_data = []; // Initialize the array

while ($row = $result->fetch_assoc()) {
    $nama_entitas = $row['nama_entitas'] ?? 'Unknown Entity';
    $namabank = $row['namabank'] ?? 'Unknown Bank';
    $ac_number = $row['ac_number'] ?? 'Unknown AC Number';
    
    // Initialize the nested structure if it doesn't exist
    if (!isset($report_data[$nama_entitas][$namabank][$ac_number])) {
        $report_data[$nama_entitas][$namabank][$ac_number] = [];
    }
    
    // Add the giro information to the array
    $report_data[$nama_entitas][$namabank][$ac_number][] = [
        'nogiro' => $row['nogiro'] ?? null,
        'total_nominal' => $row['total_nominal'] ?? 0,
        'nominal' => $row['Nominal'] ?? 0,
        'tanggal_jatuh_tempo' => $row['tanggal_jatuh_tempo'] ?? 'Unknown Date',
        'ac_number' => $row['ac_number'] ?? 'Unknown AC Number',
        'namabank' => $row['namabank'] ?? 'Unknown Bank',
        'ac_penerima' => $row['ac_penerima'] ?? 'Unknown Account',
        'nama_penerima' => $row['nama_penerima'] ?? 'Unknown Name',
        'bank_penerima' => $row['bank_penerima'] ?? 'Unknown Bank',
        'PVRNo' => $row['PVRNo'] ?? '',
        'keterangan' => $row['keterangan'] ?? 'No Description',
    ];
}

// Calculate grand total
$grand_total = 0;
foreach ($report_data as $banks) {
    foreach ($banks as $acNumbers) {
        foreach ($acNumbers as $giroList) {
            $grand_total += count($giroList);
        }
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
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <title>GIRO ISSUED</title>
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

        .giro-list {
        display: none; /* Hide all giro lists by default */
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
    <div class="header">
        <a class="btn btn-primary" href="/CurdGiro/dashboard.php#cek">Kembali</a>
        <h1>GIRO Available</h1>
    </div>

    <?php if (empty($report_data)): ?>
        <p style="text-align: center;">Tidak ada data giro.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nama Entitas</th>
                    <th>Bank</th>
                    <th>AC Number</th>
                    <th>Jumlah Giro</th>
                    <th>Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $nama_entitas => $banks): ?>
                    <tr class="entity-header">
                        <td colspan="5"><?php echo htmlspecialchars($nama_entitas); ?></td>
                    </tr>
                    <?php foreach ($banks as $bank => $acNumbers): ?>
                        <tr class="bank-header" style="text-align: center;">
                            <td><br></td>
                            <td><?php echo htmlspecialchars($bank); ?></td>
                            <td><br></td>
                            <td><br></td>
                            <td><br></td>

                        </tr>
                        <?php foreach ($acNumbers as $ac_number => $giroList): ?>
                            <?php 
                            $totalNominal = array_sum(array_column($giroList, 'total_nominal')); 
                            ?>
                           <tr class="ac-header" style="text-align: center;" onclick="toggleGiroList('<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>')">
                                <td></td>
                                <td></td>
                                <td><?php echo htmlspecialchars($ac_number); ?></td>
                                <td><?php echo count($giroList); ?></td>
                                <td><?php echo 'Rp. ' . number_format($totalNominal, 2, ',', '.'); ?></td>

                            </tr>
                            <tr class="giro-list" id="<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>">
                                <td colspan="5">
                                <table class="table table-bordered table-striped" >
                                        <thead>
                                            <tr>
                                                <th style="width:5px; text-align:center;">No</th>
                                                <th style="width:90px;text-align:center;">Tanggal Giro</th>
                                                <th style="width:90px; text-align:center;">No Giro</th>
                                                <th style="width:110px;text-align:center;">No Rek Asal</th>
                                                <th style="width:150px;text-align:center;">Bank Asal</th>
                                                <th style="width:170px;text-align:center;">Rekening Tujuan</th>
                                                <th style="width:260px;text-align:center;">Atas Nama</th>
                                                <th style="width:150px;text-align:center;">Bank Tujuan</th>
                                                <th style="width:150px;text-align:center;">No PVR</th>
                                                <th style="width:150px;text-align:center;">Keterangan</th>
                                                <th style="width:150px;text-align:center;">Nominal</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody id="<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>-body">
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
                                                    <td><?php echo 'Rp. ' . number_format($giro['nominal'], 2, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="grand-total">Grand Total: <?php echo $grand_total; ?> Giro</div>
    <?php endif; ?>

    <script>
        function toggleGiroList(uniqueId) {
            const giroList = document.getElementById(uniqueId);
            giroList.style.display = giroList.style.display === "none" || giroList.style.display === "" ? "table-row" : "none";
        }

        function sortGiroList(uniqueId) {
            const tbody = document.getElementById(uniqueId + '-body');
            const rows = Array.from(tbody.rows);
            
            // Sort rows based on the AC Number (third column)
            rows.sort((a, b) => {
                const acA = a.cells[2].innerText.trim().toLowerCase();
                const acB = b.cells[2].innerText.trim().toLowerCase();
                return acA.localeCompare(acB);
            });
            
            // Clear the tbody and append the sorted rows
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
