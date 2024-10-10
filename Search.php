<?php
include 'koneksi.php';
session_start();

// Get the search term from the GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the SQL statement for both Giro and Cek
$sql = "
    SELECT d.jenis_giro, e.nama_entitas, d.namabank, d.ac_number, dg.StatGiro, dg.nogiro, 
           NULL AS nocek, NULL AS StatCek, 
           SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.TglVoid 
    FROM detail_giro AS dg
    INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE (dg.nogiro LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
    GROUP BY dg.tanggal_jatuh_tempo, d.jenis_giro, e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.TglVoid
    
    UNION ALL
    
    SELECT d.jenis_cek, e.nama_entitas, d.namabank, d.ac_number, NULL AS StatGiro, NULL AS nogiro, 
           dc.nocek, dc.StatCek, 
           SUM(dc.Nominal) AS total_nominal, dc.tanggal_jatuh_tempo, dc.TglVoid 
    FROM detail_cek AS dc
    INNER JOIN data_cek AS d ON dc.nocek = d.nocek
    INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE (dc.nocek LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
    GROUP BY dc.tanggal_jatuh_tempo, d.jenis_cek, e.nama_entitas, d.namabank, d.ac_number, dc.nocek, dc.TglVoid
    UNION ALL
    
    SELECT d.jenis_loa, e.nama_entitas, d.namabank, d.ac_number, NULL AS StatGiro, NULL AS nogiro, 
           dl.noloa, dl.StatLoa, 
           SUM(dl.Nominal) AS total_nominal, dl.tanggal_jatuh_tempo, dl.TglVoid 
    FROM detail_loa AS dl
    INNER JOIN data_loa AS d ON dl.noloa = d.noloa
    INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE (dl.noloa LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
    GROUP BY dl.tanggal_jatuh_tempo, d.jenis_loa, e.nama_entitas, d.namabank, d.ac_number, dl.noloa, dl.TglVoid
    ORDER BY tanggal_jatuh_tempo ASC;
";

$stmt = $conn->prepare($sql);

// Check if preparation was successful
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Bind parameters for Giro
$search_like = '%' . $search_term . '%';
$stmt->bind_param("sssssssss", $search_like, $search_like, $search_like, $search_like, $search_like, $search_like, $search_like, $search_like, $search_like);

// Execute the statement
$stmt->execute();
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
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro/Cek, Entitas, atau Bank" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
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
        <?php if (empty($records)): ?>
            <tr>
                <td colspan="8" class="text-center">Tidak ada data.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['nama_entitas']); ?></td>
                    <td><?php echo htmlspecialchars($record['jenis_giro']); ?></td>
                    <td><?php echo htmlspecialchars($record['nogiro'] ?: $record['nocek']); ?></td>
                    <td><?php echo htmlspecialchars($record['StatGiro'] ?: $record['StatCek']); ?></td>
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
