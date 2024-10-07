<?php
include 'koneksi.php';

// Function to count the number of items based on status
function countItems($conn, $table, $statusColumn, $statusValue) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM $table WHERE $statusColumn = ?");
    $stmt->bind_param("s", $statusValue);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int)$row['count'];
}

// Function to count cheques based on a specific condition
function countChequesDue($conn, $table, $statusColumn, $statusValue, $dateCondition) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM $table WHERE $statusColumn = ? AND $dateCondition");
    $stmt->bind_param("s", $statusValue);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return (int)$row['count'];
}

// Initialize counts for Giro
$unused_count = countItems($conn, 'data_giro', 'statusgiro', 'Unused');
$issued_count = countItems($conn, 'detail_giro', 'statgiro', 'Issued');
$Posted_count = countItems($conn, 'detail_giro', 'statgiro', 'Posted');
$void_count = countItems($conn, 'detail_giro', 'statgiro', 'void');
$return_count = countItems($conn, 'detail_giro', 'statgiro', 'return');
$jt_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "DATEDIFF(tanggal_jatuh_tempo, NOW()) BETWEEN 0 AND 7");
$monthly_due_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW())");
$Overdue_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "tanggal_jatuh_tempo < CURDATE()");

// Initialize counts for Cek
$unused_cek_count = countItems($conn, 'data_cek', 'statuscek', 'Unused');
$issued_cek_count = countItems($conn, 'detail_cek', 'statcek', 'Issued');
$Posted_cek_count = countItems($conn, 'detail_cek', 'statcek', 'Posted');
$void_cek_count = countItems($conn, 'detail_cek', 'statcek', 'void');
$return_cek_count = countItems($conn, 'detail_cek', 'statcek', 'return');
$jt_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "DATEDIFF(tanggal_jatuh_tempo, NOW()) BETWEEN 0 AND 7");
$monthly_due_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW())");
$Overdue_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "tanggal_jatuh_tempo < NOW()");

//Kebutuhan Tab List Giro
// Initialize an empty array to store the due cheques
$due_cheques = [];

// Get the selected start and end dates or default to today
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Query for fetching the cheques due between the selected dates
$sql = "SELECT d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.nogiro, 
               SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.PVRNo, dg.keterangan 
        FROM detail_giro AS dg
        INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
        WHERE dg.StatGiro = 'Issued' 
        AND dg.tanggal_jatuh_tempo BETWEEN '$start_date' AND '$end_date'
        GROUP BY dg.tanggal_jatuh_tempo, d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.nogiro, dg.PVRNo, dg.keterangan
        ORDER BY dg.tanggal_jatuh_tempo ASC;";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $due_cheques[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}


// Close connection
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

        .tabs {
            margin-bottom: 20px;
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
                        <a href="InputCustomer.php">Input Customer</a>
                        <a href="Generate.php">Generate</a>
                    </div>
                </li>
                <li><a href="#">Giro</a>
                    <div class="dropdown">
                        <a href="TulisGiro.php">Issued Giro</a>
                        <a href="ProsesGiro.php">Proses Giro</a>
                    </div>
                </li>
                <li><a href="#">Cek</a>
                    <div class="dropdown">
                        <a href="TulisCek.php">Tulis Cek</a>
                        <a href="PencairanCek.php">Pencairan Cek</a>
                        <a href="CekVoid.php">Void Cek</a>
                        <a href="CekReturn.php">Return Cek</a>
                        <a href="Search.php">Search Cek</a>
                    </div>
                </li>
                <li><a href="Search.php">Search</a></li>
                <li><a href="#">Laporan</a>
                    <div class="dropdown">
                        <a href="ReportStockGiro.php">Laporan Stock Giro Belum Terpakai</a>
                        <a href="ReportIssuedGiro.php">Laporan Giro yang sudah terbit</a>
                    </div>
                </li>
                <li><a href="logout.php">Logout</a></li> <!-- Logout link -->
            </ul>
        </nav>

    <header>
        Aplikasi Giro
    </header>

    <section>
        <div class="tabs">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#listgiro">List Giro</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#giro">Giro</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#cek">Cek</a>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div id="listgiro" class="tab-pane fade show active">
            <form method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-6">
                        <label for="start_date" class="form-label">Tanggal Mulai:</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="end_date" class="form-label">Tanggal Akhir:</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-2">Tampilkan</button>
            </form>
            <div class="container table-container mt-4" id="contentExport">
                <h2 class="mb-4">Daftar Giro yang Jatuh Tempo</h2>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-primary">
                            <tr>
                                <th>Tanggal Jatuh Tempo</th>
                                <th>No. Giro</th>
                                <th>Pemegang</th>
                                <th>Nama Penerima</th>
                                <th>Ak. Penerima</th>
                                <th>Bank Penerima</th>
                                <th>No PVR</th>
                                <th>Keterangan</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($due_cheques) > 0): ?>
                            <?php
                            // Organize data by date and bank
                            $bank_data = [];
                            foreach ($due_cheques as $cheque) {
                                $date_key = $cheque['tanggal_jatuh_tempo'];
                                $bank_key = $cheque['namabank'];

                                if (!isset($bank_data[$date_key][$bank_key])) {
                                    $bank_data[$date_key][$bank_key] = [
                                        'entries' => [],
                                        'subtotal' => 0,
                                    ];
                                }

                                $bank_data[$date_key][$bank_key]['entries'][] = $cheque;
                                $bank_data[$date_key][$bank_key]['subtotal'] += $cheque['total_nominal'];
                            }

                            // Render the organized data
                            foreach ($bank_data as $date => $banks): ?>
                                <tr>
                                    <td colspan="9" class="section-title"><h4><?php echo htmlspecialchars(date('d-m-Y', strtotime($date))); ?></h4></td>
                                </tr>
                                <?php foreach ($banks as $bank_name => $bank_info): ?>
                                    <tr>
                                        <td colspan="9" class="section-title"><strong><?php echo htmlspecialchars($bank_name); ?></strong></td>
                                    </tr>
                                    <?php foreach ($bank_info['entries'] as $cheque): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($cheque['tanggal_jatuh_tempo']))); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['nogiro']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['ac_name']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['nama_penerima']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['ac_penerima']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['namabank']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['PVRNo']); ?></td>
                                            <td><?php echo htmlspecialchars($cheque['keterangan']); ?></td>
                                            <td><?php echo htmlspecialchars(number_format($cheque['total_nominal'], 2)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td colspan="8" class="text-end"><strong>Total untuk <?php echo htmlspecialchars($bank_name); ?>:</strong></td>
                                        <td><?php echo htmlspecialchars(number_format($bank_info['subtotal'], 2)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center">Tidak ada giro yang jatuh tempo pada tanggal ini.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
            <div id="giro" class="tab-pane fade">
                <div class="stats-card">
                    <div class="card">
                        <a href="UnusedGiroList.php">
                            <h3>Giro Available</h3>
                            <p><?php echo $unused_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="IssuedGiroList.php">
                            <h3>Giro Issued</h3>
                            <p><?php echo $issued_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="PostedGiroList.php">
                            <h3>Giro Posted</h3>
                            <p><?php echo $Posted_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="VoidGiroList.php">
                            <h3>Giro Voided</h3>
                            <p><?php echo $void_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="ReturnGiroList.php">
                            <h3>Giro Returned</h3>
                            <p><?php echo $return_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="JTGiroList.php">
                            <h3>Giro Due in 7 Days</h3>
                            <p><?php echo $jt_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="MonthlyDueGiroList.php">
                            <h3>Giro Monthly Due</h3>
                            <p><?php echo $monthly_due_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="OverDueGiroList.php">
                            <h3>Giro Overdue</h3>
                            <p><?php echo $Overdue_count; ?></p>
                        </a>
                    </div>
                </div>
            </div>
            <div id="cek" class="tab-pane fade">
                <div class="stats-card">
                    <div class="card">
                        <a href="UnusedCekList.php">
                            <h3>Cek Available</h3>
                            <p><?php echo $unused_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="IssuedCekList.php">
                            <h3>Cek Issued</h3>
                            <p><?php echo $issued_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="PostedCekList.php">
                            <h3>Cek Posted</h3>
                            <p><?php echo $Posted_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="VoidCekList.php">
                            <h3>Cek Voided</h3>
                            <p><?php echo $void_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="ReturnCekList.php">
                            <h3>Cek Returned</h3>
                            <p><?php echo $return_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="JTCekList.php">
                            <h3>Cek Due in 7 Days</h3>
                            <p><?php echo $jt_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="MonthlyDueCekList.php">
                            <h3>Cek Monthly Due</h3>
                            <p><?php echo $monthly_due_cek_count; ?></p>
                        </a>
                    </div>
                    <div class="card">
                        <a href="OverDueCekList.php">
                            <h3>Cek Overdue</h3>
                            <p><?php echo $Overdue_cek_count; ?></p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        © 2024 Aplikasi Giro. All rights reserved.
    </footer>

    <script>
        document.getElementById('toggleNavbar').addEventListener('click', function() {
            document.getElementById('navbar').classList.toggle('hide');
        });
    </script>
</body>
</html>
