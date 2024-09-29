<?php
include 'koneksi.php';

// Initialize an empty array to store the due cheques
$due_cheques = [];

// Get the selected month and year or default to current
$selected_month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
$selected_year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');

// Query for fetching the cheques due in the selected month and year
$sql = "SELECT d.namabank, d.ac_name, dg.nogiro, SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo 
        FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        WHERE dg.StatGiro = 'Issued' 
        AND MONTH(dg.tanggal_jatuh_tempo) = $selected_month 
        AND YEAR(dg.tanggal_jatuh_tempo) = $selected_year
        GROUP BY dg.tanggal_jatuh_tempo, d.namabank, d.ac_name, dg.nogiro
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
$grand_total = 0;
$current_date = null;
$bank_data = [];

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
            background-color: #f4f7fa;
            padding: 30px;
            font-family: Arial, sans-serif;
        }
        header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 0.9em;
            color: #6c757d;
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .btn-back {
            margin-top: 20px;
            display: inline-block;
            background-color: #0056b3;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #004494;
        }
    </style>
</head>
<body>
    <header>
        <h1>Giro Jatuh Tempo <?php echo htmlspecialchars(date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year))); ?></h1>
    </header>

    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <select name="month" class="form-select">
                    <?php
                    // Get current month
                    $current_month = date('n');
                    for ($m = 1; $m <= 12; $m++) {
                        echo '<option value="' . $m . '"' . ($current_month == $m ? ' selected' : '') . '>' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <select name="year" class="form-select">
                    <?php
                    // Get current year
                    $current_year = date('Y');
                    for ($y = $current_year - 5; $y <= $current_year + 5; $y++) {
                        echo '<option value="' . $y . '"' . ($current_year == $y ? ' selected' : '') . '>' . $y . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Tampilkan</button>
    </form>

    <div class="container table-container mt-4">
        <h2 class="mb-4">Daftar Giro yang Jatuh Tempo</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>No. Giro</th>
                        <th>Pemegang</th>
                        <th>Bank</th>
                        <th>Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($due_cheques) > 0): ?>
                    <?php
                    // Organize data by date and bank
                    foreach ($due_cheques as $cheque) {
                        $date_key = $cheque['tanggal_jatuh_tempo'];
                        $bank_key = $cheque['namabank'];

                        if (!isset($bank_data[$date_key][$bank_key])) {
                            $bank_data[$date_key][$bank_key] = [
                                'entries' => [],
                                'subtotal' => 0,
                            ];
                        }

                        $bank_data[$date_key][$bank_key]['entries'][] = $cheque;
                        $bank_data[$date_key][$bank_key]['subtotal'] += $cheque['total_nominal'];
                    }

                    // Render the organized data
                    foreach ($bank_data as $date => $banks): ?>
                        <tr>
                            <td colspan="4" class="text-left section-title"><h4><?php echo htmlspecialchars(date('d-m-Y', strtotime($date))); ?></h4></td>
                        </tr>
                        <?php 
                        $date_total = 0; // Initialize date total
                        foreach ($banks as $bank_name => $bank_info): ?>
                            <tr>
                                <td colspan="4" class="text-left section-title"><strong><?php echo htmlspecialchars($bank_name); ?></strong></td>
                            </tr>
                            <?php foreach ($bank_info['entries'] as $cheque): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cheque['nogiro']); ?></td>
                                    <td><?php echo htmlspecialchars($cheque['ac_name']); ?></td>
                                    <td class="text-left"><?php echo htmlspecialchars($cheque['namabank']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format($cheque['total_nominal'], 2)); ?></td>
                                </tr>
                                <?php $date_total += $cheque['total_nominal']; // Add to date total ?>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="3" class="text-end subtotal"><strong>Subtotal untuk <?php echo htmlspecialchars($bank_name); ?>:</strong></td>
                                <td class="subtotal"><strong><?php echo htmlspecialchars(number_format($bank_info['subtotal'], 2)); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="3" class="text-end subtotal"><strong>Subtotal untuk <?php echo htmlspecialchars(date('d-m-Y', strtotime($date))); ?>:</strong></td>
                            <td class="subtotal"><strong><?php echo htmlspecialchars(number_format($date_total, 2)); ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada giro yang jatuh tempo bulan ini.</td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="3" class="text-end subtotal"><strong>Grand Total:</strong></td>
                    <td class="subtotal">
                        <strong>
                            <?php 
                            // Initialize grand total
                            $grand_total = 0; 
                            // Loop through bank data to sum subtotals
                            foreach ($bank_data as $banks) {
                                foreach ($banks as $bank_info) {
                                    $grand_total += $bank_info['subtotal'];
                                }
                            }
                            // Output formatted grand total
                            echo htmlspecialchars(number_format($grand_total, 2)); 
                            ?>
                        </strong>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <a href="dashboard.php" class="btn-back">Kembali</a>              
    </div>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Aplikasi Giro. All rights reserved.</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
