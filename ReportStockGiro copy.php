<?php
include 'koneksi.php';

$filter_status = '';
$start_date = '';
$end_date = '';
$results = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $filter_status = $_POST['filter_status'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "SELECT dg.*, dgiro.* 
            FROM data_giro dg 
            JOIN detail_giro dgiro ON dg.id = dgiro.id 
            WHERE 1=1";

    // Add filters
    if (!empty($filter_status)) {
        $sql .= " AND dg.statusgiro = ?";
    }
    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND dgiro.tanggal_jatuh_tempo BETWEEN ? AND ?";
    }

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $params = [];
    
    if (!empty($filter_status)) {
        $params[] = $filter_status;
    }
    if (!empty($start_date) && !empty($end_date)) {
        $params[] = $start_date;
        $params[] = $end_date;
    }

    // Bind parameters and execute
    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }
    
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();

    // Fetch results
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    $stmt->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Laporan Giro</h1>
    </header>

    <form method="POST">
        <label for="filter_status">Status Giro:</label>
        <select name="filter_status" id="filter_status">
            <option value="">Semua</option>
            <option value="Unused" <?php echo $filter_status == 'Unused' ? 'selected' : ''; ?>>Unused</option>
            <option value="Issued" <?php echo $filter_status == 'Issued' ? 'selected' : ''; ?>>Issued</option>
        </select>

        <label for="start_date">Tanggal Mulai:</label>
        <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>">

        <label for="end_date">Tanggal Akhir:</label>
        <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>">

        <input type="submit" value="Tampilkan Laporan">
    </form>

    <section>
        <h2>Hasil Laporan</h2>
        <?php if ($results): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID Giro</th>
                        <th>Status Giro</th>
                        <th>Jumlah</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <!-- Add more columns as necessary -->
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['statusgiro']); ?></td>
                            <td><?php echo htmlspecialchars($row['jumlah']); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_jatuh_tempo']); ?></td>
                            <!-- Add more fields as necessary -->
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Tidak ada data yang ditemukan.</p>
        <?php endif; ?>
    </section>
</body>
</html>
