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

// Prepare the SQL statement
$stmt = $conn->prepare("
    SELECT nogiro, tanggal_jatuh_tempo, nominal 
    FROM detail_giro 
    WHERE StatGiro = 'Issued' AND tanggal_jatuh_tempo < ?
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
            <th>No Giro</th>
            <th>Tanggal Jatuh Tempo</th>
            <th>Nominal</th>
        </tr>
        <?php foreach ($issued_giro_records as $giro): ?>
            <tr>
                <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                <td><?php echo number_format($giro['nominal']); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <br>
    <a href="dashboard.php">Kembali ke Halaman Utama</a>
</body>
</html>
