<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Query with Filters</title>
    <style>
        table {
            margin: 20px auto;
            /* Centers the table and adds top margin */
            max-width: 1900px;
            /* Set the maximum width of the table */
            border: 1px solid #dee2e6;
            font-size: 15px;
            border-collapse: collapse;
            /* Optional: for cleaner borders */
            width: 100%;
            /* Makes the table responsive */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Adds shadow */
            border-radius: 4px;
            /* Optional: for rounded corners */
        }

        th,
        td {
            padding: 8px;
            /* Add padding for better spacing */
            text-align: left;
            /* Align text as desired */
        }

        th {
            background-color: #f8f9fa;
            /* Optional: header background */
            border-bottom: 2px solid #dee2e6;
            /* Optional: header border */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
            /* Optional: zebra striping */
        }

        .header {
            display: flex;
            align-items: center;
            padding: 20px;
            text-transform: uppercase;
            /* Menjadikan semua teks uppercase */
            font-family: "Roboto Slab", serif;
        }


        .header a.btn {
            margin: 20px;
            /* Jarak antara tombol dan judul */
            padding: 10px 15px;
            /* Padding tombol */
            transition: background-color 0.3s;
            /* Transisi pada hover */
            border-radius: 50px;
            /* Sudut membulat */
            width: 130px;
            /* Lebar tombol */
        }

        .header h1 {
            flex: 0.9;
            /* Mengambil ruang yang tersedia */
            text-align: center;
            /* Memusatkan teks */
            margin: 0;
            /* Menghapus margin default */
            line-height: 1.6;
            /* Mengatur jarak antar baris */
        }

        #filter-form {
            background-color: white;
            /* Form background */
            /* Rounded corners */
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.1);
            /* Shadow effect */
            padding: 20px;
            /* Padding inside the form */
            width: 2000px;
            /* Fixed width */
        }

        label {
            display: block;
            /* Labels on their own lines */
            margin-bottom: 5px;
            /* Space between label and input */
            font-weight: bold;
            /* Bold labels */
        }

        input[type="date"],
        select {
            width: 100%;
            /* Full width inputs */
            padding: 8px;
            /* Padding for inputs */
            margin-bottom: 15px;
            /* Space between inputs */
            border: 1px solid #ced4da;
            /* Input border */
            border-radius: 4px;
            /* Rounded corners */
            font-size: 14px;
            /* Font size */
        }

        input[type="submit"] {
            background-color: #007bff;
            /* Button color */
            color: white;
            /* Text color */
            border: none;
            /* Remove border */
            border-radius: 4px;
            /* Rounded corners */
            padding: 10px;
            /* Padding for button */
            cursor: pointer;
            /* Pointer cursor on hover */
            font-size: 16px;
            /* Font size */
            transition: background-color 0.3s;
            /* Transition effect */
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
            /* Darker color on hover */
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
                        data: {
                            id_entitas: entitasId
                        },
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
                        data: {
                            namabank: namabank,
                            id_entitas: entitasId
                        },
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
                        data: {
                            ac_number: acNumber
                        },
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body>

    <div class="header d-flex align-items-center" style="padding-top: 10px;">
        <a class="btn btn-primary d-flex align-items-center" href="/CurdGiro/dashboard.php#cek" style="margin-right: 20px; transition: background-color 0.3s;width: 120px;">
            <i class="bi bi-backspace" style="margin-right: 8px;"></i>
            Kembali
        </a>
        <h1 class="mb-0" style="line-height: 1; margin: 0;">Query Data by Date Range and Filters</h1>
    </div>

    <form method="post" action="" id="filter-form" class="d-flex flex-wrap justify-content-between">
        <div class="form-group" style="flex: 1; min-width: 200px; margin-right: 10px;">
            <label for="start_date">Start Date:</label>
            <input type="date" id="start_date" name="start_date" required>
        </div>

        <div class="form-group" style="flex: 1; min-width: 200px; margin-right: 10px;">
            <label for="end_date">End Date:</label>
            <input type="date" id="end_date" name="end_date" required>
        </div>

        <div class="form-group" style="flex: 1; min-width: 200px; margin-right: 10px;">
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
        </div>

        <div class="form-group" style="flex: 1; min-width: 200px; margin-right: 10px;">
            <label for="namabank">Nama Bank:</label>
            <select id="namabank" name="namabank" required>
                <option value="">Select Nama Bank</option>
            </select>
        </div>

        <div class="form-group" style="flex: 1; min-width: 200px; margin-right: 10px;">
            <label for="ac_number">No. Rekening:</label>
            <select id="ac_number" name="ac_number" required>
                <option value="">Select No. Rekening</option>
            </select>
        </div>

        <div id="saldo_awal" style="flex: 1; min-width: 200px; margin-top: 10px;"></div>

        <div class="form-group" style="flex: 1; min-width: 200px; margin-top: 10px;">
            <input type="submit" value="Submit" class="btn btn-primary">
        </div>
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
                saldoawal AS saldo_awal
            FROM 
                list_rekening
            WHERE 
                no_akun = ?"; // Replace with your actual saldo table and field

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
            dg.tanggal_cair_giro >= '2000-01-01' AND
            dg.tanggal_cair_giro < ?";

            $cek_nominal_sql = "
            SELECT 
            SUM(dc.Nominal) AS total_before_start
            FROM 
            detail_cek AS dc
            INNER JOIN 
            data_cek AS c ON dc.nocek = c.nocek
            WHERE 
            dc.StatCek = 'Posted' AND
            c.ac_number = ? AND 
            dc.tanggal_cair_cek >= '2000-01-01' AND
            dc.tanggal_cair_cek < ?";

            $cek_nominal_stmt = $conn->prepare($cek_nominal_sql);
            $cek_nominal_stmt->bind_param('ss', $ac_number, $start_date);

            if ($cek_nominal_stmt->execute()) {
                $cek_nominal_result = $cek_nominal_stmt->get_result();
                if ($cek_nominal_row = $cek_nominal_result->fetch_assoc()) {
                    $total_before_start += $cek_nominal_row['total_before_start'] ?? 0;
                }
            } else {
                echo "Error executing cek nominal query: " . htmlspecialchars($cek_nominal_stmt->error) . "<br>";
            }

            $nominal_stmt = $conn->prepare($nominal_sql);
            $nominal_stmt->bind_param('ss', $ac_number, $start_date);

            if ($nominal_stmt->execute()) {
                $nominal_result = $nominal_stmt->get_result();
                if ($nominal_row = $nominal_result->fetch_assoc()) {
                    $total_before_start += $nominal_row['total_before_start'] ?? 0;
                }
            } else {
                echo "Error executing nominal query: " . htmlspecialchars($nominal_stmt->error) . "<br>";
            }

            // Calculate saldo
            // var_dump($initial_balance, $total_before_start, );
            $saldo = $initial_balance - $total_before_start;
            // echo "<div>Saldo: " . htmlspecialchars($saldo) . "</div>";

            $cek_nominal_stmt->close();
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
        dg.tanggal_cair_giro AS tanggal_cair,
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
        dg.tanggal_cair_giro BETWEEN ? AND ?";

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
        e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.tanggal_cair_giro, dg.TglVoid, dg.image_giro

    UNION ALL

    SELECT 
        'Cek' AS jenis,
        e.nama_entitas,
        c.namabank,
        c.ac_number,
        dc.StatCek AS status,
        dc.nocek AS nomor,
        SUM(dc.Nominal) AS total_nominal,
        dc.tanggal_cair_cek AS tanggal_cair,
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
        dc.tanggal_cair_cek BETWEEN ? AND ?";

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
        e.nama_entitas, c.namabank, c.ac_number, dc.nocek, dc.tanggal_cair_cek, dc.TglVoid, dc.image_giro
    ORDER BY 
        tanggal_cair ASC;";

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
                        <th>Tanggal Cair Giro</th>
                        <th>Tgl Void</th>
                        <th>Image Giro</th>
                    </tr>";

                $subtotal = 0;
                while ($row = $result->fetch_assoc()) {
                    $subtotal += $row['total_nominal'];
                    echo "<tr>
                        <td>" . htmlspecialchars($row['jenis']) . "</td>
                        <td>" . htmlspecialchars($row['nama_entitas']) . "</td>
                        <td>" . htmlspecialchars($row['namabank']) . "</td>
                        <td>" . htmlspecialchars($row['ac_number']) . "</td>
                        <td>" . htmlspecialchars($row['status']) . "</td>
                        <td>" . htmlspecialchars($row['nomor']) . "</td>
                        <td>Rp. " . htmlspecialchars(number_format($row['total_nominal'], 2, ',', '.')) . "</td>
                        <td>" . htmlspecialchars($row['tanggal_cair']) . "</td>
                        <td>" . htmlspecialchars($row['TglVoid']) . "</td>
                        <td><img src='" . htmlspecialchars($row['image_giro']) . "' alt='Image' width='50'></td>
                      </tr>";
                }
                echo "<tr>
                    <td colspan='9' style='text-align:right;'><strong>Saldo Awal:</strong></td>
                    <td>Rp. " . htmlspecialchars(number_format($initial_balance, 2, ',', '.')) . "</td>
                  </tr>";
                echo "<tr>
                    <td colspan='9' style='text-align:right;'><strong>Saldo :</strong></td>
                    <td>Rp. " . htmlspecialchars(number_format($saldo, 2, ',', '.')) . "</td>
                  </tr>";
                echo "<tr>
                    <td colspan='6' style='text-align:right;'><strong>Subtotal:</strong></td>
                    <td>Rp. " . htmlspecialchars(number_format($subtotal, 2, ',', '.')) . "</td>
                    <td colspan='2' style='text-align:right;'><strong>Saldo Akhir:</strong></td>
                    <td>Rp. " . htmlspecialchars(number_format($saldo - $subtotal, 2, ',', '.')) . "</td>
                  </tr>";
                echo "</table>";
            } else {
                echo "No data found.";
                echo "<td colspan='6' style='text-align:right;'><strong>Saldo Akhir:</strong></td>
                    <td>" . htmlspecialchars($saldo - $subtotal) . "</td>
                    <td colspan='3'></td>";
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