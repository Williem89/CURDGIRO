<?php
include 'koneksi.php'; // Ensure you have the connection file

// Initialize filter variables
$start_date = '';
$end_date = '';
$selected_bank = '';
$selected_ac_number = '';

// Prepare dropdown options
$bank_options = [];
$ac_number_options = [];

// Fetch distinct bank names
$bank_stmt = $conn->prepare("SELECT DISTINCT namabank FROM data_giro");
if ($bank_stmt) {
    $bank_stmt->execute();
    $bank_stmt->bind_result($bank_name);
    while ($bank_stmt->fetch()) {
        $bank_options[] = $bank_name;
    }
    $bank_stmt->close();
}

// Fetch distinct account numbers
$ac_stmt = $conn->prepare("SELECT DISTINCT ac_number FROM data_giro");
if ($ac_stmt) {
    $ac_stmt->execute();
    $ac_stmt->bind_result($ac_number);
    while ($ac_stmt->fetch()) {
        $ac_number_options[] = $ac_number;
    }
    $ac_stmt->close();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get filter inputs
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $selected_bank = filter_input(INPUT_POST, 'selected_bank', FILTER_SANITIZE_STRING);
    $selected_ac_number = filter_input(INPUT_POST, 'selected_ac_number', FILTER_SANITIZE_STRING);

    // Build the SQL query
    $sql = "SELECT dg.nogiro, 
                   dg.namabank, 
                   dg.ac_number, 
                   dg.ac_name, 
                   d.tanggal_giro, 
                   d.tanggal_jatuh_tempo, 
                   d.Nominal, 
                   d.nama_penerima, 
                   d.bank_penerima, 
                   d.ac_penerima, 
                   d.Keterangan
            FROM detail_giro d
            JOIN data_giro dg ON d.nogiro = dg.nogiro
            WHERE d.statgiro = 'Issued'";

    // Append filters to SQL query
    $params = [];
    $types = '';

    if (!empty($start_date) && !empty($end_date)) {
        $sql .= " AND d.tanggal_giro BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    }

    if (!empty($selected_bank)) {
        $sql .= " AND dg.namabank = ?";
        $params[] = $selected_bank;
        $types .= "s";
    }

    if (!empty($selected_ac_number)) {
        $sql .= " AND dg.ac_number = ?";
        $params[] = $selected_ac_number;
        $types .= "s";
    }

    // Prepare statement
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        
        // Execute statement
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            
            // Initialize variables for subtotal calculations
            $current_account = '';
            $current_bank = '';
            $account_subtotal = 0;
            $bank_subtotal = 0;

            if ($result->num_rows > 0) {
                echo "<table>
                        <thead>
                            <tr>
                                <th>No Giro</th>
                                <th>Nama Bank</th>
                                <th>Nomor Akun</th>
                                <th>Nama Akun</th>
                                <th>Tanggal Giro</th>
                                <th>Tanggal Jatuh Tempo</th>
                                <th>Nominal</th>
                                <th>Nama Penerima</th>
                                <th>Bank Penerima</th>
                                <th>Akun Penerima</th>
                                <th>Keterangan</th>
                            </tr>
                          </thead>
                          <tbody>";

                while ($row = $result->fetch_assoc()) {
                    // Start new bank subtotal if bank changes
                    if ($current_bank !== $row['namabank']) {
                        if ($current_bank !== '') {
                            echo "<tr>
                                    <td colspan='6'>Subtotal untuk Bank: " . htmlspecialchars($current_bank) . "</td>
                                    <td><strong>" . number_format($bank_subtotal, 2) . "</strong></td>
                                    <td colspan='4'></td>
                                  </tr>";
                        }
                        $current_bank = $row['namabank'];
                        $bank_subtotal = 0; // Reset bank subtotal
                    }

                    // Start new account subtotal if account number changes
                    if ($current_account !== $row['ac_number']) {
                        if ($current_account !== '') {
                            echo "<tr>
                                    <td colspan='6'>Subtotal untuk Akun: " . htmlspecialchars($current_account) . "</td>
                                    <td><strong>" . number_format($account_subtotal, 2) . "</strong></td>
                                    <td colspan='4'></td>
                                  </tr>";
                        }
                        $current_account = $row['ac_number'];
                        $account_subtotal = 0; // Reset account subtotal
                    }

                    // Display the row data
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nogiro']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['namabank']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ac_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ac_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tanggal_giro']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['tanggal_jatuh_tempo']) . "</td>";
                    echo "<td>" . number_format($row['Nominal'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nama_penerima']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['bank_penerima']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['ac_penerima']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Keterangan']) . "</td>";
                    echo "</tr>";

                    // Update subtotals
                    $account_subtotal += $row['Nominal'];
                    $bank_subtotal += $row['Nominal'];
                }

                // Output the last subtotals
                if ($current_account !== '') {
                    echo "<tr>
                            <td colspan='6'>Subtotal untuk Akun: " . htmlspecialchars($current_account) . "</td>
                            <td><strong>" . number_format($account_subtotal, 2) . "</strong></td>
                            <td colspan='4'></td>
                          </tr>";
                }
                if ($current_bank !== '') {
                    echo "<tr>
                            <td colspan='6'>Subtotal untuk Bank: " . htmlspecialchars($current_bank) . "</td>
                            <td><strong>" . number_format($bank_subtotal, 2) . "</strong></td>
                            <td colspan='4'></td>
                          </tr>";
                }

                echo "</tbody></table>";
            } else {
                echo "Tidak ada data ditemukan.";
            }
        } else {
            echo "Error executing query: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Giro Issued</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        input[type="date"], select {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            font-size: 16px;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h1>Laporan Giro Issued</h1>

    <form method="POST">
        <label for="start_date">Tanggal Mulai:</label>
        <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">

        <label for="end_date">Tanggal Selesai:</label>
        <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">

        <label for="selected_bank">Pilih Bank:</label>
        <select id="selected_bank" name="selected_bank">
            <option value="">Semua Bank</option>
            <?php foreach ($bank_options as $bank): ?>
                <option value="<?php echo htmlspecialchars($bank); ?>" <?php echo ($selected_bank === $bank) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($bank); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="selected_ac_number">Pilih Nomor Akun:</label>
        <select id="selected_ac_number" name="selected_ac_number">
            <option value="">Semua Nomor Akun</option>
            <?php foreach ($ac_number_options as $ac_number): ?>
                <option value="<?php echo htmlspecialchars($ac_number); ?>" <?php echo ($selected_ac_number === $ac_number) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ac_number); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Tampilkan Laporan">
    </form>
</body>
</html>
