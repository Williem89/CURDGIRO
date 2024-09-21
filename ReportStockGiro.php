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
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Laporan Giro Unused per Bank dan Nomor Akun</h1>
    </header>

    <table border="1">
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
                    <td colspan="3">Tidak ada data yang ditemukan.</td>
                </tr>
            <?php 
            else:
                $current_bank = '';
                $subtotal = 0;

                foreach ($results as $row):
                    if ($current_bank !== $row['namabank']): 
                        if ($current_bank !== ''): // Close subtotal row for the previous bank
                        ?>
                            <tr>
                                <td><strong>Subtotal <?php echo htmlspecialchars($current_bank); ?></strong></td>
                                <td></td>
                                <td><strong><?php echo htmlspecialchars($subtotal); ?></strong></td>
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
                    <tr>
                        <td><strong>Subtotal <?php echo htmlspecialchars($current_bank); ?></strong></td>
                        <td></td>
                        <td><strong><?php echo htmlspecialchars($subtotal); ?></strong></td>
                    </tr>
                <?php 
                endif; 
            endif; 
            ?>
        </tbody>
    </table>

    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
