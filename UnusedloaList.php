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
    <title>Laporan Jumlah LOA Available</title>
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
            text-transform: uppercase; /* Menjadikan semua teks uppercase */
            font-family: "Roboto Slab", serif;
        }


        .header a.btn {
            margin: 20px; /* Jarak antara tombol dan judul */
            padding: 10px 15px; /* Padding tombol */
            transition: background-color 0.3s; /* Transisi pada hover */
            border-radius: 50px; /* Sudut membulat */
            width: 130px; /* Lebar tombol */
        }

        .header h1 {
            flex: 0.9; /* Mengambil ruang yang tersedia */
            text-align: center; /* Memusatkan teks */
            margin: 0; /* Menghapus margin default */
            line-height: 1.6; /* Mengatur jarak antar baris */
        }

        table {
            width: 50%;
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
        }
        a:hover {
            background-color: #357ab8;
        }
        .grand-total {
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        .bank-header {
            background-color: #cce5ff; /* Warna latar belakang default */
            cursor: pointer; /* Mengubah kursor menjadi pointer */
            transition: background-color 0.3s; /* Transisi untuk efek halus */
        }

        .bank-header:hover {
            background-color: #a4c8e1; /* Warna latar belakang saat hover */
        }


    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body>

<div class="header d-flex align-items-center" style="padding-top: 10px;">
                <a class="btn btn-primary d-flex align-items-center" href="/CurdGiro/dashboard.php#loa" style="margin-right: 20px; transition: background-color 0.3s; border-radius: 50px; width: 120px;">
                    <i class="bi bi-backspace" style="margin-right: 8px;"></i>
                    Kembali
                </a>
                <h1 class="mb-0" style="line-height: 1; margin: 0;">Laporan Jumlah LOA Available</h1>
            </div>


<br><br>
    
    <?php if (empty($report_data)): ?>
        <p style="text-align: center;">Tidak ada data LOA.</p>
    <?php else: ?>
        <table class="mx-auto px-4">
    <thead>
        <tr>
            <th>Nama Entitas</th>
            <th>Bank</th>
            <th>Jumlah LOA</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($report_data as $nama_entitas => $banks): ?>
            <tr class="entity-header">
                <td colspan="3"><?php echo htmlspecialchars($nama_entitas); ?></td>
            </tr>
            <?php foreach ($banks as $bank => $loaList): ?>
                <?php $uniqueId = htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank); ?>
                <tr class="bank-header" onclick="toggleloaList('<?php echo $uniqueId; ?>')">
                    <td></td>
                    <td><?php echo htmlspecialchars($bank); ?></td>
                    <td><?php echo count($loaList); ?></td>
                </tr>
                <tr class="loa-list" id="<?php echo $uniqueId; ?>">
                    <td colspan="3">
                        <table style="width: 100%;">
                            <thead>                
                                <tr>
                                    <th>No Urut</th>
                                    <th>No loa</th>
                                    <th onclick="sortloaList('<?php echo $uniqueId; ?>')" style="cursor: pointer;">AC Number</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                usort($loaList, function($a, $b) {
                                    return strcmp($a['ac_number'], $b['ac_number']);
                                });
                                
                                foreach ($loaList as $index => $loa): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
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

<script>
function toggleloaList(uniqueId) {
    var loaList = document.getElementById(uniqueId);
    if (loaList.style.display === "none" || loaList.style.display === "") {
        loaList.style.display = "table-row"; // Show the list
    } else {
        loaList.style.display = "none"; // Hide the list
    }
}
</script>

        <div class="grand-total">
            Grand Total: <?php echo $grand_total; ?> LOA
        </div>
        <br><br>
    <?php endif; ?>

</body>
</html>
