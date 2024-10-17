<?php
include 'koneksi.php';

// Initialize results array
$results = [];

// Query to get unused giro data grouped by bank and account number
$sql = "
    SELECT namabank, ac_number, COUNT(*) AS unused_count
    FROM data_giro
    WHERE statusgiro = 'Unused'
    GROUP BY namabank, ac_number
    ORDER BY namabank, ac_number
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Giro Unused</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
        }

        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%; /* Ensures vertical centering */
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ced4da;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .subtotal-row {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .no-data {
            text-align: center;
            color: #dc3545;
            font-weight: bold;
        }

        .back-button {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px; /* Optional padding inside card */
        }
    </style>
</head>
<body>
    <header>
        <h1>Laporan Giro Unused per Bank dan Nomor Akun</h1>
    </header>

    <div class="container">
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Bank</th>
                    <th>Nomor Akun</th>
                    <th>Jumlah Giro Unused</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (empty($results)): ?>
                    <tr>
                        <td colspan="3" class="no-data">Tidak ada data yang ditemukan.</td>
                    </tr>
                <?php 
                else:
                    $current_bank = '';
                    $subtotal = 0;

                    foreach ($results as $row):
                        if ($current_bank !== $row['namabank']): 
                            if ($current_bank !== ''): // Close subtotal row for the previous bank
                            ?>
                                <tr class="subtotal-row">
                                    <td>Subtotal <?php echo htmlspecialchars($current_bank); ?></td>
                                    <td></td>
                                    <td><?php echo htmlspecialchars($subtotal); ?></td>
                                </tr>
                            <?php 
                            endif;
                            // Reset subtotal for new bank
                            $current_bank = $row['namabank'];
                            $subtotal = 0;
                        endif;

                        $subtotal += $row['unused_count']; // Add to subtotal
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($row['ac_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['unused_count']); ?></td>
                    </tr>
                <?php 
                    endforeach; 
                    // Print the last subtotal
                    if ($current_bank !== ''): ?>
                        <tr class="subtotal-row">
                            <td>Subtotal <?php echo htmlspecialchars($current_bank); ?></td>
                            <td></td>
                            <td><?php echo htmlspecialchars($subtotal); ?></td>
                        </tr>
                    <?php 
                    endif; 
                endif; 
                ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-button">Kembali ke Halaman Utama</a>
    </div>
            </div>
</body>
</html>
