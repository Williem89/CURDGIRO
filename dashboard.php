<?php
include 'koneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}



$username = $_SESSION['username'];
$UserLevel = $_SESSION['UserLevel'];

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

// for fetch data last update data at list_rekening
$lastupd = "SELECT MAX(updtgl) as updtgl FROM list_rekening";
$lastupd_stmt = $conn->prepare($lastupd);
$lastupd_stmt->execute();
$lastupd_result = $lastupd_stmt->get_result();
$lastupd_row = $lastupd_result->fetch_assoc();

// for fetch data last update data at KI
$lastupd_bnl = "SELECT MAX(updtgl) as updtgl FROM bnl";
$lastupd_bnl_stmt = $conn->prepare($lastupd_bnl);
$lastupd_bnl_stmt->execute();
$lastupd_bnl_result = $lastupd_bnl_stmt->get_result();
$lastupd_bnl_row = $lastupd_bnl_result->fetch_assoc();

// for fetch data last update data at Prepost
$lastupd_pfb = "SELECT MAX(updtgl) as updtgl FROM pfb";
$lastupd_pfb_stmt = $conn->prepare($lastupd_pfb);
$lastupd_pfb_stmt->execute();
$lastupd_pfb_result = $lastupd_pfb_stmt->get_result();
$lastupd_pfb_row = $lastupd_pfb_result->fetch_assoc();

$verifiedQuery = "SELECT Verified, verified_at, updtgl FROM list_rekening LIMIT 1";  // You should limit the result to one row (or use an appropriate condition)
$verifiedResult = $conn->query($verifiedQuery);

$verifiedQuery_bnl = "SELECT Verified, verified_at, updtgl FROM bnl LIMIT 1";  // You should limit the result to one row (or use an appropriate condition)
$verifiedResult_bnl = $conn->query($verifiedQuery_bnl);

$verifiedQuery_pfb = "SELECT Verified, verified_at, updtgl FROM pfb LIMIT 1";  // You should limit the result to one row (or use an appropriate condition)
$verifiedResult_pfb = $conn->query($verifiedQuery_pfb);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_verified_status'])) {
    $updateQuery = "UPDATE list_rekening SET Verified = '1', verified_at = NOW()";
    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Verified status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating verified status: " . $conn->error . "');</script>";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_verified_status_bnl'])) {
    $updateQuery = "UPDATE bnl SET Verified = '1', verified_at = NOW()";
    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Verified status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating verified status: " . $conn->error . "');</script>";
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_verified_status_pfb'])) {
    $updateQuery = "UPDATE pfb SET Verified = '1', verified_at = NOW()";
    if ($conn->query($updateQuery) === TRUE) {
        echo "<script>alert('Verified status updated successfully!');</script>";
    } else {
        echo "<script>alert('Error updating verified status: " . $conn->error . "');</script>";
    }
}

if (isset($_POST['generate_excel'])) {
    require 'vendor/autoload.php'; // Ensure you have PhpSpreadsheet installed via Composer

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    if (isset($_POST['report_type']) && $_POST['report_type'] === 'girocek') {
        $sheet->setCellValue('A1', 'No Reference');
        $sql = "
        SELECT 
            nogiro AS no_reference, 
            namabank, 
            ac_number, 
            ac_name, 
            jenis_giro AS jenis 
        FROM 
            data_giro 
        WHERE 
            statusgiro = 'Unused'
        UNION ALL
        SELECT 
            nocek AS no_reference, 
            namabank, 
            ac_number, 
            ac_name, 
            jenis_cek AS jenis 
        FROM 
            data_cek 
        WHERE 
            statuscek = 'Unused'
        ";


        // Set the header row
        $sheet->setCellValue('B1', 'Nama Bank');
        $sheet->setCellValue('C1', 'Account Number');
        $sheet->setCellValue('D1', 'Account Name');
        $sheet->setCellValue('E1', 'Jenis');

        // Set fixed width for columns
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setWidth(20);
        }

        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $rowNumber = 2; // Start from the second row
            while ($row = $result->fetch_assoc()) {
                $sheet->setCellValue('A' . $rowNumber, $row['no_reference']);
                $sheet->setCellValue('B' . $rowNumber, $row['namabank']);
                $sheet->setCellValue('C' . $rowNumber, $row['ac_number']);
                $sheet->setCellValue('D' . $rowNumber, $row['ac_name']);
                $sheet->setCellValue('E' . $rowNumber, $row['jenis']);
                $rowNumber++;
            }
        }

        // Set the headers to download the file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="giro_cek_available_report.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit();
    }

    if (isset($_POST['report_type']) && $_POST['report_type'] === 'giro') {
        $sheet->setCellValue('A1', 'No Giro');
        $sql = "SELECT g.nogiro AS nomor, g.namabank, g.ac_number, g.ac_name, dg.tanggal_jatuh_tempo, dg.Nominal, dg.nama_penerima, dg.bank_penerima, dg.ac_penerima, dg.Keterangan FROM data_giro AS g LEFT JOIN detail_giro AS dg ON g.nogiro = dg.nogiro WHERE dg.StatGiro IN ('Issued','Void')";
    } else if (isset($_POST['report_type']) && $_POST['report_type'] === 'giro') {
        $sheet->setCellValue('A1', 'No Cek');
        $sql = "SELECT c.nocek AS nomor, c.namabank, c.ac_number, c.ac_name, dc.tanggal_jatuh_tempo, dc.Nominal, dc.nama_penerima, dc.bank_penerima,dc.ac_penerima,dc.Keterangan FROM data_cek AS c LEFT JOIN detail_cek AS dc ON c.nocek = dc.nocek WHERE dc.StatCek IN ('Issued','Void')";
    }

    // Set the header row
    $sheet->setCellValue('B1', 'Nama Bank');
    $sheet->setCellValue('C1', 'Account Number');
    $sheet->setCellValue('D1', 'Account Name');
    $sheet->setCellValue('E1', 'Tanggal Jatuh Tempo');
    $sheet->setCellValue('F1', 'Nominal');
    $sheet->setCellValue('G1', 'Nama Penerima');
    $sheet->setCellValue('H1', 'Bank Penerima');
    $sheet->setCellValue('I1', 'Account Penerima');
    $sheet->setCellValue('J1', 'Keterangan');

    // Set fixed width for columns
    foreach (range('A', 'J') as $columnID) {
        $sheet->getColumnDimension($columnID)->setWidth(20);
    }

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $rowNumber = 2; // Start from the second row
        while ($row = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowNumber, $row['nomor']);
            $sheet->setCellValue('B' . $rowNumber, $row['namabank']);
            $sheet->setCellValue('C' . $rowNumber, $row['ac_number']);
            $sheet->setCellValue('D' . $rowNumber, $row['ac_name']);
            $sheet->setCellValue('E' . $rowNumber, $row['tanggal_jatuh_tempo']);
            $sheet->setCellValue('F' . $rowNumber, $row['Nominal']);
            $sheet->setCellValue('G' . $rowNumber, $row['nama_penerima']);
            $sheet->setCellValue('H' . $rowNumber, $row['bank_penerima']);
            $sheet->setCellValue('I' . $rowNumber, $row['ac_penerima']);
            $sheet->setCellValue('J' . $rowNumber, $row['Keterangan']);
            $rowNumber++;
        }
    }

    // Set the headers to download the file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    if (isset($_POST['report_type']) && $_POST['report_type'] === 'giro') {
        header('Content-Disposition: attachment;filename="giro_issued_report.xlsx"');
    } else {
        header('Content-Disposition: attachment;filename="cek_issued_report.xlsx"');
    }
    header('Cache-Control: max-age=0');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit();
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aplikasi Giro</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup/dist/magnific-popup.css">
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
            left: 0;
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
            margin-left: -30px;
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
    <?php if (isset($_SESSION['UsrLevel']) && ($_SESSION['UsrLevel'] == '1' || $_SESSION['UsrLevel'] == '2')): ?>
        <div id="toggleNavbar">☰ Menu</div>
        <nav id="navbar" style="margin-top:50px">
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
                <li><a title="Pre/Post" href="prepost.php#pre_financing">Pre/Post</a></li>
                <?php if ($_SESSION['UsrLevel'] == '2'): ?>
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
                <li><a title="Leasing" href="leasing.php">Leasing</a></li>
                <li><a title="Untuk Mencari GIRO/Cek/LOA " href="Search.php">Search</a></li>
                <li><a href="#">Laporan</a>
                    <div class="dropdown">
                        <a href="summaryall.php" target="_blank">Statistik GIRO CEK LOA</a>
                        <a id="giroissuedreport" href="#">Giro Issued</a>
                        <a id="cekissuedreport" href="#">Cek Issued</a>
                        <a id="girocekavailable" href="#">Giro & Cek Available</a>
                    </div>
                </li>
                <button id="changelog-button" style="position:absolute;bottom:10px; background-color: #007bff; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer; margin-bottom:50px;margin-left:35px;">
                    Show Changelog
                </button>
            </ul>
        </nav>
    <?php endif; ?>

    <header>
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="text-center flex-grow-1">Aplikasi Giro</h2>
            <div style="padding: 15px;">

                <div class="dropdown" style="display: inline-block;">
                    <button style="color:white; font-size:15pt" class="btn btn-transparent dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false"> <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="userMenu">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <section>
        <div class="tabs">
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a title="Menampilkan data GIRO/CEK/LOA yang sudah belum dicairkan" class="nav-link active" data-bs-toggle="tab" href="#listGiroCek">Outstanding Vs Saldo</a>
                </li>
                <li class="nav-item">
                    <a title="Menampilkan data Statistik PRE / POST" class="nav-link" data-bs-toggle="tab" href="#ki">Saldo & Sisa Plafond</a>
                </li>
                <li class="nav-item">
                    <a title="Menampilkan data Statistik PRE / POST" class="nav-link" data-bs-toggle="tab" href="#prepost">PRE / POST</a>
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
                <br>
                <br>
                <?php if (isset($_SESSION['UsrLevel']) && ($_SESSION['UsrLevel'] == '1' || $_SESSION['UsrLevel'] == '2')): ?>
                    <button class="btn btn-danger" title="export to pdf" onclick="generatePDF()" style="font-size: 28px;"><i class="bi bi-file-pdf"></i></button>
                    <a onclick="exportToWhatsapp()" class="btn btn-success" style="margin-left:10px;font-size:28px;"><i class="bi bi-whatsapp"></i> </a>
                <?php endif; ?>
                <h1 class="mt-5" style="text-shadow: 2px 2px black;color: #007bff">Outstanding Vs Saldo</h1>
                <form method="post" class="mb-4">
                    <?php include 'outstanding.php'; ?>
            </div>
            <div id="ki" class="tab-pane fade">
                <?php include 'ki_show.php'; ?>
            </div>
        </div>
        <div id="prepost" class="tab-pane fade">
            <?php if (isset($_SESSION['UsrLevel']) && ($_SESSION['UsrLevel'] == '1' || $_SESSION['UsrLevel'] == '2')): ?>
                <button class="btn btn-danger" title="export to pdf" onclick="generatePDFPrePost()" style="font-size: 28px;"><i class="bi bi-file-pdf"></i></button>
            <?php endif; ?>
            <?php if ($_SESSION['username'] == 'financeview'): ?>
                <a id="verifiedButton" onclick="updateVerifiedStatus_pfb()" class="btn btn-success" style="margin-left:10px;font-size:28px;">
                    <i class="bi bi-patch-check"></i>
                </a>
            <?php endif; ?>
            <script>
                function updateVerifiedStatus_pfb() {
                    const xhr = new XMLHttpRequest();
                    xhr.open("POST", "", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            alert("Verified status updated successfully!");
                            location.reload(); // Reload the page to reflect changes
                        }
                    };
                    xhr.send("update_verified_status_pfb=1");
                }
            </script>
            <div>
                <p></p>
                <!-- <?php
                        if ($verifiedResult_pfb === false) {
                            echo "<span style='color: red; font-weight: bold;'>Error executing query: " . $conn->error . "</span>";
                        } else if ($verifiedResult_pfb->num_rows > 0) {
                            // Fetch the result as an associative array
                            $row = $verifiedResult_pfb->fetch_assoc();
                            $verified = $row['Verified'];  // Get the value of 'Verified'
                            // Check the value of 'Verified' and display accordingly
                            if ($verified == 1) {
                                echo "<br><span style='color: green; font-weight: bold;'>Verified At: " . date('d-M-Y H:i:s', strtotime($row['verified_at'])) . "</span>";
                            } else if ($verified == 0) {
                                echo "<span style='color: red; font-weight: bold;'>Unverified</span>";
                            } else {
                                echo "<span style='color: gray; font-weight: bold;'>Unknown Status</span>";
                            }
                        } else {
                            // Handle the case where no rows were returned
                            echo "<span style='color: gray; font-weight: bold;'>No data found</span>";
                        }
                        ?> -->
                <P>Last Update Saldo At :
                    <?php
                    echo date('d-M-Y H:i:s', strtotime($lastupd_pfb_row['updtgl']));
                    ?>
                </p>
            </div>
            <br>
            <br>
            <form method="post" class="mb-4">
                <?php
                include 'prepost_show.php';
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
        <div>
            © 2024 Aplikasi Giro. Powered By IT AVENGER.
        </div>
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

        document.getElementById('cekissuedreport').addEventListener('click', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="generate_excel" value="1">';
            form.innerHTML += '<input type="hidden" name="report_type" value="cek">';
            document.body.appendChild(form);
            form.submit();
        });

        document.getElementById('giroissuedreport').addEventListener('click', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="generate_excel" value="1">';
            form.innerHTML += '<input type="hidden" name="report_type" value="giro">';
            document.body.appendChild(form);
            form.submit();
        });

        document.getElementById('girocekavailable').addEventListener('click', function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="generate_excel" value="1">';
            form.innerHTML += '<input type="hidden" name="report_type" value="girocek">';
            document.body.appendChild(form);
            form.submit();
        });
    </script>
    <div id="changelog-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5);">
        <div style="position: relative; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; border-radius: 5px; width: 80%; max-width: 600px; max-height: 80%; overflow-y: auto;">
            <h4>Changelog</h4>
            <ul id="changelog-list"></ul>
            <button id="close-changelog" style="background-color: #dc3545; color: #fff; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">
                Close
            </button>
        </div>
    </div>
</body>

</html>