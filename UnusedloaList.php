<?php
include 'koneksi.php';

// Prepare the statement to get unused loa records grouped by bank and entity names
$stmt = $conn->prepare("
    SELECT le.nama_entitas, dg.namabank, dg.noloa, dg.ac_number
    FROM data_loa dg 
    JOIN list_entitas le ON dg.id_entitas = le.id_entitas
    WHERE dg.Statusloa = 'Unused'
    ORDER BY le.nama_entitas, dg.namabank
");
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold the counts and records, and a variable for grand total
$report_data = [];
$grand_total = 0;

while ($row = $result->fetch_assoc()) {
    $report_data[$row['nama_entitas']][$row['namabank']][] = [
        'noloa' => $row['noloa'],
        'ac_number' => $row['ac_number'],
    ];
}

// Calculate grand total
foreach ($report_data as $banks) {
    foreach ($banks as $loaList) {
        $grand_total += count($loaList);
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
    <title>Laporan Jumlah LOA Unused</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            color: #333;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            color: #4a90e2;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 10px;
            overflow: hidden;
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
        .bank-header {
            background-color: #cce5ff;
            font-weight: bold;
            cursor: pointer;
        }
        .entity-header {
            background-color: #b3d4fc;
            font-weight: bold;
        }
        .loa-list {
            display: none; /* Initially hide the list */
            padding-left: 20px;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
            width: 200px;
            margin-left: auto;
            margin-right: auto;
        }
        a:hover {
            background-color: #357ab8;
        }
        .grand-total {
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        function toggleloaList(bank) {
            const loaList = document.getElementById(bank);
            loaList.style.display = loaList.style.display === "none" ? "table-row" : "none";
        }

        function sortloaList(loaListId) {
            const loaTable = document.querySelector(`#${loaListId} table tbody`);
            const rows = Array.from(loaTable.rows);
            const acNumberIndex = 2;

            rows.sort((rowA, rowB) => {
                const acNumberA = rowA.cells[acNumberIndex].textContent.trim();
                const acNumberB = rowB.cells[acNumberIndex].textContent.trim();
                return acNumberA.localeCompare(acNumberB);
            });

            rows.forEach(row => loaTable.appendChild(row));
        }
    </script>
</head>
<body>
    <h1>Laporan Jumlah loa Unused</h1>
    
    <?php if (empty($report_data)): ?>
        <p style="text-align: center;">Tidak ada data loa.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Nama Entitas</th>
                    <th>Bank</th>
                    <th>Jumlah loa</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $nama_entitas => $banks): ?>
                    <tr class="entity-header">
                        <td colspan="3"><?php echo htmlspecialchars($nama_entitas); ?></td>
                    </tr>
                    <?php foreach ($banks as $bank => $loaList): ?>
                        <tr class="bank-header" onclick="toggleloaList('<?php echo htmlspecialchars($bank); ?>')">
                            <td></td>
                            <td><?php echo htmlspecialchars($bank); ?></td>
                            <td><?php echo count($loaList); ?></td> <!-- Count of noloa for this bank -->
                        </tr>
                        <tr class="loa-list" id="<?php echo htmlspecialchars($bank); ?>">
                            <td colspan="3">
                                <table style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>No Urut</th>
                                            <th>No loa</th>
                                            <th onclick="sortloaList('<?php echo htmlspecialchars($bank); ?>')" style="cursor: pointer;">AC Number</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        usort($loaList, function($a, $b) {
                                            return strcmp($a['ac_number'], $b['ac_number']);
                                        });
                                        
                                        foreach ($loaList as $index => $loa): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td> <!-- Add sequence number -->
                                                <td><?php echo htmlspecialchars($loa['noloa']); ?></td>
                                                <td><?php echo htmlspecialchars($loa['ac_number']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="grand-total">
            Grand Total: <?php echo $grand_total; ?> loa
        </div>
    <?php endif; ?>
    
    <a href="index.php">Kembali ke Halaman Utama</a>
</body>
</html>
