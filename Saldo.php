<?php
include 'koneksi.php';

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Koneksi gagal. Silakan coba lagi.");
}

// Query to retrieve all accounts
$sql = "
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
$result = $conn->query($sql);

// Check for query execution errors
if (!$result) {
    error_log("Query failed: " . $conn->error);
    die("Terjadi kesalahan saat mengambil data.");
}

// Function to format numbers as Rupiah
function formatRupiah($value) {
    return 'Rp ' . number_format($value, 0, ',', '.');
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    foreach ($_POST['nominal_update'] as $no_akun => $nominal) {
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
    <h2>SALDO : <?php echo date("d F Y"); ?></h2>
    <form action="" method="post">
        <table>
            <tr>
                <th>No.</th>
                <th>Nomor Akun</th>
                <th>Nama Bank</th>
                <th>Nama Akun</th>
                <th>Saldo</th>
                <th>Terakhir Update</th>
                <th>Update Saldo</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                $counter = 1; // Initialize counter for serial number
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>"; // Display the serial number
                    echo "<td>" . htmlspecialchars($row["no_akun"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nama_bank"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nama_akun"]) . "</td>";
                    echo "<td>" . formatRupiah($row["saldo"]) . "</td>";
                    $formattedDate = date("d-m-Y H:i:s", strtotime($row["updtgl"]));
                    echo "<td>" . htmlspecialchars($formattedDate) . "</td>";
                    echo '<td><input type="text" data-id="' . htmlspecialchars($row["no_akun"]) . '" onkeyup="formatRupiah(this)" value="' . formatRupiah($row["saldo"]) . '" placeholder="Masukkan saldo baru"></td>';
                    echo '<input type="hidden" id="nominal_' . htmlspecialchars($row["no_akun"]) . '" name="nominal_update[' . htmlspecialchars($row["no_akun"]) . ']" value="' . $row["saldo"] . '">';
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>Tidak ada data rekening ditemukan.</td></tr>";
            }
            ?>
        </table>
        <br>
        <input type="submit" value="Perbarui Saldo">
        <input type="button" value="Ekspor ke Excel" onclick="exportToExcel()">
    </form>
    <form action="dashboard.php" method="get" style="display: inline;">
        <button type="submit" class="btn-back">Kembali</button>
    </form>

<?php
$conn->close();
?>
</body>
</html>
