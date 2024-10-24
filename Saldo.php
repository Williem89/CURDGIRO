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

        // Update saldo in the database
        $stmt = $conn->prepare("UPDATE list_rekening SET saldo = ?, updtgl = CURDATE() WHERE no_akun = ?");
        $stmt->bind_param("ds", $nominal, $no_akun);

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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #007BFF;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        input[type="text"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        input[type="submit"], .btn-back {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        input[type="submit"]:hover, .btn-back:hover {
            background-color: #218838;
        }
    </style>
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
                // Output data for each row
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $counter++ . "</td>"; // Display the serial number
                    echo "<td>" . htmlspecialchars($row["no_akun"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nama_bank"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["nama_akun"]) . "</td>";
                    echo "<td>" . formatRupiah($row["saldo"]) . "</td>";
                    // Format date to dd-mm-yyyy
                    $formattedDate = date("d-m-Y", strtotime($row["updtgl"]));
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
    </form>
    <form action="dashboard.php" method="get" style="display: inline;">
        <button type="submit" class="btn-back">Kembali</button>
    </form>

<?php
$conn->close();
?>
</body>
</html>
