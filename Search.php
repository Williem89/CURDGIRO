<?php
include 'koneksi.php';
session_start();

// Get the search term from the GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT 
        'Giro' AS jenis, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        d.Statusgiro AS status, 
        dg.StatGiro AS stat, 
        d.nogiro AS no, 
        dg.nogiro AS no_detail, 
        NULL AS nocek, 
        NULL AS StatCek, 
        NULL AS noloa, 
        SUM(dg.Nominal) AS total_nominal, 
        dg.tanggal_jatuh_tempo, 
        dg.TglVoid, 
        NULL AS jenis_cek, 
        NULL AS jenis_loa
    FROM 
        data_giro AS d
    LEFT JOIN 
        detail_giro AS dg ON dg.nogiro = d.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (d.nogiro LIKE ? OR dg.nogiro LIKE ? OR e.nama_entitas LIKE ? 
        OR d.namabank LIKE ? OR d.Statusgiro LIKE ?) 
        AND (d.Statusgiro = 'Unused' OR dg.StatGiro IN ('Issued', 'Void', 'Return', 'Pending Issued', 'Pending Post', 'Pending Void', 'Pending Return')) 
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number, d.Statusgiro, dg.StatGiro, 
        d.nogiro, dg.nogiro, dg.tanggal_jatuh_tempo, dg.TglVoid

    UNION ALL

    SELECT 
        'Cek' AS jenis, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        d.statuscek AS status, 
        dc.StatCek AS stat, 
        NULL AS nogiro, 
        NULL AS no_detail, 
        d.nocek AS no, 
        dc.nocek AS no_detail, 
        NULL AS noloa, 
        SUM(dc.Nominal) AS total_nominal, 
        dc.tanggal_jatuh_tempo, 
        dc.TglVoid, 
        NULL AS jenis_giro, 
        NULL AS jenis_loa
    FROM 
        data_cek AS d
    LEFT JOIN 
        detail_cek AS dc ON dc.nocek = d.nocek
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (d.nocek LIKE ? OR dc.nocek LIKE ? OR e.nama_entitas LIKE ? 
        OR d.namabank LIKE ? OR d.Statuscek LIKE ?) 
        AND (d.statuscek = 'Unused' OR dc.StatCek IN ('Issued', 'Void', 'Return', 'Pending Issued', 'Pending Post', 'Pending Void', 'Pending Return'))
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number, d.statuscek, dc.StatCek, 
        d.nocek, dc.nocek, dc.tanggal_jatuh_tempo, dc.TglVoid

    UNION ALL

    SELECT 
        'Loa' AS jenis, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        d.statusloa AS status, 
        dl.StatLoa AS stat, 
        NULL AS nogiro, 
        NULL AS no_detail, 
        NULL AS nocek, 
        NULL AS StatCek, 
        d.noloa AS noloa, 
        SUM(dl.Nominal) AS total_nominal, 
        dl.tanggal_jatuh_tempo, 
        dl.TglVoid, 
        NULL AS jenis_giro, 
        NULL AS jenis_cek
    FROM 
        data_loa AS d
    LEFT JOIN 
        detail_loa AS dl ON dl.noloa = d.noloa
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (d.noloa LIKE ? OR dl.noloa LIKE ? OR e.nama_entitas LIKE ? 
        OR d.namabank LIKE ? OR d.Statusloa LIKE ?) 
        AND (d.statusloa = 'Unused' OR dl.StatLoa IN ('Issued', 'Void', 'Return', 'Pending Issued', 'Pending Post', 'Pending Void', 'Pending Return'))
        e.nama_entitas, d.namabank, d.ac_number, d.statusloa, dl.StatLoa, 
        d.noloa, dl.noloa, dl.tanggal_jatuh_tempo, dl.TglVoid

    ORDER BY 
        tanggal_jatuh_tempo ASC;
";

$stmt = $conn->prepare($sql);

// Check if preparation was successful
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Prepare the search term for LIKE queries
$search_like = '%' . $search_term . '%';

// Bind parameters for Giro, Cek, and Loa
$stmt->bind_param(
    "sssssssssssssss",
    $search_like, // For Giro: d.nogiro
    $search_like, // For Giro: dg.nogiro
    $search_like, // For Giro: e.nama_entitas
    $search_like, // For Giro: d.namabank
    $search_like, // For Giro: d.Statusgiro
    $search_like, // For Cek: dc.nocek
    $search_like, // For Cek: e.nama_entitas
    $search_like, // For Cek: d.namabank
    $search_like, // For Loa: dl.noloa
    $search_like, // For Loa: e.nama_entitas
    $search_like, // For Loa: d.namabank
    $search_like, // For Loa: d.namabank
    $search_like, // For Loa: d.namabank
    $search_like, // For Loa: d.namabank
    $search_like, // For Loa: d.namabank
);


// Execute the statement
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Initialize an array to hold records
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
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
    <title>Daftar Giro dan Cek</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        h1 {
            margin: 20px 0;
            font-weight: bold;
            color: #0277bd;
        }

        .container {
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }

        .table thead th {
            background-color: #80deea;
            color: #004d40;
        }

        .table tbody tr:hover {
            background-color: #e1f5fe;
        }

        .input-group input {
            border-radius: 5px;
        }

        .input-group .btn {
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container" style="width: 100%; max-width: 2000px">
        <h1 class="text-center">Daftar Giro dan Cek</h1>

        <!-- Search Form -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro/Cek, Entitas, atau Bank" value="<?php echo htmlspecialchars($search_term); ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </div>

        <table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Entitas</th>
            <th>Jenis</th>
            <th>No Giro/Cek</th>
            <th>Status</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Tanggal Giro/Cek Cair</th>
            <th>Bank</th>
            <th>No. Rekening</th>
            <th>Nominal</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($records)): 
            ?>
            <tr>
                <td colspan="9" class="text-center">Tidak ada data.</td>
            </tr>
        <?php else: 
            // echo '<pre>';
            // print_r($records);
            // echo '</pre>';
            ?>
            <?php foreach ($records as $record): 
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['nama_entitas']); ?></td>
                    <td><?php echo htmlspecialchars($record['jenis'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($record['no'] ?: $record['nocek'] ?: $record['noloa']?:$record['no_detail']?:""); ?></td> <!-- Use 'no' directly -->
                    <td><?php echo htmlspecialchars($record['status'] === 'Unused' ? 'Available' : ($record['stat'] ?: $record['status'] ?: '')); ?></td>
                    <td><?php echo htmlspecialchars($record['tanggal_jatuh_tempo']); ?></td>
                    <td><?php echo htmlspecialchars($record['TglVoid']); ?></td>
                    <td><?php echo htmlspecialchars($record['namabank']); ?></td>
                    <td><?php echo htmlspecialchars($record['ac_number']); ?></td>
                    <td><?php echo number_format($record['total_nominal'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
    </div>
</body>

</html>