<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Query with Filters</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    


    $(document).ready(function() {
        $('#entitas').change(function() {
            var entitasId = $(this).val();
            $('#namabank').html('<option value="">Select Nama Bank</option>');
            $('#ac_number').html('<option value="">Select No. Rekening</option>');
            if (entitasId) {
                $.ajax({
                    type: 'POST',
                    url: 'fetch_namabank.php',
                    data: { id_entitas: entitasId },
                    success: function(response) {
                        $('#namabank').html(response);
                    }
                });
            }
        });

        $('#namabank').change(function() {
            var namabank = $(this).val();
            var entitasId = $('#entitas').val();
            $('#ac_number').html('<option value="">Select No. Rekening</option>');
            if (namabank && entitasId) {
                $.ajax({
                    type: 'POST',
                    url: 'fetch_acnumber.php',
                    data: { namabank: namabank, id_entitas: entitasId },
                    success: function(response) {
                        $('#ac_number').html(response);
                    }
                });
            }
        });

        $('#ac_number').change(function() {
            var acNumber = $(this).val();
            if (acNumber) {
                $.ajax({
                    type: 'POST',
                    url: 'fetch_saldo.php',
                    data: { ac_number: acNumber },
                    success: function(response) {
                        $('#saldo_awal').html(response);
                    }
                });
            } else {
                $('#saldo_awal').html('');
            }
        });
    });
    </script>
</head>
<body>

<h1>Query Data by Date Range and Filters</h1>
<form method="post" action="" id="filter-form">
    <label for="start_date">Start Date:</label>
    <input type="date" id="start_date" name="start_date" required>
    
    <label for="end_date">End Date:</label>
    <input type="date" id="end_date" name="end_date" required>
    
    <label for="entitas">Entitas:</label>
    <select id="entitas" name="entitas" required>
        <option value="">Select Entitas</option>
        <?php
        include 'koneksi.php';
        $result = $conn->query("SELECT id_entitas, nama_entitas FROM list_entitas");
        while ($row = $result->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($row['id_entitas']) . "'>" . htmlspecialchars($row['nama_entitas']) . "</option>";
        }
        ?>
    </select>

    <label for="namabank">Nama Bank:</label>
    <select id="namabank" name="namabank" required>
        <option value="">Select Nama Bank</option>
    </select>

    <label for="ac_number">No. Rekening:</label>
    <select id="ac_number" name="ac_number" required>
        <option value="">Select No. Rekening</option>
    </select>

    <div id="saldo_awal" style="margin-top: 10px;"></div>
    
    <input type="submit" value="Submit">
</form>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get selected filter values
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);
    $entitas = filter_input(INPUT_POST, 'entitas', FILTER_SANITIZE_STRING);
    $namabank = filter_input(INPUT_POST, 'namabank', FILTER_SANITIZE_STRING);
    $ac_number = filter_input(INPUT_POST, 'ac_number', FILTER_SANITIZE_STRING);

    // Get saldo awal for the specific ac_number before the start date
    $initial_balance = 0; // Assume saldo awal starts at 0
    $total_before_start = 0;

    if ($ac_number) {
        $saldo_sql = "
            SELECT 
                saldo_awal AS saldo_awal
            FROM 
                your_saldo_table 
            WHERE 
                ac_number = ?"; // Replace with your actual saldo table and field

        $saldo_stmt = $conn->prepare($saldo_sql);
        $saldo_stmt->bind_param('s', $ac_number);
        
        if ($saldo_stmt->execute()) {
            $saldo_result = $saldo_stmt->get_result();
            if ($saldo_row = $saldo_result->fetch_assoc()) {
                $initial_balance = $saldo_row['saldo_awal'] ?? 0;
            }
        } else {
            echo "Error executing saldo query: " . $saldo_stmt->error;
        }

        // Get total nominal before the start date
        $nominal_sql = "
            SELECT 
                SUM(dg.Nominal) AS total_before_start
            FROM 
                detail_giro AS dg
            INNER JOIN 
                data_giro AS d ON dg.nogiro = d.nogiro
            WHERE 
                dg.StatGiro = 'Posted' AND
                d.ac_number = ? AND 
                dg.tanggal_jatuh_tempo < ?";

        $nominal_stmt = $conn->prepare($nominal_sql);
        $nominal_stmt->bind_param('ss', $ac_number, $start_date);
        
        if ($nominal_stmt->execute()) {
            $nominal_result = $nominal_stmt->get_result();
            if ($nominal_row = $nominal_result->fetch_assoc()) {
                $total_before_start = $nominal_row['total_before_start'] ?? 0;
            }
        } else {
            echo "Error executing nominal query: " . $nominal_stmt->error;
        }

        // Calculate saldo
        $saldo = $initial_balance - $total_before_start;
        echo "<div>Saldo: " . htmlspecialchars($saldo) . "</div>";

        $nominal_stmt->close();
        $saldo_stmt->close();
    }

    // Prepare the main SQL query
    $sql = "
        SELECT 
            'Giro' AS jenis,
            e.nama_entitas,
            d.namabank,
            d.ac_number,
            dg.StatGiro AS status,
            dg.nogiro AS nomor,
            SUM(dg.Nominal) AS total_nominal,
            dg.tanggal_jatuh_tempo,
            dg.TglVoid,
            dg.image_giro
        FROM 
            detail_giro AS dg
        INNER JOIN 
            data_giro AS d ON dg.nogiro = d.nogiro
        INNER JOIN 
            list_entitas AS e ON d.id_entitas = e.id_entitas
        WHERE 
            dg.StatGiro = 'Posted' AND
            dg.tanggal_jatuh_tempo BETWEEN ? AND ?";

    // Adding filters if they are selected
    if ($entitas) {
        $sql .= " AND e.id_entitas = ?";
    }
    if ($namabank) {
        $sql .= " AND d.namabank = ?";
    }
    if ($ac_number) {
        $sql .= " AND d.ac_number = ?";
    }

    $sql .= " GROUP BY 
                e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.tanggal_jatuh_tempo, dg.TglVoid, dg.image_giro

        UNION ALL

        SELECT 
            'Cek' AS jenis,
            e.nama_entitas,
            c.namabank,
            c.ac_number,
            dc.StatCek AS status,
            dc.nocek AS nomor,
            SUM(dc.nominal) AS total_nominal,
            dc.tanggal_jatuh_tempo,
            dc.TglVoid,
            dc.image_giro
        FROM 
            detail_cek AS dc
        INNER JOIN 
            data_cek AS c ON dc.nocek = c.nocek
        INNER JOIN 
            list_entitas AS e ON c.id_entitas = e.id_entitas
        WHERE 
            dc.StatCek = 'Posted' AND
            dc.tanggal_jatuh_tempo BETWEEN ? AND ?";

    // Adding filters for Cek
    if ($entitas) {
        $sql .= " AND e.id_entitas = ?";
    }
    if ($namabank) {
        $sql .= " AND c.namabank = ?";
    }
    if ($ac_number) {
        $sql .= " AND c.ac_number = ?";
    }

    $sql .= " GROUP BY 
                e.nama_entitas, c.namabank, c.ac_number, dc.nocek, dc.tanggal_jatuh_tempo, dc.TglVoid, dc.image_giro
        ORDER BY 
            tanggal_jatuh_tempo ASC;";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    
    // Create an array for binding parameters
    $params = [$start_date, $end_date];
    
    // Add filters to the parameters array
    if ($entitas) {
        $params[] = $entitas;
    }
    if ($namabank) {
        $params[] = $namabank;
    }
    if ($ac_number) {
        $params[] = $ac_number;
    }
    
    // Add the same parameters for the second part of the UNION
    $params[] = $start_date;
    $params[] = $end_date;
    
    if ($entitas) {
        $params[] = $entitas;
    }
    if ($namabank) {
        $params[] = $namabank;
    }
    if ($ac_number) {
        $params[] = $ac_number;
    }

    // Create the type string for bind_param
    $types = str_repeat('s', count($params)); // 's' for string type
    $stmt->bind_param($types, ...$params); // Use the spread operator

    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        // Display results
        if ($result->num_rows > 0) {
            echo "<table>
                    <tr>
                        <th>Jenis</th>
                        <th>Nama Entitas</th>
                        <th>Nama Bank</th>
                        <th>No. Rekening</th>
                        <th>Status</th>
                        <th>Nomor</th>
                        <th>Total Nominal</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Tgl Void</th>
                        <th>Image Giro</th>
                    </tr>";
            
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['jenis']) . "</td>
                        <td>" . htmlspecialchars($row['nama_entitas']) . "</td>
                        <td>" . htmlspecialchars($row['namabank']) . "</td>
                        <td>" . htmlspecialchars($row['ac_number']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                        <td>" . htmlspecialchars($row['nomor']) . "</td>
                        <td>" . htmlspecialchars($row['total_nominal']) . "</td>
                        <td>" . htmlspecialchars($row['tanggal_jatuh_tempo']) . "</td>
                        <td>" . htmlspecialchars($row['TglVoid']) . "</td>
                        <td><img src='" . htmlspecialchars($row['image_giro']) . "' alt='Image' width='50'></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "No data found.";
        }
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
</body>
</html>
