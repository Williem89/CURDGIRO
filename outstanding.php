<?php
include 'koneksi.php';

// Set default date range to today if not provided
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');
$namapenerima = isset($_POST['nama_penerima']) ? $_POST['nama_penerima'] : 'all';


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
        dg.nosurat
    FROM 
        detail_giro AS dg
    INNER JOIN 
        data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        dg.StatGiro = 'Issued' 
        AND dg.tanggal_jatuh_tempo BETWEEN ? AND ?
        AND (? = 'all' OR dg.nama_penerima = ?)
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
        dg.keterangan,
        dg.nosurat

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
        dc.nosurat
    FROM 
        detail_cek AS dc
    INNER JOIN 
        data_cek AS d ON dc.nocek = d.nocek
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        dc.Statcek = 'Issued' 
        AND dc.tanggal_jatuh_tempo BETWEEN ? AND ?
        AND (? = 'all' OR dc.nama_penerima = ?)
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
        dc.keterangan

    UNION ALL

    SELECT 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        ad.ac_penerima, 
        ad.nama_penerima, 
        ad.bank_penerima, 
        ad.noautodebit AS nogiro, 
        SUM(ad.nominal) AS total_nominal, 
        ad.tanggal_jatuh_tempo, 
        ad.PVRNo, 
        ad.keterangan,
        ad.nosurat
    FROM 
        detail_autodebit AS ad
    INNER JOIN 
        data_autodebit AS d ON ad.noautodebit = d.noautodebit
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        ad.Statautodebit = 'Issued' 
        AND ad.tanggal_jatuh_tempo BETWEEN ? AND ?
        AND (? = 'all' OR ad.nama_penerima = ?)
    GROUP BY 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        ad.ac_penerima, 
        ad.nama_penerima, 
        ad.bank_penerima, 
        ad.noautodebit, 
        ad.tanggal_jatuh_tempo, 
        ad.PVRNo, 
        ad.keterangan;
";

$alloutstandingGiroQuery = "
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
        dg.nosurat
    FROM 
        detail_giro AS dg
    INNER JOIN 
        data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        dg.StatGiro = 'Issued' 
        AND dg.tanggal_jatuh_tempo <= ?
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
        dc.nosurat
    FROM 
        detail_cek AS dc
    INNER JOIN 
        data_cek AS d ON dc.nocek = d.nocek
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        dc.Statcek = 'Issued' 
        AND dc.tanggal_jatuh_tempo <= ?
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
        dc.keterangan

    UNION ALL

    SELECT 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        ad.ac_penerima, 
        ad.nama_penerima, 
        ad.bank_penerima, 
        ad.noautodebit AS nogiro, 
        SUM(ad.nominal) AS total_nominal, 
        ad.tanggal_jatuh_tempo, 
        ad.PVRNo, 
        ad.keterangan,
        ad.nosurat
    FROM 
        detail_autodebit AS ad
    INNER JOIN 
        data_autodebit AS d ON ad.noautodebit = d.noautodebit
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        ad.Statautodebit = 'Issued' 
        AND ad.tanggal_jatuh_tempo <= ?
    GROUP BY 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        ad.ac_penerima, 
        ad.nama_penerima, 
        ad.bank_penerima, 
        ad.noautodebit, 
        ad.tanggal_jatuh_tempo, 
        ad.PVRNo, 
        ad.keterangan;
";

$alloutstanding_stmt = $conn->prepare($alloutstandingGiroQuery);
if (!$alloutstanding_stmt) {
    die("Preparation failed: " . $conn->error);
}

$alloutstanding_stmt->bind_param('sss', $end_date, $end_date, $end_date);
$alloutstanding_stmt->execute();
$alloutstanding_result = $alloutstanding_stmt->get_result();

$alloutstanding_data = [];
$report_data2 = [];
while ($row = $alloutstanding_result->fetch_assoc()) {
    $nama_entitas = $row['nama_entitas'] ?? 'Unknown Entity';
    $namabank = $row['namabank'] ?? 'Unknown Bank';
    $ac_number = $row['ac_number'] ?? 'Unknown AC Number';

    if (!isset($report_data2[$nama_entitas][$namabank][$ac_number])) {
        $report_data2[$nama_entitas][$namabank][$ac_number] = [];
    }

    $report_data2[$nama_entitas][$namabank][$ac_number][] = [
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
        'nosurat' => $row['nosurat'] ?? '-',
    ];
}

$alloutstanding_stmt->close();

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

$prefixQuery = "SELECT letter_making_web.TREASURY.prefix, letter_making_web.TREASURY.ket, curdgiro.detail_giro.nogiro
FROM letter_making_web.TREASURY
JOIN curdgiro.detail_giro ON letter_making_web.TREASURY.ket_detail = curdgiro.detail_giro.nogiro;
";

// Connect to the second database
// $conn2 = new mysqli($servername2, $username2, $password2, $dbname2);

// if ($conn2->connect_error) {
//     die("Connection failed: " . $conn2->connect_error);
// }

$dataCoa = "SELECT * FROM coa";
$coa_stmt = $conn2->prepare($dataCoa);
if (!$coa_stmt) {
    die("Preparation failed: " . $conn2->error);
}

$coa_stmt->execute();
$coa_result = $coa_stmt->get_result();

$coa_data = [];
while ($row = $coa_result->fetch_assoc()) {
    $coa_data[] = $row;
}

// Execute prefixQuery
$prefix_stmt = $conn2->prepare($prefixQuery);
if (!$prefix_stmt) {
    die("Preparation failed: " . $conn2->error);
}

$prefix_stmt->execute();
$prefix_result = $prefix_stmt->get_result();

$prefix_data = [];
while ($row = $prefix_result->fetch_assoc()) {
    $prefix_data[] = $row;
}

$prefix_stmt->close();

// Prepare and execute unused giro records query
$stmt = $conn->prepare($unusedGiroQuery);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

$stmt->bind_param('ssssssssssss', $start_date, $end_date, $namapenerima, $namapenerima, $start_date, $end_date, $namapenerima, $namapenerima, $start_date, $end_date, $namapenerima, $namapenerima);
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
        'nosurat' => $row['nosurat'] ?? '-',
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

            // Fetch updtgl
            $updtglQuery = "
                SELECT updtgl
                FROM list_rekening
                WHERE no_akun = ?
            ";
            $updtgl_stmt = $conn->prepare($updtglQuery);
            $updtgl_stmt->bind_param('s', $ac_number);
            if ($updtgl_stmt->execute()) {
                $updtgl_result = $updtgl_stmt->get_result();
                if ($updtgl_row = $updtgl_result->fetch_assoc()) {
                    $updtgl = $updtgl_row['updtgl'] ?? 'Unknown Date';
                }
            }
            $updtgl_stmt->close();


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

// Fetch saldo and total_before_start for each account
foreach ($report_data2 as $nama_entitas => &$banks) {
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

            // Fetch updtgl
            $updtglQuery = "
                SELECT updtgl
                FROM list_rekening
                WHERE no_akun = ?
            ";
            $updtgl_stmt = $conn->prepare($updtglQuery);
            $updtgl_stmt->bind_param('s', $ac_number);
            if ($updtgl_stmt->execute()) {
                $updtgl_result = $updtgl_stmt->get_result();
                if ($updtgl_row = $updtgl_result->fetch_assoc()) {
                    $updtgl = $updtgl_row['updtgl'] ?? 'Unknown Date';
                }
            }
            $updtgl_stmt->close();


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
            $report_data2[$nama_entitas][$namabank][$ac_number]['saldo'] = $saldo;
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
?>

<?php
// echo "<pre>";
// print_r($report_data2);
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <title>GIRO ISSUED</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        .giro-list2 {
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
</head>

<body>


    <div class="row">
        <div class="col">
            <form method="POST" class="mb-4" style="box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); border-radius: 12px; z-index: 1; background-color: white;">
                <div class="row align-items-center" style="margin: 10px; width: 1000px;">
                    <div class="col-md-2" style="width:175px;">
                        <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-2" style="width:175px;">
                        <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <script>
                        document.querySelector('input[name="start_date"]').addEventListener('change', function() {
                            const startDate = new Date(this.value);
                            const endDateInput = document.querySelector('input[name="end_date"]');
                            const endDate = new Date(endDateInput.value);

                            if (startDate > endDate) {
                                endDateInput.value = this.value;
                            }
                        });
                    </script>
                    <div class="col-md-3" style="width:300px;">
                        <select name="nama_penerima" class="form-control">
                            <option value="all" <?php echo ($namapenerima == 'all') ? 'selected' : ''; ?>>Semua Penerima</option>
                            <?php
                            $namaPenerimaQuery = "
                        SELECT DISTINCT nama_penerima 
                        FROM (
                            SELECT nama_penerima FROM detail_giro
                            UNION
                            SELECT nama_penerima FROM detail_cek
                            UNION
                            SELECT nama_penerima FROM detail_autodebit
                        ) AS combined
                        ORDER BY nama_penerima
                    ";
                            $namaPenerimaResult = $conn->query($namaPenerimaQuery);
                            while ($row = $namaPenerimaResult->fetch_assoc()) {
                                $selected = ($row['nama_penerima'] == $namapenerima) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($row['nama_penerima']) . "' $selected>" . htmlspecialchars($row['nama_penerima']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary mt-2" style="margin: 10px; width: 150px; margin-left: 10px;">
                            <i class="bi bi-collection"></i> Tampilkan
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- fetch Oustanding per Bank  -->
        <?php if (empty($report_data)): ?>

        <?php else: ?>
            <div class="row">
                <div class="col">
                </div>
                <div class="col">
                </div>
                <div class="col">
                    <table class="table table-striped" style="border: 1px solid black; width: 600px; border-radius: 10px; overflow: hidden; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);">
                        <thead>
                            <tr>
                                <th style="text-align: center; width: 100px ">Nama Entitas</th>
                                <th style="text-align: center; width: 100px">Bank</th>
                                <th style="text-align: center; width: 100px">AC Number</th>
                                <th style="text-align: center; width: 20px">Jumlah</th>
                                <th style="text-align: center; width: 170px">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data2 as $nama_entitas => &$banks): ?>

                                <?php foreach ($banks as $bank => &$acNumbers): ?>

                                    <?php foreach ($acNumbers as $ac_number => &$giroList): ?>
                                        <?php
                                        $saldo = $giroList['saldo'];

                                        $totalNominal = array_sum(array_column($giroList, 'total_nominal'));
                                        if ($totalNominal > $saldo) {
                                            $warning = 'Saldo tidak cukup!';
                                        } else {
                                            $warning = '<span style="color: green;">Saldo cukup</span>';
                                        }
                                        ?>
                                        <tr class="ac-header" style="text-align: center;" onclick="toggleGiroList('<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>')">
                                            <td style="font-size: 11pt;"><?php echo htmlspecialchars($nama_entitas); ?></td>
                                            <td style="font-size: 11pt;"><?php echo htmlspecialchars($bank); ?></td>
                                            <td style="font-size: 11pt;"><?php echo htmlspecialchars($ac_number); ?></td>
                                            <td style="font-size: 11pt;"><?php echo count($giroList); ?></td>
                                            <td style="font-size: 11pt;"><?php echo 'Rp. ' . number_format($totalNominal, 2, ',', '.'); ?></td>
                                        </tr>


                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    //Whatsapp Message generator
    $currentHour = date('H');
    if ($currentHour < 12) {
        $greeting = "Selamat Pagi";
    } elseif ($currentHour < 15) {
        $greeting = "Selamat Siang";
    } elseif ($currentHour < 18) {
        $greeting = "Selamat Sore";
    } else {
        $greeting = "Selamat Malam";
    }

    // Get today's date and day of the week
    // Get today's date and day of the week
    $daysOfWeek = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];
    $today = $daysOfWeek[date('l')] . ', ' . date('d/m/Y H:i'); // e.g., 'Kamis, 31/10/2024 14:30'

    // Initialize the WhatsApp message
    $whatsappMessage = "$greeting, \n\nBerikut cicilan jatuh tempo sampai dengan hari $today yang belum di bayar :\n\n";

    // Loop through the report data

    foreach ($report_data2 as $nama_entitas => &$banks) {
        // Add entity name
        $whatsappMessage .= "*$nama_entitas* :\n\n";
        foreach ($banks as $namabank => &$acNumbers) {
            foreach ($acNumbers as $ac_number => &$giroList) {

                // Add account details
                $whatsappMessage .= "# TRANSFER/ MENGGUNAKAN GIRO/ CEK " . $namabank . " NO REK " . $ac_number . "\n\n";
                // Group items by due date
                $items_by_date = [];
                foreach ($giroList as $index => &$giro) {
                    if ($index === 'saldo') continue;
                    $jatuh_tempo = date('d/m/Y', strtotime($giro['tanggal_jatuh_tempo']));
                    $items_by_date[$jatuh_tempo][] = $giro;
                }

                // Sort dates from newest to oldest
                ksort($items_by_date);

                // For each due date, list items
                foreach ($items_by_date as $jatuh_tempo => $items) {
                    $whatsappMessage .= "Jatuh tempo tgl $jatuh_tempo :\n\n";
                    foreach ($items as &$giro) {

                        $nominalrupiahf = 'Rp. ' . number_format($giro['total_nominal'], 0, ',', '.') . ',-';
                        // Find the prefix for the current nogiro
                        $prefix = '';
                        foreach ($prefix_data as $prefix_row) {
                            if ($prefix_row['nogiro'] === $giro['nogiro']) {
                                $prefix = $prefix_row['prefix'];
                                break;
                            }
                        }

                        // Build the item message
                        $whatsappMessage .= "- " . $giro['PVRNo'] . ", " . $giro['nama_penerima'] . ", " . $giro['keterangan'] . "\n";
                        $whatsappMessage .= "MENGGUNAKAN GIRO " . $giro['namabank'] . " NO " . $giro['ac_number'] . " " . $giro['nogiro'] . " " . $giro['nosurat'] . " " . $giro['PVRno'] . " " . $nominalrupiahf . "\n\n";
                    }
                }
                // Add saldo and total cicilan information

                $saldo = $giroList['saldo'];

                $total_cicilan = array_sum(array_column($giroList, 'total_nominal'));
                $saldo_formatted = 'Rp. ' . number_format($saldo, 0, ',', '.') . ',-';
                $total_cicilan_formatted = 'Rp. ' . number_format($total_cicilan, 0, ',', '.') . ',-';



                if ($saldo < $total_cicilan) {
                    $difference = $total_cicilan - $saldo;
                    $difference_formatted = 'Rp. ' . number_format($difference, 0, ',', '.') . ',-';
                    $whatsappMessage .= "# Untuk saldo saat ini sebesar $saldo_formatted\n";
                    $whatsappMessage .= "# Total cicilan sebesar $total_cicilan_formatted\n";
                    $whatsappMessage .= "# Untuk saldo saat ini tidak cukup, saldo kurang $difference_formatted\n\n";
                } else {
                    $whatsappMessage .= "# Untuk saldo saat ini sebesar $saldo_formatted\n";
                    $whatsappMessage .= "# Total cicilan sebesar $total_cicilan_formatted\n";
                    $whatsappMessage .= "# Untuk saldo saat ini cukup\n\n";
                }
            }
            // Add separator between banks
            $whatsappMessage .= "----------------------------------------\n\n";
        }
        // Add separator between entity
        $whatsappMessage .= "=======================================\n\n";
    }
    $whatsappMessage .= "Terima kasih";
    ?>



    <script>
        async function exportToWhatsapp() {
            const {
                value: numbers
            } = await Swal.fire({
                title: "Whatsapp Number Destination",
                html: `
                        <div class="d-flex justify-content-between mb-3">
                        <button type="button" class="btn btn-outline-primary" onclick="selectAllNumbers(true)">Select All</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="selectAllNumbers(false)">Deselect All</button>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6285156132721" >
                        <label class="form-check-label text-start" for="6285156132721">User 1</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="628197297908" checked>
                        <label class="form-check-label text-start" for="628197297908">Tester 2</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6282113889526" >
                        <label class="form-check-label text-start" for="6282113889526">User 3</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6283892727583" >
                        <label class="form-check-label text-start" for="6283892727583">Abdul</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6281584321196" >
                        <label class="form-check-label text-start" for="6281584321196">BNL</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6282123349666" >
                        <label class="form-check-label text-start" for="6282123349666">Ibu Putri</label>
                    </div>
                `,
                confirmButtonText: `
                    Continue&nbsp;<i class="fa fa-arrow-right"></i>
                `,
                preConfirm: () => {
                    const numbers = [];
                    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(checkbox => {
                        if (checkbox.checked) {
                            numbers.push(checkbox.id);
                        }
                    });
                    return numbers;
                }
            });
            const message = `<?php echo $whatsappMessage; ?>`;

            const maxMessageLength = 4096; // WhatsApp message length limit

            function splitMessage(message, maxLength) {
                const parts = [];
                let currentPart = '';

                message.split('\n').forEach(line => {
                    if ((currentPart + line).length > maxLength) {
                        parts.push(currentPart);
                        currentPart = '';
                    }
                    currentPart += line + '\n';
                });

                if (currentPart) {
                    parts.push(currentPart);
                }

                return parts;
            }

            const messageParts = splitMessage(message, maxMessageLength);

            messageParts.forEach((part, index) => {
                setTimeout(() => {
                    const url = 'http://115.85.74.82:5678/send-message';
                    const data = {
                        message: part,
                        numbers: numbers
                    };

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok ' + response.statusText);
                            }
                            return response.text();
                        })
                        .then(data => {
                            console.log('Success:', data);
                            Swal.fire("yey", "Message sent", "success");
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                        });
                }, index * 1000); // Delay each message by 1 second
            });
        }
    </script>


    <script>
        function selectAllNumbers(select) {
            const checkboxes = document.querySelectorAll('input[type="checkbox"]')
            checkboxes.forEach(checkbox => {
                checkbox.checked = select
            });
        }
    </script>
    <script>
        function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('landscape');

            // Add Overdue List table

            const today = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const formattedDate = today.toLocaleDateString('id-ID', options);
            const user = "<?php echo $_SESSION['username']; ?>";
            console.log(user); // This will output the username correctly
            doc.setFontSize(10);
            doc.setTextColor(192, 192, 192); // Set text color to grey
            doc.text('Di cetak pada tanggal: ' + formattedDate + ' | Di cetak oleh: ' + user, 14, 10);
            doc.setTextColor(0, 0, 0); // Reset text color to black
            doc.setFontSize(16);
            doc.text('Overdue List', 14, 25);
            const overdueData = <?php echo json_encode($report_data2); ?>;
            const prefixData = <?php echo json_encode($prefix_data); ?>;
            const overdueRows = [];
            console.log(overdueData);
            console.log(prefixData);
            for (const entity in overdueData) {
                for (const bank in overdueData[entity]) {
                    for (const acNumber in overdueData[entity][bank]) {
                        for (const key in overdueData[entity][bank][acNumber]) {
                            const item = overdueData[entity][bank][acNumber][key];
                            if (key !== 'saldo' && new Date(item.tanggal_jatuh_tempo) < new Date().setHours(0, 0, 0, 0)) {
                                overdueRows.push([
                                    item.tanggal_jatuh_tempo,
                                    item.nogiro,
                                    item.ac_number,
                                    item.namabank,
                                    item.ac_penerima,
                                    item.nama_penerima,
                                    item.bank_penerima,
                                    item.PVRNo,
                                    item.keterangan,
                                    new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(item.total_nominal),
                                    prefixData.find(prefixRow => prefixRow.nogiro === item.nogiro)?.prefix || ''
                                ]);
                            }
                        }
                    }
                }
            }
            doc.autoTable({
                head: [
                    ['Tanggal Giro', 'No Giro', 'No Rek Asal', 'Bank Asal', 'No Rek Tujuan', 'Atas Nama', 'Bank Tujuan', 'No PVR', 'Keterangan', 'Nominal', 'Prefix']
                ],
                body: overdueRows,
                startY: 35,
                styles: {
                    fontSize: 7
                },
                columnStyles: {
                    9: {
                        cellWidth: 29
                    } // Set fixed width for the "Nominal" column
                }
            });

            // Add Outstanding List table
            doc.addPage('landscape');
            doc.text('Outstanding List', 14, 22);
            const outstandingRows = [];
            for (const entity in overdueData) {
                for (const bank in overdueData[entity]) {
                    for (const acNumber in overdueData[entity][bank]) {
                        for (const key in overdueData[entity][bank][acNumber]) {
                            const item = overdueData[entity][bank][acNumber][key];
                            if (key !== 'saldo' && new Date(item.tanggal_jatuh_tempo) > new Date().setHours(0, 0, 0, 0)) {
                                outstandingRows.push([
                                    item.tanggal_jatuh_tempo,
                                    item.nogiro,
                                    item.ac_number,
                                    item.namabank,
                                    item.ac_penerima,
                                    item.nama_penerima,
                                    item.bank_penerima,
                                    item.PVRNo,
                                    item.keterangan,
                                    new Intl.NumberFormat('id-ID', {
                                        style: 'currency',
                                        currency: 'IDR'
                                    }).format(item.total_nominal),
                                    prefixData.find(prefixRow => prefixRow.nogiro === item.nogiro)?.prefix || ''
                                ]);
                            }
                        }
                    }
                }
            }
            doc.autoTable({
                head: [
                    ['Tanggal Giro', 'No Giro', 'No Rek Asal', 'Bank Asal', 'No Rek Tujuan', 'Atas Nama', 'Bank Tujuan', 'No PVR', 'Keterangan', 'Nominal', 'Prefix']
                ],
                body: outstandingRows,
                startY: 30,
                styles: {
                    fontSize: 7,
                },
                columnStyles: {
                    9: {
                        cellWidth: 29
                    } // Set fixed width for the "Nominal" column
                }
            });

            // Save the PDF
            const fileName = `Outstanding giro - ${new Date().toISOString().split('T')[0]}.pdf`;
            doc.save(fileName);
            // doc.output('dataurlnewwindow');
        }
    </script>
    <br>
    <div style="color: red;">

        <br>
        <?php

        echo "<h1 style='text-shadow: 2px 2px black;'>Overdue List</h1>";
        echo "per tanggal : " . date('d-M-Y');

        include 'OverDueList.php';
        ?>
        <br><br>
    </div>

    <div class="d-flex">
        <?php
        echo "<h1 style='text-shadow: 2px 2px black;color: green'>Oustanding List</h1>";
        ?>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css">
        <!-- Add the Export PDF button -->

    </div>

    <!-- fetch outstanding data -->
    <?php if (empty($report_data)): ?>
        <p style="text-align: center;">Tidak ada data giro.</p>
    <?php else: ?>

        <table class="table table-striped" style="border-radius: 10px; overflow: hidden; box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);">
            <thead>
                <tr>
                    <th style="text-align: center;">Nama Entitas</th>
                    <th style="text-align: center;">Bank</th>
                    <th style="text-align: center;">AC Number</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: center;">Nominal</th>
                    <th style="text-align: center;">Saldo</th>
                    <th style="text-align: center;">Last Update</th>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($report_data as $nama_entitas => &$banks): ?>
                    <tr class="entity-header">
                        <td colspan="7"><?php echo htmlspecialchars($nama_entitas); ?></td>
                    </tr>
                    <?php foreach ($banks as $bank => &$acNumbers): ?>
                        <tr class="bank-header" style="text-align: center;">
                            <td><br></td>
                            <td><?php echo htmlspecialchars($bank); ?></td>
                            <td><br></td>
                            <td><br></td>
                            <td><br></td>
                            <td><br></td>
                            <td><br></td>
                        </tr>
                        <?php foreach ($acNumbers as $ac_number => &$giroList): ?>
                            <?php
                            $saldo = $giroList['saldo'];

                            $totalNominal = array_sum(array_column($giroList, 'total_nominal'));
                            if ($totalNominal > $saldo) {
                                $warning = '<span style="text-shadow: 1px 1px black;color: red">Saldo tidak cukup !</span>';
                            } else {
                                $warning = '<span style="text-shadow: 1px 1px black;color: green">Saldo cukup</span>';
                            }
                            ?>
                            <tr class="ac-header" style="text-align: center;" onclick="toggleGiroList('<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>')">
                                <td></td>
                                <td></td>
                                <td><?php echo htmlspecialchars($ac_number); ?></td>
                                <td><?php echo count($giroList) - 1; ?></td>
                                <td><?php echo 'Rp. ' . number_format($totalNominal, 2, ',', '.'); ?></td>
                                <td><?php echo 'Rp. ' . number_format($saldo, 2, ',', '.'); ?></td>
                                <td><?php echo date('d-M-Y H:i:s', strtotime($updtgl)); ?></td>
                            </tr>
                            <tr>
                                <td colspan="7" align="center">
                                    <?php if ($warning): ?>
                                        <br><span style="color: red; font-size: 30px;"> <?php echo $warning; ?> </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr class="giro-list" id="<?php echo htmlspecialchars($nama_entitas) . '-' . htmlspecialchars($bank) . '-' . htmlspecialchars($ac_number); ?>">
                                <td colspan="7">
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
                                                <th style="width:150px;text-align:center;">No Surat</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rowIndex = 1; ?>
                                            <?php
                                            $giroListWithoutSaldo = array_filter($giroList, function($key) {
                                                return $key !== 'saldo';
                                            }, ARRAY_FILTER_USE_KEY);

                                            usort($giroListWithoutSaldo, function ($a, $b) {
                                                return strtotime($a['tanggal_jatuh_tempo']) - strtotime($b['tanggal_jatuh_tempo']);
                                            });
                                            ?>
                                            <?php foreach ($giroListWithoutSaldo as $index => &$giro): ?>
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
                                                    <td><?php echo 'Rp. ' . number_format($giro['total_nominal'], 2, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($giro['nosurat']); ?></td>
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
    <!-- Include jsPDF library -->

    <!-- Add the JavaScript function to generate PDF -->

</body>

</html>
<?php
$stmt->close();
$conn->close();
$conn2->close();
$coa_stmt->close();
?>