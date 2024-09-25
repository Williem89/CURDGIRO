<?php
include 'koneksi.php';

// Initialize an empty array to store the due cheques
$due_cheques = [];

// Query for fetching the cheques due this month along with account names, grouped by date
$sql = "SELECT d.ac_name, dg.nogiro, SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo 
        FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        WHERE dg.StatGiro = 'Issued' 
        AND MONTH(dg.tanggal_jatuh_tempo) = MONTH(NOW()) 
        AND YEAR(dg.tanggal_jatuh_tempo) = YEAR(NOW())
        GROUP BY dg.tanggal_jatuh_tempo, d.ac_name, dg.nogiro
        ORDER BY dg.tanggal_jatuh_tempo ASC;";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $due_cheques[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();

// Initialize variables for subtotals and grand total
$subtotal = 0;
$grand_total = 0;
$current_date = null;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giro Jatuh Tempo Bulan Ini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e9f7fa;
            padding: 20px;
        }
        header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header>
        <h1>Giro Jatuh Tempo Bulan Ini</h1>
    </header>

    <div class="container mt-4">
        <h2 class="mb-4">Daftar Giro yang Jatuh Tempo</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>No. Giro</th>
                        <th>Pemegang</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($due_cheques) > 0): ?>
                        <?php foreach ($due_cheques as $cheque): ?>
                            <?php
                            // Check if we have a new date
                            if ($current_date !== $cheque['tanggal_jatuh_tempo']) {
                                // If it's not the first date, output the subtotal row
                                if ($current_date !== null) {
                                    echo "<tr>
                                        <td colspan='3' class='text-end'><strong>Subtotal untuk $current_date:</strong></td>
                                        <td><strong>" . htmlspecialchars(number_format($subtotal, 2)) . "</strong></td>
                                      </tr>";
                                    $grand_total += $subtotal; // Add to grand total
                                }
                                // Update current date and reset subtotal
                                $current_date = $cheque['tanggal_jatuh_tempo'];
                                $subtotal = 0; // Reset subtotal for new date
                                echo "<tr><td colspan='4'><h3 class='text-center'>Tanggal: " . htmlspecialchars(date('Y-m-d', strtotime($current_date))) . "</h3></td></tr>";
                            }
                            // Add to subtotal for the current date
                            $subtotal += $cheque['total_nominal'];
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cheque['nogiro']); ?></td>
                                <td><?php echo htmlspecialchars($cheque['ac_name']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($cheque['tanggal_jatuh_tempo']))); ?></td>
                                <td><?php echo htmlspecialchars(number_format($cheque['total_nominal'], 2)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Output the last subtotal -->
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal untuk <?php echo htmlspecialchars($current_date); ?>:</strong></td>
                            <td><strong><?php echo htmlspecialchars(number_format($subtotal, 2)); ?></strong></td>
                        </tr>
                        <?php $grand_total += $subtotal; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada giro yang jatuh tempo bulan ini.</td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Grand Total:</strong></td>
                        <td><strong><?php echo htmlspecialchars(number_format($grand_total, 2)); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Aplikasi Giro. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
