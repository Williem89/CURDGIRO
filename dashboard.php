    <?php
    include 'koneksi.php';

    session_start();

    // Function to count the number of items based on status
    function countItems($conn, $table, $statusColumn, $statusValue)
    {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM $table WHERE $statusColumn = ?");
        $stmt->bind_param("s", $statusValue);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return (int)$row['count'];
    }

    // Function to count cheques based on a specific condition
    function countChequesDue($conn, $table, $statusColumn, $statusValue, $dateCondition)
    {
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
    $jt_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "DATEDIFF(tanggal_jatuh_tempo, CURDATE()) BETWEEN 0 AND 7");
    $monthly_due_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW())");
    $Overdue_count = countChequesDue($conn, 'detail_giro', 'StatGiro', 'Issued', "tanggal_jatuh_tempo < CURDATE()");

    // Initialize counts for Cek
    $unused_cek_count = countItems($conn, 'data_cek', 'statuscek', 'Unused');
    $issued_cek_count = countItems($conn, 'detail_cek', 'statcek', 'Issued');
    $Posted_cek_count = countItems($conn, 'detail_cek', 'statcek', 'Posted');
    $void_cek_count = countItems($conn, 'detail_cek', 'statcek', 'void');
    $return_cek_count = countItems($conn, 'detail_cek', 'statcek', 'return');
    $jt_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "DATEDIFF(tanggal_jatuh_tempo, CURDATE()) BETWEEN 0 AND 7");
    $monthly_due_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW())");
    $Overdue_cek_count = countChequesDue($conn, 'detail_cek', 'Statcek', 'Issued', "tanggal_jatuh_tempo < CURDATE()");

    // Initialize counts for loa
    $unused_loa_count = countItems($conn, 'data_loa', 'statusloa', 'Unused');
    $issued_loa_count = countItems($conn, 'detail_loa', 'statloa', 'Issued');
    $Posted_loa_count = countItems($conn, 'detail_loa', 'statloa', 'Posted');
    $void_loa_count = countItems($conn, 'detail_loa', 'statloa', 'void');
    $return_loa_count = countItems($conn, 'detail_loa', 'statloa', 'return');
    $jt_loa_count = countChequesDue($conn, 'detail_loa', 'Statloa', 'Issued', "DATEDIFF(tanggal_jatuh_tempo, CURDATE()) BETWEEN 0 AND 7");
    $monthly_due_loa_count = countChequesDue($conn, 'detail_loa', 'Statloa', 'Issued', "MONTH(tanggal_jatuh_tempo) = MONTH(NOW()) AND YEAR(tanggal_jatuh_tempo) = YEAR(NOW())");
    $Overdue_loa_count = countChequesDue($conn, 'detail_loa', 'Statloa', 'Issued', "tanggal_jatuh_tempo < CURDATE()");

    //Kebutuhan Tab List Giro
    // Initialize an empty array to store the due cheques
    $due_cheques = [];
    // Initialize variables
    $due_giros = []; // Initialize as an empty array
    $due_checks = []; // Initialize as an empty array
    $due_loas = [];

    // Get the selected start and end dates or default to today
    $start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('d-m-y');
    $end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('d-m-y');
    $type = isset($_POST['filter_type']) ? $_POST['filter_type'] : 'All';

    /// Function to fetch due items
    function fetchDueItems($conn, $type, $start_date, $end_date)
    {
        // Define the table and column names based on the type
        if ($type === 'giro') {
            $tableDetail = 'detail_giro';
            $tableData = 'data_giro';
            $statusColumn = 'StatGiro';
            $numberColumn = 'nogiro';
        } elseif ($type === 'cek') {
            $tableDetail = 'detail_cek';
            $tableData = 'data_cek';
            $statusColumn = 'StatCek';
            $numberColumn = 'nocek';
        } elseif ($type === 'loa') {
            $tableDetail = 'detail_loa';
            $tableData = 'data_loa';
            $statusColumn = 'StatLoa';
            $numberColumn = 'noloa';
        } else {
            throw new Exception("Invalid type specified");
        }

        // Prepare the SQL query
        $sql = "SELECT d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.$numberColumn, 
                    SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.PVRNo, dg.keterangan 
                FROM $tableDetail AS dg
                INNER JOIN $tableData AS d ON dg.$numberColumn = d.$numberColumn
                WHERE dg.$statusColumn = 'Issued' 
                AND dg.tanggal_jatuh_tempo BETWEEN ? AND ?
                GROUP BY dg.tanggal_jatuh_tempo, d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.$numberColumn, dg.PVRNo, dg.keterangan
                ORDER BY dg.tanggal_jatuh_tempo ASC";

        // Execute the prepared statement
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch the results
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        $stmt->close();
        return $items;
    }

    // Fetch due items based on the selected type
    if ($type === 'All') {
        $due_cheques = fetchDueItems($conn, 'cek', $start_date, $end_date);
        $due_giro = fetchDueItems($conn, 'giro', $start_date, $end_date);
        $due_loa = fetchDueItems($conn, 'loa', $start_date, $end_date);
        // Combine all arrays
        $due_items = array_merge($due_cheques, $due_giro, $due_loa);
    } elseif ($type === 'Giro') {
        $due_giro = fetchDueItems($conn, 'giro', $start_date, $end_date);
    } elseif ($type === 'Cek') {
        $due_cheques = fetchDueItems($conn, 'cek', $start_date, $end_date);
    } elseif ($type === 'Loa') {
        $due_loa = fetchDueItems($conn, 'loa', $start_date, $end_date);
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
        <link rel="icon" type="image/x-icon" href="img/icon.png">
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
            left : 0;
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
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
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
            z-index: 999;
            transform: translateX(0);
            transition: transform 0.3s ease;
        }

        nav.hide {
            transform: translateX(-100%);
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
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
            padding: 20px;
            transition: transform 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .card a {
            text-decoration: none;
            color: inherit;
        }

        .card a:hover {
            color: inherit;
        }

        .card:hover {
            transform: translateY(-5px);
            background-color: #f0f4ff;
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
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
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
        <script>
            async function getData(id) {
                const apitoken = '76e66990-0128-4c26-a8bb-6c84c033188c';
                const collection = 'todo';
                try {
                    const response = await fetch(`https://knightly-dolphin-6e73.codehooks.io/${collection}/${id}`, {
                        method: 'GET',
                        headers: {
                            'x-apikey': apitoken,
                            'Content-Type': 'application/json'
                        }
                    });
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    throw error;
                }
            }

            document.addEventListener('DOMContentLoaded', async function() {
            const changelogButton = document.getElementById('changelog-button');
            const changelogModal = document.getElementById('changelog-modal');
            const changelogList = document.getElementById('changelog-list');
            const closeChangelog = document.getElementById('close-changelog');

            changelogButton.addEventListener('click', async function() {
                changelogList.innerHTML = ''; // Clear previous data
                try {
                    const data = await getData('66fdf7bfc20f986ee946ade2');
                    data.timeline.forEach(item => {
                        const listItem = document.createElement('li');
                        listItem.innerHTML = `
                            <strong>Note:</strong> ${item.note}<br>
                            <strong>Done Time:</strong> ${item.donetime ? new Date(item.donetime).toLocaleString() : 'Not completed'}<br>
                            <strong>Status:</strong> ${item.status}<br>
                            ${item.holdreason ? `<strong>Hold Reason:</strong> ${item.holdreason}<br>` : ''}
                        `;
                        changelogList.appendChild(listItem);
                    });
                    changelogModal.style.display = 'block';
                } catch (error) {
                    changelogList.innerHTML = '<li>Error fetching changelog</li>';
                    changelogModal.style.display = 'block';
                }
            });

            closeChangelog.addEventListener('click', function() {
                changelogModal.style.display = 'none';
            });

            // Close the modal when clicking outside of it
            window.addEventListener('click', function(event) {
                if (event.target == changelogModal) {
                    changelogModal.style.display = 'none';
                }
            });
        });
        </script>
        <div id="toggleNavbar">☰ Menu</div>
        <nav id="navbar">
            <ul>
                <li><a href="#">Master Data</a>
                    <div class="dropdown">
                        <a title="Untuk menganti password user" href="change_password.html">Change Password</a>
                        <a title="untuk mendaftarkan perusahaan baru yang tergabung dalam GEL GROUP" href="inputentitas.php">Input Entitas</a>
                        <a title="untuk mendaftarkan rekening baru untuk entitas yang ada" href="InputRekening.php">Input Rekening</a>
                        <a title="untuk mendaftarkan Customer baru untuk GEL GROUP" href="InputCustomer.php">Input Customer</a>
                        <a title="untuk membuat data lembaran GIRO/CEK/LOA" href="Generate.php">Generate</a>
                    </div>
                </li>
                <li><a title="Update saldo Rekening" href="Saldo.php">Update Saldo</a></li>
                <!-- if (!isset($_SESSION['username']) || !isset($_SESSION['UsrLevel']) || $_SESSION['UsrLevel'] != '2') {
                        header('Location: backoff.html');
                        exit();
                    } -->
                <?php if (isset($_SESSION['UsrLevel']) && $_SESSION['UsrLevel'] == '2'): ?>
                    <li><a title="untuk memyetujui data lembaran GIRO/CEK/LOA yang baru di buat" href="Approve.php">Approve Generate</a></li>
                <?php endif; ?>
                <li><a title="Menulis atau mengisi Data GIRO/CEK/LOA" href="#">Issued</a>
                    <div class="dropdown">
                        <a title="Mengisi data Giro" href="TulisGiro.php">Giro</a>
                        <a title="Mengisi data Cek" href="TulisCek.php">Cek</a>
                        <a title="Mengisi data LOA" href="Tulisloa.php">LOA</a>
                    </div>
                </li>
                <li><a title="Untuk Approve Issued /Mencairkan / Void / Return /Edit NoPVR& Keterangan /Preview Scan Giro yang di Attach" href="ProsesGiro.php">Proses GIRO/CEK/LOA</a></li>
                <li><a title="Untuk Mencari GIRO/Cek/LOA " href="Search.php">Search</a></li>
                <!--<li><a href="#">Laporan</a>
                        <div class="dropdown">
                            <a href="ReportStockGiro.php">Laporan Stock Giro Belum Terpakai</a>
                            <a href="ReportIssuedGiro.php">Laporan Giro yang sudah terbit</a>
                        </div>
                    </li>-->
                <li><a title="untuk keluar dari software" href="logout.php">Logout</a></li> <!-- Logout link -->
                <button id="changelog-button" style="position:absolute;bottom:10px; background-color: #007bff; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin:auto">
                    Show Changelog
                </button>
            </ul>
        </nav>

        <header>
            <h2>Aplikasi Giro</h2>
        </header>

        <section>
            
            
            <div class="tabs">
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a title="Menampilkan data GIRO/CEK/LOA yang sudah belum dicairkan" class="nav-link active" data-bs-toggle="tab" href="#listGiroCek">Outstanding Vs Saldo</a>
                    </li>
                    <li class="nav-item">
                        <a title="Menampilkan data Statistik GIRO" class="nav-link" data-bs-toggle="tab" href="#giro">Giro</a>
                    </li>
                    <li class="nav-item">
                        <a title="Menampilkan data Statistik CEK" class="nav-link" data-bs-toggle="tab" href="#cek">Cek</a>
                    </li>
                    <li class="nav-item">
                        <a title="Menampilkan data Statistik LOA" class="nav-link" data-bs-toggle="tab" href="#loa">LOA</a>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <div id="listGiroCek" class="tab-pane fade">
                    <!-- Combined Table for Giro and Cek -->
                    <h2 class="mt-5">Outstanding Vs Saldo</h2>
                    <p>Per tanggal : <?php echo date('d-m-Y'); ?></p>
                    <form method="post" class="mb-4">    <?php 
                    include 'outstanding.php';
                    ?>
                </div>

                <div id="giro" class="tab-pane fade">
                    <div class="stats-card">
                        <div class="card">
                            <a title="Jumlah Giro yang Available" href="UnusedGiroList.php">
                                <h3>Giro Available</h3>
                                <p><?php echo $unused_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Jumlah Giro yang sudah di Issued" href="IssuedGiroList.php">
                                <h3>Giro Issued</h3>
                                <p><?php echo $issued_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Jumlah Giro yang sudah Cair/Posted" href="PostedGiroList.php">
                                <h3>Giro Posted</h3>
                                <p><?php echo $Posted_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a titile="Jumlah Giro yang Void" href="VoidGiroList.php">
                                <h3>Giro Voided</h3>
                                <p><?php echo $void_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Jumlah Giro yang sudah di kembalikan ke Bank" href="ReturnGiroList.php">
                                <h3>Giro Returned</h3>
                                <p><?php echo $return_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="JUmlah Giro yang akan jatuh tempo dalam kurung waktu 7 hari" href="JTGiroList.php">
                                <h3>Giro Due in 7 Days</h3>
                                <p><?php echo $jt_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Jumlah Giro yang akan jatuh tempo dalam periode bulan ini" href="MonthlyDueGiroList.php">
                                <h3>Giro Monthly Due</h3>
                                <p><?php echo $monthly_due_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Jumlah Giro yang sudah lewat jatuh tempo" href="OverDueGiroList.php">
                                <h3>Giro Overdue</h3>
                                <p><?php echo $Overdue_count; ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                <div id="cek" class="tab-pane fade">
                    <div class="stats-card">
                        <div class="card">
                            <a title="Daftar Cek yang Available" href="UnusedCekList.php">
                                <h3>Cek Available</h3>
                                <p><?php echo $unused_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang Sudah di Issued/Tulis" href="IssuedCekList.php">
                                <h3>Cek Issued</h3>
                                <p><?php echo $issued_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang sudah Posted/Cair" href="PostedCekList.php">
                                <h3>Cek Posted</h3>
                                <p><?php echo $Posted_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang sudah Void" href="VoidCekList.php">
                                <h3>Cek Voided</h3>
                                <p><?php echo $void_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang sudah dikembalikan ke Bank" href="ReturnCekList.php">
                                <h3>Cek Returned</h3>
                                <p><?php echo $return_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang akan jatuh tempo dalam kurun waktu 7 hari" href="JTCekList.php">
                                <h3>Cek Due in 7 Days</h3>
                                <p><?php echo $jt_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar Cek yang akan jatuh tempo dalam bulan ini" href="MonthlyDueCekList.php">
                                <h3>Cek Monthly Due</h3>
                                <p><?php echo $monthly_due_cek_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar cek yang sudah lewat jatuh tempo" href="OverDueCekList.php">
                                <h3>Cek Overdue</h3>
                                <p><?php echo $Overdue_cek_count; ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                <div id="loa" class="tab-pane fade">
                    <div class="stats-card">
                        <div class="card">
                            <a title="Daftar LOA yang Available" href="UnusedloaList.php">
                                <h3>LOA Available</h3>
                                <p><?php echo $unused_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang sudah di Issued/Tulis" href="IssuedloaList.php">
                                <h3>LOA Issued</h3>
                                <p><?php echo $issued_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang Posted/cair" href="PostedloaList.php">
                                <h3>LOA Posted</h3>
                                <p><?php echo $Posted_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang sudah Void/Batal" href="VoidloaList.php">
                                <h3>LOA Voided</h3>
                                <p><?php echo $void_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang sudah Return" href="ReturnloaList.php">
                                <h3>LOA Returned</h3>
                                <p><?php echo $return_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang akan Jatuh Tempo dalam kurun waktu 7 hari" href="JTloaList.php">
                                <h3>LOA Due in 7 Days</h3>
                                <p><?php echo $jt_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang akan Jatuh Tempo Bulan ini" href="MonthlyDueloaList.php">
                                <h3>LOA Monthly Due</h3>
                                <p><?php echo $monthly_due_loa_count; ?></p>
                            </a>
                        </div>
                        <div class="card">
                            <a title="Daftar LOA yang sudah lewat Jatuh Tempo" href="OverDueloaList.php">
                                <h3>LOA Overdue</h3>
                                <p><?php echo $Overdue_loa_count; ?></p>
                            </a>
                        </div>
                    </div>
                </div>

                </section>


                <footer>
                    © 2024 Aplikasi Giro. Powered By IT AVENGER.
                </footer>

                <script>
                    document.getElementById('toggleNavbar').addEventListener('click', function() {
                        document.getElementById('navbar').classList.toggle('hide');
                    });


                    document.addEventListener('DOMContentLoaded', function() {
                        const hash = window.location.hash;

                        // Deactivate all tabs and hide all content
                        document.querySelectorAll('.nav-link').forEach(link => {
                            link.classList.remove('active');
                        });
                        document.querySelectorAll('.tab-pane').forEach(tab => {
                            tab.classList.remove('show', 'active');
                        });

                        if (hash) {
                            // If there's a hash, activate the corresponding tab
                            const activeTab = document.querySelector(`.nav-link[href="${hash}"]`);
                            if (activeTab) {
                                activeTab.classList.add('active');

                                // Show the corresponding tab content
                                const tabContent = document.querySelector(hash);
                                if (tabContent) {
                                    tabContent.classList.add('show', 'active');
                                }
                            }
                        } else {
                            // Default to List Giro dan Cek if no hash is present
                            const defaultTab = document.querySelector('.nav-link[href="#listGiroCek"]');
                            if (defaultTab) {
                                defaultTab.classList.add('active');

                                const defaultContent = document.querySelector('#listGiroCek');
                                if (defaultContent) {
                                    defaultContent.classList.add('show', 'active');
                                }
                            }
                        }
                    });
                </script>
                <div id="changelog-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
                <div style="position: relative; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; border-radius: 5px; width: 80%; max-width: 600px;">
                    <h4>Changelog</h4>
                    <ul id="changelog-list"></ul>
                    <button id="close-changelog" style="background-color: #dc3545; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
                        Close
                    </button>
                </div>
                </div>

    </body>
    </html>