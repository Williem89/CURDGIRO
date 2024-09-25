<?php
include 'koneksi.php';

// Inisialisasi variabel
$unused_count = 0;
$issued_count = 0;
$jt_count = 0; // Changed this to match the variable being used later

// Query untuk menghitung jumlah giro yang belum digunakan
$sql = "SELECT COUNT(*) AS unused_count FROM data_giro WHERE statusgiro='Unused'";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $unused_count = (int)$row['unused_count'];
    }

// Query untuk menghitung jumlah giro yang sudah diterbitkan
$sql = "SELECT COUNT(*) AS issued_count FROM detail_giro WHERE statgiro='Issued'";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $issued_count = (int)$row['issued_count'];
}

// Query for counting the number of cheques due in one week
$sql = "SELECT COUNT(*) AS jt_count 
    FROM detail_giro 
    WHERE StatGiro = 'Issued' 
    AND DATEDIFF(tanggal_jatuh_tempo, NOW()) BETWEEN 0 AND 7;";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $jt_count = (int)$row['jt_count'];
} else {
    echo "Error: " . $conn->error;
    $jt_count = 0;
}

// Query for counting the number of cheques Overdue
$sql = "SELECT COUNT(*) AS Overdue_count 
        FROM detail_giro 
        WHERE StatGiro = 'Issued' 
        AND tanggal_jatuh_tempo < NOW();";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $Overdue_count = (int)$row['Overdue_count'];
} else {
    echo "Error: " . $conn->error;
    $Overdue_count = 0;
}

// Query for counting the number of cheques due this month
$sql = "SELECT COUNT(*) AS monthly_due_count 
        FROM detail_giro 
        WHERE StatGiro = 'Issued' 
        AND MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) 
        AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW());";
$result = $conn->query($sql);
if ($result) {
    $row = $result->fetch_assoc();
    $monthly_due_count = (int)$row['monthly_due_count'];
} else {
    echo "Error: " . $conn->error;
    $monthly_due_count = 0;
}


// Tutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #baf2ff;
            margin: 0;
            padding: 20px;
        }
        header {
            text-align: center;
            padding: 20px;
            background-color: #bae8ff;
            color: white;
        }
        nav {
            margin: 20px 0;
            text-align: left;
        }
        nav ul {
            list-style-type: none;
            padding: 0;
        }
        nav ul li {
            display: inline-block;
            position: relative;
            margin: 0 15px;
        }
        nav ul li a {
            text-decoration: none;
            color: #007bff;
            padding: 10px 15px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        nav ul li a:hover {
            background-color: #007bff;
            color: white;
        }
        .dropdown {
            display: none;
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            z-index: 1;
            min-width: 150px;
        }
        nav ul li:hover .dropdown {
            display: block;
        }
        .dropdown a {
            display: block;
            color: #007bff;
            padding: 10px;
            text-decoration: none;
        }
        .dropdown a:hover {
            background-color: #f1f1f1;
        }
        section {
            background: #bae8ff;
            border-radius: 5px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        section h2 {
            color: #343a40;
        }
        footer {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <header>
        <h1>Selamat Datang di Aplikasi Giro</h1>
        <p>Silakan pilih salah satu opsi di bawah ini:</p>
    </header>
    
    <nav>
        <ul>
            <li>
                <a href="#">Master Data</a>
                <div class="dropdown">
                    <a href="register.html">Register User</a>
                    <a href="inputentitas.php">Input Entitas</a>
                    <a href="InputRekening.php">Input Rekening</a>
                    <a href="GenerateGiro.php">Generate Giro</a>
                </div>
            </li>
            <li>
                <a href="#">Giro</a>
                <div class="dropdown">
                    <a href="TulisGiro.php">Tulis Giro</a>
                    <a href="PencairanGiro.php">Pencairan Giro</a>
                </div>
            </li>
            <li>
                <a href="#">Laporan</a>
                <div class="dropdown">
                    <a href="ReportStockGiro.php">Laporan Stock Giro Belum Terpakai</a>
                    <a href="ReportIssuedGiro.php">Laporan Giro yang sudah terbit</a>
                </div>
            </li>
        </ul>
    </nav>

    <section>
    <h2>Statistik Giro</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Jumlah Giro Unused</td>
                <td>
                    <a href="UnusedGiroList.php"><?php echo htmlspecialchars($unused_count); ?></a>
                </td>
            </tr>
            <tr>
                <td>Jumlah Giro Issued</td>
                <td>
                    <a href="IssuedGiroList.php"><?php echo htmlspecialchars($issued_count); ?></a>
                </td>
            </tr>
            <tr>
                <td>Jumlah Giro Akan Jatuh Tempo</td>
                <td>
                    <a href="JTGiroList.php"><?php echo htmlspecialchars($jt_count); ?></a>
                </td>
            </tr>
            <tr>
                <td style="color: red;">Jumlah Giro Lewat Jatuh Tempo</td>
                <td>
                    <a href="OverDueGiroList.php" style="color: red;"><?php echo htmlspecialchars($Overdue_count); ?></a>
                </td>
            </tr>
            <tr>
                <td>Jumlah Giro Jatuh Tempo Bulan Ini</td>
                <td>
                    <a href="MonthlyDueGiroList.php"><?php echo htmlspecialchars($monthly_due_count); ?></a>
                </td>
            </tr>
        </tbody>
    </table>
</section>



    <footer>
        <p>&copy; <?php echo date("Y"); ?> Aplikasi Giro. All rights reserved.</p>
    </footer>
</body>
</html>
