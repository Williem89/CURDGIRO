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
    <title>Aplikasi Giro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f9fc;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background: linear-gradient(90deg, #007bff, #6a11cb);
            text-align: center;
            padding: 15px 0;
            color: white;
            font-size: 24px;
            font-weight: 500;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav {
            background-color: #fff;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 60px;
            padding-left: 10px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            transform: translateX(0); /* Default visible position */
            transition: transform 0.3s ease;
        }

        nav.hide {
            transform: translateX(-100%); /* Move off-screen */
        }

        nav ul {
            padding: 0;
            list-style: none;
        }

        nav ul li {
            margin: 20px 0;
        }

        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            display: block;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        nav ul li a:hover {
            background-color: #007bff;
            color: white;
        }

        .dropdown {
            display: none;
            padding-left: 20px;
        }

        nav ul li:hover .dropdown {
            display: block;
        }

        .dropdown a {
            padding: 5px 20px;
            color: #007bff;
        }

        section {
            margin-left: 270px;
            padding: 100px 20px 20px;
            flex-grow: 1;
        }

        .stats-card {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: #fff;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
            text-decoration: none; /* Remove underline */
            color: inherit; /* Inherit text color */
        }
        .card a {
            text-decoration: none; /* Remove underline */
            color: inherit; /* Inherit the text color */
        }

        .card a:hover {
            color: inherit; /* Ensure color remains the same on hover */
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: #f0f4ff; /* Optional: Change background on hover */
        }


        .card h3 {
            font-size: 18px;
            font-weight: 500;
        }

        .card p {
            font-size: 24px;
            font-weight: 700;
            margin: 10px 0 0;
        }

        footer {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-align: center;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        }

        #toggleNavbar {
            position: fixed;
            top: 15px;
            left: 10px;
            background-color: #007bff;
            color: white;
            padding: 10px;
            cursor: pointer;
            border-radius: 4px;
            z-index: 1001;
        }

        @media (max-width: 768px) {
            nav {
                transform: translateX(-100%);
            }

            nav.hide {
                transform: translateX(0);
            }

            section {
                margin-left: 0;
                padding: 80px 10px;
            }
        }
    </style>
</head>
<body>
    <div id="toggleNavbar">☰ Menu</div>
    <nav id="navbar">
        <ul>
            <li><a href="#">Master Data</a>
                <div class="dropdown">
                    <a href="register.html">Register User</a>
                    <a href="inputentitas.php">Input Entitas</a>
                    <a href="InputRekening.php">Input Rekening</a>
                    <a href="GenerateGiro.php">Generate Giro</a>
                </div>
            </li>
            <li><a href="#">Giro</a>
                <div class="dropdown">
                    <a href="TulisGiro.php">Tulis Giro</a>
                    <a href="PencairanGiro.php">Pencairan Giro</a>
                </div>
            </li>
            <li><a href="#">Laporan</a>
                <div class="dropdown">
                    <a href="ReportStockGiro.php">Laporan Stock Giro Belum Terpakai</a>
                    <a href="ReportIssuedGiro.php">Laporan Giro yang sudah terbit</a>
                </div>
            </li>
        </ul>
    </nav>

    <header>
        Selamat Datang di Aplikasi Giro
    </header>

    <section>
    <h2>Statistik Giro</h2>
    <div class="stats-card">
        <div class="card">
            <a href="UnusedGiroList.php">
                <h3>Jumlah Giro Unused</h3>
                <p><?php echo htmlspecialchars($unused_count); ?></p>
            </a>
        </div>
        <div class="card">
            <a href="IssuedGiroList.php">
                <h3>Jumlah Giro Issued</h3>
                <p><?php echo htmlspecialchars($issued_count); ?></p>
            </a>
        </div>
        <div class="card">
            <a href="JTGiroList.php">
                <h3>Jumlah Giro Akan Jatuh Tempo</h3>
                <p><?php echo htmlspecialchars($jt_count); ?></p>
            </a>
        </div>
        <div class="card">
            <a href="OverDueGiroList.php">
                <h3 style="color: red;">Jumlah Giro Lewat Jatuh Tempo</h3>
                <p style="color: red;"><?php echo htmlspecialchars($Overdue_count); ?></p>
            </a>
        </div>
        <div class="card">
            <a href="MonthlyDueGiroList.php">
                <h3>Jumlah Giro Jatuh Tempo Bulan Ini</h3>
                <p><?php echo htmlspecialchars($monthly_due_count); ?></p>
            </a>
        </div>
    </div>
</section>

    <footer>
        &copy; <?php echo date("Y"); ?> Aplikasi Giro. All rights reserved.
    </footer>

    <script>
        const toggleNavbar = document.getElementById('toggleNavbar');
        const navbar = document.getElementById('navbar');

        toggleNavbar.addEventListener('click', () => {
            navbar.classList.toggle('hide');
        });
    </script>
</body>
</html>

