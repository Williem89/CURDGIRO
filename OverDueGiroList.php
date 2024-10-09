<?php
include 'koneksi.php';

// Establishing database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate the current date in the appropriate format
$current_date = date('Y-m-d'); // Adjust the date format based on your database

$stmt = $conn->prepare("SELECT e.nama_entitas, 
       d.namabank, 
       d.ac_number, 
       dg.ac_penerima, 
       dg.nama_penerima, 
       dg.bank_penerima, 
       dg.nogiro, 
       SUM(dg.nominal) AS total_nominal, 
       dg.tanggal_jatuh_tempo, 
       dg.PVRNo, 
       dg.keterangan, 
       dg.nominal as Nominal
FROM detail_giro AS dg
INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
WHERE StatGiro = 'Issued' AND tanggal_jatuh_tempo < ?
GROUP BY e.nama_entitas, 
         d.namabank, 
         d.ac_number, 
         dg.ac_penerima, 
         dg.nama_penerima, 
         dg.bank_penerima, 
         dg.nogiro, 
         dg.tanggal_jatuh_tempo, 
         dg.PVRNo, 
         dg.keterangan
ORDER BY e.nama_entitas, dg.nogiro;
");

// Bind parameters
$stmt->bind_param("s", $current_date);

// Execute the statement
if (!$stmt->execute()) {
    echo "Error executing query: " . $stmt->error;
}

// Get the result
$result = $stmt->get_result();
$issued_giro_records = [];
$no=1;

// Check if there are any results
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $issued_giro_records[] = $row;
    }
} else {
    echo "No records found.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Lewat Jatuh Tempo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: linear-gradient(to right, #ff9eb3, #ff4d94);
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            display: inline-block;
            margin: 20px auto;
            padding: 10px 20px;
            background: linear-gradient(to right, #ff9eb3, #ff4d94);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }

        a:hover {
            background: linear-gradient(to right, #ff4d94, #ff6f99);
        }
    </style>
</head>
<body>
    <h1>Daftar Giro yang Lewat Jatuh Tempo</h1>
    <table>
            <tr>
                <th>No</th>
                <th>Tanggal Jatuh Tempo</th>
                <th>No Giro</th>
                <th>No Rek Asal</th>
                <th>Bank Asal</th>
                <th>Rekening Tujuan</th>
                <th>Atas Nama</th>
                <th>Bank Tujuan</th>
                <th>No PVR</th>
                <th>Keterangan</th>
                <th>Nominal</th>
            </tr>
        <?php foreach ($issued_giro_records as $giro): ?>
            <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d-M-Y', strtotime($giro['tanggal_jatuh_tempo'])); ?></td>  
                        <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                        <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['nama_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['bank_penerima']); ?></td>
                        <td><?php echo htmlspecialchars($giro['PVRNo']); ?></td>
                        <td><?php echo htmlspecialchars($giro['keterangan']); ?></td>
                        <td><?php echo 'Rp. '. number_format($giro['Nominal'], 2, ',', '.'); ?></td>
                    </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
