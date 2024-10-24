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
        dg.Nominal, 
        dg.tanggal_jatuh_tempo, 
        dg.TglVoid, 
        dg.ac_penerima, 
        dg.nama_penerima, 
        dg.bank_penerima, 
        dg.PVRNo, 
        dg.keterangan, 
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
        AND (d.Statusgiro = 'Unused' OR dg.StatGiro IN ('Issued', 'Posted', 'Void', 'Return', 'Pending Issued', 'Pending Void', 'Pending Return', 'Pending Post')) 
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number, d.Statusgiro, dg.StatGiro, 
        d.nogiro, dg.nogiro, dg.tanggal_jatuh_tempo, dg.TglVoid, 
        dg.ac_penerima, dg.nama_penerima, dg.bank_penerima, dg.PVRNo, dg.keterangan, dg.Nominal

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
        dc.Nominal, 
        dc.tanggal_jatuh_tempo, 
        dc.TglVoid, 
        NULL AS jenis_giro, 
        NULL AS jenis_loa, 
        NULL AS ac_penerima, 
        NULL AS nama_penerima, 
        NULL AS bank_penerima, 
        NULL AS PVRNo, 
        NULL AS keterangan
    FROM 
        data_cek AS d
    LEFT JOIN 
        detail_cek AS dc ON dc.nocek = d.nocek
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (d.nocek LIKE ? OR dc.nocek LIKE ? OR e.nama_entitas LIKE ? 
        OR d.namabank LIKE ? OR d.Statuscek LIKE ?) 
        AND (d.statuscek = 'Unused' OR dc.StatCek IN ('Issued', 'Posted', 'Void', 'Return', 'Pending Issued', 'Pending Void', 'Pending Return', 'Pending Post')) 
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
        dl.Nominal, 
        dl.tanggal_jatuh_tempo, 
        dl.TglVoid, 
        NULL AS jenis_giro, 
        NULL AS jenis_cek, 
        NULL AS ac_penerima, 
        NULL AS nama_penerima, 
        NULL AS bank_penerima, 
        NULL AS PVRNo, 
        NULL AS keterangan
    FROM 
        data_loa AS d
    LEFT JOIN 
        detail_loa AS dl ON dl.noloa = d.noloa
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (d.noloa LIKE ? OR dl.noloa LIKE ? OR e.nama_entitas LIKE ? 
        OR d.namabank LIKE ? OR d.statusloa LIKE ?) 
        AND (d.statusloa = 'Unused' OR dl.StatLoa IN ('Issued', 'Posted', 'Void', 'Return', 'Pending Issued', 'Pending Void', 'Pending Return', 'Pending Post')) 
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number, d.statusloa, dl.StatLoa, 
        d.noloa, dl.noloa, dl.tanggal_jatuh_tempo, dl.TglVoid
";
if (!empty($search_term)) {
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
}

?>

<!-- <pre>
<?php print_r($records); ?>
</pre> -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Giro dan Cek</title>
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
            position: sticky;
            top: 0;
            z-index: 10;
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

        <!-- Records Table -->
         

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th style="width:5px; text-align:center;">No</th>
                    <th style="width:90px;text-align:center;">Tanggal</th>
                    <th style="width:90px; text-align:center;">No</th>
                    <th style="width:90px; text-align:center;">Status</th>
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
            <tbody>
                <?php if (empty($records)):
                ?>
                    <tr>
                        <td colspan="12" class="text-center">Tidak ada data.</td>
                    </tr>
                <?php else:
                    // echo '<pre>';
                    // print_r($records);
                    // echo '</pre>';
                ?>
                    <?php foreach ($records as $record):
                    $no = 1;
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d-M-Y', strtotime($record['tanggal_jatuh_tempo'])); ?></td>
                            <td><?php echo htmlspecialchars($record['no'] ?? $record['nocek'] ?? $record['noloa'] ?? $record['no_detail'] ?? ""); ?></td>
                            <td><?php echo htmlspecialchars($record['status'] === 'Unused' ? 'Available' : ($record['stat'] ?? $record['status'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars($record['ac_number']); ?></td>
                            <td><?php echo htmlspecialchars($record['namabank']); ?></td>
                            <td><?php echo htmlspecialchars($record['ac_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($record['nama_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($record['bank_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($record['PVRNo']); ?></td>
                            <td><?php echo htmlspecialchars($record['keterangan']); ?></td>
                            <td><?php echo 'Rp. ' . number_format($record['Nominal'], 2, ',', '.'); ?></td>

                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>