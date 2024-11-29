<?php
include 'koneksi.php';

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Koneksi gagal. Silakan coba lagi.");
}

// Query to retrieve all accounts
$sql_saldo = "
SELECT 
    lr.no_akun, 
    lr.nama_bank, 
    lr.nama_akun, 
    lr.saldo,
    lr.updtgl,
    lr.id_entitas, 
    le.nama_entitas 
FROM 
    list_rekening AS lr
INNER JOIN 
    list_entitas AS le 
ON 
    lr.id_entitas = le.id_entitas
";
$result_saldo = $conn->query($sql_saldo);

// Check for query execution errors
if (!$result_saldo) {
    error_log("Query failed: " . $conn->error);
    die("Terjadi kesalahan saat mengambil data.");
}

// Ambil data dari tabel bnl
$sql_leasing = "SELECT * FROM bnl ORDER BY id";
$result = $conn->query($sql_leasing);
if (!$result) {
    die("Query error: " . $conn->error);
}
$leasingData = $result->fetch_all(MYSQLI_ASSOC);
$result->data_seek(0);



// Function to format numbers as Rupiah
function formatRupiah($value)
{
    return 'Rp ' . number_format($value, 0, ',', '.');
}


// Existing function to update saldo
function updateSaldo($conn, $nominal_update)
{
    foreach ($nominal_update as $no_akun => $nominal) {
        $nominal = str_replace('.', '', $nominal); // Remove dots
        $nominal = (float)$nominal; // Convert to float
        if ($nominal < 0) {
            echo "Saldo tidak valid untuk Nomor Akun: $no_akun<br>";
            continue; // Skip invalid entries
        }
        $verified = '0';
        // Update saldo in the database
        $stmt = $conn->prepare("UPDATE list_rekening SET saldo = ?, Verified = ?, updtgl = NOW() WHERE no_akun = ?");
        $stmt->bind_param("dss", $nominal, $verified, $no_akun);

        if ($stmt->execute()) {
            echo "Saldo berhasil diperbarui untuk Nomor Akun: $no_akun<br>";
        } else {
            echo "Error updating saldo for Nomor Akun: $no_akun<br>";
        }
        $stmt->close();
    }
}

// Existing function to update sisa plafond
function updateSisaPlafond($conn, $nominal_update)
{
    foreach ($nominal_update as $id => $nominal) {
        $nominal = str_replace('.', '', $nominal); // Remove dots
        $nominal = (float)$nominal; // Convert to float
        if ($nominal < 0) {
            echo "Sisa plafond tidak valid untuk ID: $id<br>";
            continue; // Skip invalid entries
        }
        $verified = '0';
        // Update sisa plafond in the database
        $stmt = $conn->prepare("UPDATE bnl SET sisa_plafond = ?, Verified = ?, updtgl = NOW() WHERE id = ?");
        $stmt->bind_param("dss", $nominal, $verified, $id);

        if ($stmt->execute()) {
            echo "Sisa plafond berhasil diperbarui untuk ID: $id<br>";
        } else {
            echo "Error updating sisa plafond for ID: $id<br>";
        }
        $stmt->close();
    }
}

// Handle form submission for different POST methods
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_saldo'])) {  // If 'update_saldo' field is submitted
        updateSaldo($conn, $_POST['nominal_update']);
    } elseif (isset($_POST['update_sisa_plafond'])) {  // If 'update_sisa_plafond' field is submitted
        updateSisaPlafond($conn, $_POST['nominal_update']);
    }

    // Refresh the page after form submission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Rekening</title>
    <link rel="stylesheet" type="text/css" href="saldo.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        function formatRupiah(element) {
            let value = element.value.replace(/[^,\d]/g, '').toString();
            let split = value.split(',');
            let sisa = split[0].length % 3;
            let rupiah = split[0].substr(0, sisa);
            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
            element.value = 'Rp ' + rupiah;

            // Update the hidden input with the integer value
            let nominalInput = document.getElementById('nominal_' + element.dataset.id);
            nominalInput.value = value.replace(/\./g, '');
        }

        function exportToExcel() {
            // Get the table element
            var table = document.querySelector("table");

            // Create a new workbook
            var wb = XLSX.utils.book_new();
            var wsData = [];

            // Define the headers you want to export
            var headers = ["No", "Nomor Akun", "Nama Bank", "Nama Akun", "Saldo", "Terakhir Update"];
            wsData.push(headers);

            // Get the data rows
            var dataRows = table.querySelectorAll("tr");
            dataRows.forEach((row, rowIndex) => {
                if (rowIndex === 0) return; // Skip header row
                var rowData = [];
                var cells = row.querySelectorAll("td");
                rowData.push(rowIndex); // Add the serial number
                rowData.push(cells[1].innerText); // Nomor Akun
                rowData.push(cells[2].innerText); // Nama Bank
                rowData.push(cells[3].innerText); // Nama Akun
                rowData.push(cells[4].innerText.replace('Rp ', '').replace('.', '').replace(',', '.')); // Saldo
                rowData.push(cells[5].innerText); // Terakhir Update
                wsData.push(rowData);
            });

            // Create a new worksheet from the data
            var ws = XLSX.utils.aoa_to_sheet(wsData);
            XLSX.utils.book_append_sheet(wb, ws, "Data");

            // Get the current date and time in GMT+7
            var now = new Date();
            now.setHours(now.getHours() + 7);
            var formattedDate = now.toISOString().slice(0, 19).replace(/:/g, '-');

            // Export the workbook to an .xlsx file with the formatted date and time
            XLSX.writeFile(wb, "saldo_" + formattedDate + ".xlsx");
        }
    </script>
</head>

<body>
    <div class="tabs">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a title="Saldo Bank" class="nav-link active" data-bs-toggle="tab" href="#bank">Saldo Bank</a>
            </li>
            <li class="nav-item">
                <a title="Sisa Plafond untuk bank dan leasing" class="nav-link" data-bs-toggle="tab" href="#saldoleasing">Saldo Leasing</a>
            </li>
        </ul>
    </div>
    <div class="tab-content">
        <div id="bank" class="tab-pane active">
            <br>
            <h2>SALDO : <?php echo date("d F Y"); ?></h2>
            <form action="dashboard.php" method="get" style="display: inline;">
                <button type="submit" class="btn-back">Kembali</button>
            </form>
            <form action="" method="POST">
                <table style="border: 1px solid black;">
                    <tr style="border: 1px solid black;">
                        <th style="border: 1px solid black;">No.</th>
                        <th style="border: 1px solid black;">Nomor Akun</th>
                        <th style="border: 1px solid black;">Nama Bank</th>
                        <th style="border: 1px solid black;">Nama Akun</th>
                        <th style="border: 1px solid black;">Saldo</th>
                        <th style="border: 1px solid black;">Terakhir Update</th>
                        <th style="border: 1px solid black;">Update Saldo</th>
                    </tr>
                    <?php
                    if ($result_saldo->num_rows > 0) {
                        $counter = 1; // Initialize counter for serial number
                        while ($saldo = $result_saldo->fetch_assoc()) {
                            echo "<tr style='border: 1px solid black;'>";
                            echo "<td style='border: 1px solid black;'>" . $counter++ . "</td>"; // Display the serial number
                            echo "<td style='border: 1px solid black;'>" . htmlspecialchars($saldo["no_akun"]) . "</td>";
                            echo "<td style='border: 1px solid black;'>" . htmlspecialchars($saldo["nama_bank"]) . "</td>";
                            echo "<td style='border: 1px solid black;'>" . htmlspecialchars($saldo["nama_akun"]) . "</td>";
                            echo "<td style='border: 1px solid black;'>" . formatRupiah($saldo["saldo"]) . "</td>";
                            $formattedDate = date("d-m-Y H:i:s", strtotime($saldo["updtgl"]));
                            echo "<td style='border: 1px solid black;'>" . htmlspecialchars($formattedDate) . "</td>";
                            echo '<td style="border: 1px solid black;"><input type="text" data-id="' . htmlspecialchars($saldo["no_akun"]) . '" onkeyup="formatRupiah(this)" value="' . formatRupiah($saldo["saldo"]) . '" placeholder="Masukkan saldo baru"></td>';
                            echo '<input type="hidden" id="nominal_' . htmlspecialchars($saldo["no_akun"]) . '" name="nominal_update[' . htmlspecialchars($saldo["no_akun"]) . ']" value="' . $saldo["saldo"] . '">';
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr style='border: 1px solid black;'><td colspan='7' style='border: 1px solid black;'>Tidak ada data rekening ditemukan.</td></tr>";
                    }
                    ?>
                </table>
                <br>
                <!-- Hidden field to differentiate POST -->
                <input type="hidden" name="update_saldo" value="1">
                <input type="submit" value="Update Saldo">
                <input type="button" value="Ekspor ke Excel" onclick="exportToExcel()">
            </form>
        </div>
    </div>
    <div id="saldoleasing" class="tab-pane fade">
        <br>
        <h2 style="text-align: center;">Bunga Leasing & Bank</h2>

        <form action="dashboard.php" method="get" style="display: inline;">
            <button type="submit" class="btn-back">Kembali</button>
        </form>
        <br>
        <br>
        <form action="" method="POST">
            <table style="margin: 0 auto; border: 1px solid black; box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);">
                <thead>
                    <tr style="text-align:center; border: 1px solid black;">
                        <th style="width: 30px; border: 1px solid black;">ID</th>
                        <th style="width: 150px; border: 1px solid black;">Keterangan</th>
                        <th style="width: 80px; border: 1px solid black;">Suku Bunga</th>
                        <th style="width: 80px; border: 1px solid black;">DP</th>
                        <th style="width: 80px; border: 1px solid black;">Plafond</th>
                        <th style="width: 150px; border: 1px solid black;">Sisa Plafond</th>
                        <th style="width: 80px; border: 1px solid black;">Last Update</th>
                        <th style="width: 200px; border: 1px solid black;">Update Sisa Plafond</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr style="border: 1px solid black;">
                                <td style="text-align:center; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;"><?= htmlspecialchars($row['id']); ?></td>
                                <td style="text-align:left; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;"><?= htmlspecialchars($row['Ket']); ?></td>
                                <td style="text-align:center; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;"><?= htmlspecialchars($row['suku_bunga']); ?> %</td>
                                <td style="text-align:center; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;"><?= htmlspecialchars($row['dp']); ?> %</td>
                                <td style="text-align:right; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;">Rp. <?= htmlspecialchars(number_format($row['Plafond'], 0, ',', '.')); ?></td>
                                <td style="text-align:right; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;">Rp. <?= htmlspecialchars(number_format($row['sisa_plafond'], 0, ',', '.')); ?></td>
                                <td style="text-align:left; font-family: 'Times New Roman', serif; font-size: 16px; border: 1px solid black;"><?= htmlspecialchars($row['updtgl']); ?></td>
                                <td style="border: 1px solid black;"><input type="text" data-id="<?= htmlspecialchars($row["id"]); ?>" onkeyup="formatRupiah(this)" value="<?= formatRupiah($row["sisa_plafond"]); ?>" placeholder="Masukkan sisa plafond baru"></td>
                                <input type="hidden" id="nominal_<?= htmlspecialchars($row["id"]); ?>" name="nominal_update[<?= htmlspecialchars($row["id"]); ?>]" value="<?= $row["sisa_plafond"]; ?>">
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr style="border: 1px solid black;">
                            <td colspan="8" style="text-align: center; border: 1px solid black;">Tidak ada data yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <br>
            <input type="hidden" name="update_sisa_plafond" value="1">
            <input type="submit" value="Update Sisa Plafond">
        </form>
    </div>
</body>
<?php
$conn->close();
?>
</body>
</html>