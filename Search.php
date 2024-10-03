<?php
include 'koneksi.php';
session_start();

// Get the selected month and year from the GET request, or set default values
$selected_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n'); // Default to current month
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y'); // Default to current year

// Get the search term from the GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$option_type = isset($_GET['option']) ? $_GET['option'] : 'giro'; // Default to 'giro'

// Prepare the SQL statement based on selected option
if ($option_type === 'cek') {
    $sql = "SELECT e.nama_entitas, d.namabank, d.ac_number, dc.StatCek, dc.nocek, 
                SUM(dc.Nominal) AS total_nominal, dc.tanggal_jatuh_tempo, dc.TglVoid 
            FROM detail_cek AS dc
            INNER JOIN data_cek AS d ON dc.nocek = d.nocek
            INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
            WHERE MONTH(dc.tanggal_jatuh_tempo) = ? 
            AND YEAR(dc.tanggal_jatuh_tempo) = ? 
            AND (dc.nocek LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
            GROUP BY dc.tanggal_jatuh_tempo, e.nama_entitas, d.namabank, d.ac_number, dc.nocek, dc.TglVoid
            ORDER BY dc.tanggal_jatuh_tempo ASC;";
} else {
    $sql = "SELECT e.nama_entitas, d.namabank, d.ac_number, dg.StatGiro, dg.nogiro, 
                SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.TglVoid 
            FROM detail_giro AS dg
            INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
            INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
            WHERE MONTH(dg.tanggal_jatuh_tempo) = ? 
            AND YEAR(dg.tanggal_jatuh_tempo) = ? 
            AND (dg.nogiro LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?) 
            GROUP BY dg.tanggal_jatuh_tempo, e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.TglVoid
            ORDER BY dg.tanggal_jatuh_tempo ASC;";
}

$stmt = $conn->prepare($sql);

// Check if preparation was successful
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Bind parameters
$search_like = '%' . $search_term . '%';
$stmt->bind_param("iisss", $selected_month, $selected_year, $search_like, $search_like, $search_like);

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
</head>
<body>
<div class="container">
    <h1 class="text-center">Daftar Giro dan Cek</h1>
    
    <!-- Search Form -->
    <form method="GET" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro/Cek, Entitas, atau Bank" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
        </div>
        <div class="row mb-3">
            <div class="col">
                <select name="option" class="form-select">
                    <option value="giro" <?php echo ($option_type === 'giro') ? 'selected' : ''; ?>>Giro</option>
                    <option value="cek" <?php echo ($option_type === 'cek') ? 'selected' : ''; ?>>Cek</option>
                </select>
            </div>
            <div class="col">
                <select name="month" class="form-select">
                    <option value="">Pilih Bulan</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>
                            <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col">
                <select name="year" class="form-select">
                    <option value="">Pilih Tahun</option>
                    <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $selected_year) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Entitas</th>
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
                    <td><?php echo htmlspecialchars($option_type === 'cek' ? $record['nocek'] : $record['nogiro']); ?></td>
                    <td><?php echo htmlspecialchars($option_type === 'cek' ? $record['StatCek'] : $record['StatGiro']); ?></td>
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
