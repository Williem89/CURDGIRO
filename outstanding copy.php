<?php
include 'koneksi.php';

// Set default date range to today if not provided
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

$unusedGiroQuery = "
    SELECT 
    e.nama_entitas, 
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
    dg.nominal AS Nominal
FROM 
    detail_giro AS dg
INNER JOIN 
    data_giro AS d ON dg.nogiro = d.nogiro
INNER JOIN 
    list_entitas AS e ON d.id_entitas = e.id_entitas
WHERE 
    dg.StatGiro = 'Issued' 
    AND dg.tanggal_jatuh_tempo BETWEEN ? AND ?
GROUP BY 
    e.nama_entitas, 
    d.namabank, 
    d.ac_number, 
    dg.ac_penerima, 
    dg.nama_penerima, 
    dg.bank_penerima, 
    dg.nogiro, 
    dg.tanggal_jatuh_tempo, 
    dg.PVRNo, 
    dg.keterangan

UNION ALL 

SELECT 
    e.nama_entitas, 
    d.namabank, 
    d.ac_number, 
    dc.ac_penerima, 
    dc.nama_penerima, 
    dc.bank_penerima, 
    dc.nocek AS nogiro, 
    SUM(dc.nominal) AS total_nominal, 
    dc.tanggal_jatuh_tempo, 
    dc.PVRNo, 
    dc.keterangan, 
    dc.nominal AS Nominal
FROM 
    detail_cek AS dc
INNER JOIN 
    data_cek AS d ON dc.nocek = d.nocek
INNER JOIN 
    list_entitas AS e ON d.id_entitas = e.id_entitas
WHERE 
    dc.Statcek = 'Issued' 
    AND dc.tanggal_jatuh_tempo BETWEEN ? AND ?
GROUP BY 
    e.nama_entitas, 
    d.namabank, 
    d.ac_number, 
    dc.ac_penerima, 
    dc.nama_penerima, 
    dc.bank_penerima, 
    dc.nocek, 
    dc.tanggal_jatuh_tempo, 
    dc.PVRNo, 
    dc.keterangan;
";


$nominalQuery = "
    SELECT SUM(dg.Nominal) AS total_before_start
    FROM detail_giro AS dg
    INNER JOIN data_giro AS d ON dg.nogiro = d.nogiro
    WHERE dg.StatGiro = 'Posted' AND d.ac_number = ? 
          AND dg.tanggal_cair_giro >= '2000-01-01' 
          AND dg.tanggal_cair_giro < CURDATE()
";

$saldoQuery = "
    SELECT Saldo AS Saldo
    FROM list_rekening
    WHERE no_akun = ?
";



// Connect to the second database
$conn2 = new mysqli($servername2, $username2, $password2, $dbname2);

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}

$dataTreasury = "SELECT nogiro FROM treasury WHERE ket IS NOT NULL";
$treasury_stmt = $conn2->prepare($dataTreasury);
if (!$treasury_stmt) {
    die("Preparation failed: " . $conn2->error);
}

$treasury_stmt->execute();
$treasury_result = $treasury_stmt->get_result();

$treasury_data = [];
while ($row = $treasury_result->fetch_assoc()) {
    $treasury_data[] = $row['nogiro'];
}

$treasury_stmt->close();
$conn2->close();


// Prepare and execute unused giro records query
$stmt = $conn->prepare($unusedGiroQuery);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

$stmt->bind_param('ssss', $start_date, $end_date, $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$report_data = [];

while ($row = $result->fetch_assoc()) {
    $nama_entitas = $row['nama_entitas'] ?? 'Unknown Entity';
    $namabank = $row['namabank'] ?? 'Unknown Bank';
    $ac_number = $row['ac_number'] ?? 'Unknown AC Number';

    if (!isset($report_data[$nama_entitas][$namabank][$ac_number])) {
        $report_data[$nama_entitas][$namabank][$ac_number] = [];
    }

    $report_data[$nama_entitas][$namabank][$ac_number][] = [
        'nogiro' => $row['nogiro'] ?? null,
        'total_nominal' => $row['total_nominal'] ?? 0,
        'nominal' => $row['Nominal'] ?? 0,
        'tanggal_jatuh_tempo' => $row['tanggal_jatuh_tempo'] ?? 'Unknown Date',
        'ac_number' => $row['ac_number'] ?? 'Unknown AC Number',
        'namabank' => $row['namabank'] ?? 'Unknown Bank',
        'ac_penerima' => $row['ac_penerima'] ?? 'Unknown Account',
        'nama_penerima' => $row['nama_penerima'] ?? 'Unknown Name',
        'bank_penerima' => $row['bank_penerima'] ?? 'Unknown Bank',
        'PVRNo' => $row['PVRNo'] ?? '',
        'keterangan' => $row['keterangan'] ?? 'No Description',
    ];
}

// Fetch saldo and total_before_start for each account
foreach ($report_data as $nama_entitas => &$banks) {
    foreach ($banks as $namabank => &$acNumbers) {
        foreach ($acNumbers as $ac_number => &$giroList) {
            // Fetch saldo
            $saldo_stmt = $conn->prepare($saldoQuery);
            $saldo_stmt->bind_param('s', $ac_number);
            if ($saldo_stmt->execute()) {
                $saldo_result = $saldo_stmt->get_result();
                if ($saldo_row = $saldo_result->fetch_assoc()) {
                    $saldo = $saldo_row['Saldo'] ?? 0;
                }
            }

            // Fetch total_before_start
            $nominal_stmt = $conn->prepare($nominalQuery);
            $nominal_stmt->bind_param('s', $ac_number);
            if ($nominal_stmt->execute()) {
                $nominal_result = $nominal_stmt->get_result();
                if ($nominal_row = $nominal_result->fetch_assoc()) {
                    $total_before_start = $nominal_row['total_before_start'] ?? 0;
                }
            }

            // Store saldo in report data
            $report_data[$nama_entitas][$namabank][$ac_number]['saldo'] = $saldo;
            $nominal_stmt->close();
            $saldo_stmt->close();
        }
    }
}

// Calculate grand total
$grand_total = array_reduce($report_data, function ($carry, $banks) {
    return $carry + array_reduce($banks, function ($carry, $acNumbers) {
        return $carry + count($acNumbers) - 1; // Subtract 1 for saldo entry
    }, 0);
}, 0);

// Close statements and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <title>GIRO ISSUED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 30px;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-bottom: 20px;
            color: #0056b3;
        }

        table {
            margin-top: 20px;
            border: 1px solid #dee2e6;
            font-size: 12px;
            border-collapse: collapse;
        }

        th {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 10px;
        }

        td {
            background-color: white;
            padding: 10px;
            border: 1px solid #dee2e6;
        }

        .giro-list {
            display: none;
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }

        .group-header {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .subtotal {
            font-weight: bold;
            background-color: #d1ecf1;
        }

        .grand-total {
            font-weight: bold;
            background-color: #c3e6cb;
        }
    </style>
</head>

<body>
<form method="POST" class="mb-4" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border-radius: 12px;">
    <div class="row align-items-center" style="margin: 10px; width: 100%;">
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary mt-2" style="margin: 10px; width: 150px; margin-left: 10px;">
                <i class="bi bi-collection"></i> Tampilkan
            </button>
        </div>
    </div>
</form>

<?php
    $whatsappMessage = "Selamat Pagi, Berikut cicilan jatuh tempo sampai dengan tgl ";
    $today = date('d-M-Y H:i:s');
    

    foreach ($report_data as $nama_entitas => $banks) {
        foreach ($banks as $namabank => $acNumbers) {
            foreach ($acNumbers as $ac_number => $giroList) {
                foreach ($giroList as $index => $giro) {
                    if ($index === 'saldo') continue;
                    $nominalrupiahf = 'Rp. ' . number_format($giro['nominal'], 2, ',', '.');
                    $saldorupiahf = 'Rp. ' . number_format($giroList['saldo'], 2, ',', '.');
                    $whatsappMessage .= $today ." yang belum di bayar \n". $nama_entitas ."\n\nTRANSFER/MENGGUNAKAN GIRO/ CEK BNI GEL NO REK  - ". $giro['ac_number'] . "\n\nJatuh tempo tgl " . date('d-M-Y', strtotime($giro['tanggal_jatuh_tempo'])) . " :\n\nNo Giro/Cek : " . $giro['nogiro'] . "\nNama Penerima : " . $giro['nama_penerima'] . "\nBank Penerima : " . $giro['bank_penerima'] . "\nKet : ". $giro['keterangan'] . "\nDengan Nominal : " . $nominalrupiahf . "\nSaldo saat ini adalah : " . $saldorupiahf . "\n" ;
                }
            }
        }
    }

    $whatsappMessage = urlencode($whatsappMessage);
    ?>

<a href="https://api.whatsapp.com/send/?phone=6282168136016&text=<?php echo $whatsappMessage; ?>" class="btn btn-success">Export Whatsapp</a>

<?php if (empty($report_data)): ?>
    <p style="text-align: center;">Tidak ada data giro.</p>
<?php else: ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nama Entitas</th>
                <th>Bank</th>
                <th>AC Number</th>
                <th>Jumlah</th>
                <th>Nominal</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report_data as $nama_entitas => &$banks): ?>
            <tr class="entity-header">
                <td colspan="6"><?php echo htmlspecialchars($nama_entitas); ?></td>
            </tr>
            <?php foreach ($banks as $bank => &$acNumbers): ?>
                <tr class="bank-header" style="text-align: center;">
                <td><br></td>
                <td><?php echo htmlspecialchars($bank); ?></td>
                <td><br></td>
                <td><br></td>
                <td><br></td>
                <td><br></td>
                </tr>
                <?php foreach ($acNumbers as $ac_number => &$giroList): ?>
                <?php
                $saldo = $giroList['saldo'];
                $totalNominal = array_sum(array_column($giroList, 'total_nominal'));
                $warning = ($totalNominal > $saldo) ? 'Saldo tidak cukup!' : '';
                ?>
                <tr class="ac-header" style="text-align: center;" onclick="toggleGiroList('<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>')">
                    <td></td>
                    <td></td>
                    <td><?php echo htmlspecialchars($ac_number); ?></td>
                    <td><?php echo count($giroList); ?></td>
                    <td><?php echo 'Rp. ' . number_format($totalNominal, 2, ',', '.'); ?></td>
                    <td><?php echo 'Rp. ' . number_format($saldo, 2, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td colspan="6" align="center">
                    <?php if ($warning): ?>
                        <br><span style="color: red; font-size: 30px;"> <?php echo $warning; ?> </span>
                    <?php endif; ?>
                    </td>
                </tr>
                <tr class="giro-list" id="<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>">
                    <td colspan="6">
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th style="width:5px; text-align:center;">No</th>
                            <th style="width:90px;text-align:center;">Tanggal Giro</th>
                            <th style="width:90px; text-align:center;">No Giro</th>
                            <th style="width:110px;text-align:center;">No Rek Asal</th>
                            <th style="width:150px;text-align:center;">Bank Asal</th>
                            <th style="width:170px;text-align:center;">Rekening Tujuan</th>
                            <th style="width:260px;text-align:center;">Atas Nama</th>
                            <th style="width:150px;text-align:center;">Bank Tujuan</th>
                            <th style="width:150px;text-align:center;">No PVR</th>
                            <th style="width:150px;text-align:center;">Keterangan</th>
                            <th style="width:150px;text-align:center;">Nominal</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $rowIndex = 1; ?>
                        <?php foreach ($giroList as $index => &$giro): ?>
                            <?php if ($index === 'saldo') continue; ?>
                            <?php if ($giro['tanggal_jatuh_tempo'] < $start_date || $giro['tanggal_jatuh_tempo'] > $end_date) continue; ?>
                            <tr>
                            <td><?php echo $rowIndex++; ?></td>
                            <td><?php echo date('d-M-Y', strtotime($giro['tanggal_jatuh_tempo'])); ?></td>
                            <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                            <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                            <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                            <td><?php echo htmlspecialchars($giro['ac_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($giro['nama_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($giro['bank_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($giro['PVRNo']); ?></td>
                            <td><?php echo htmlspecialchars($giro['keterangan']); ?></td>
                            <td><?php echo 'Rp. ' . number_format($giro['nominal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<script>
    function toggleGiroList(uniqueId) {
        const giroList = document.getElementById(uniqueId);
        giroList.style.display = giroList.style.display === "none" || giroList.style.display === "" ? "table-row" : "none";
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>