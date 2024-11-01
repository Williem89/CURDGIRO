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

//  $telp = "SELECT no_telp FROM list_contact";
//     $telp_stmt = $conn->prepare($telp);

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

$prefixQuery = "SELECT letter_making_web.TREASURY.prefix, curdgiro_beta.detail_giro.nogiro
FROM letter_making_web.TREASURY
JOIN curdgiro_beta.detail_giro ON letter_making_web.TREASURY.ket = curdgiro_beta.detail_giro.nogiro;
";

// Connect to the second database
$conn2 = new mysqli($servername2, $username2, $password2, $dbname2);

if ($conn2->connect_error) {
    die("Connection failed: " . $conn2->connect_error);
}

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

// Calculate grand total
$grand_total = array_reduce($report_data, function ($carry, $banks) {
    return $carry + array_reduce($banks, function ($carry, $acNumbers) {
        return $carry + count($acNumbers) - 1; // Subtract 1 for saldo entry
    }, 0);
}, 0);

// Close statements and connection

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
    // Fetch additional data from conn2 where ket == nogiro in conn1
    // foreach ($report_data as $nama_entitas => &$banks) {
    //     foreach ($banks as $namabank => &$acNumbers) {
    //         foreach ($acNumbers as $ac_number => &$giroList) {
    //             foreach ($giroList as $index => &$giro) {
    //                 if ($index === 'saldo') continue;

    //                 // Prepare and execute query to fetch additional data from conn2
    //                 // $additionalDataQuery = "SELECT * FROM additional_table WHERE ket = ?";
    //                 // $additional_stmt = $conn2->prepare($additionalDataQuery);
    //                 // $additional_stmt->bind_param('s', $giro['nogiro']);
    //                 // $additional_stmt->execute();
    //                 // $additional_result = $additional_stmt->get_result();

    //                 // // Fetch and store additional data
    //                 // if ($additional_row = $additional_result->fetch_assoc()) {
    //                 //     $giro['additional_data'] = $additional_row;
    //                 // }

    //                 // $additional_stmt->close();
    //             }
    //         }
    //     }
    // }
    ?>


    <?php
    //Whatsapp Message generator
    $currentHour = date('H');
    if ($currentHour < 12) {
        $greeting = "Selamat Pagi";
    } elseif ($currentHour < 18) {
        $greeting = "Selamat Siang";
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
    
    foreach ($report_data as $nama_entitas => &$banks) {
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
                
                // For each due date, list items
                foreach ($items_by_date as $jatuh_tempo => $items) {
                    $whatsappMessage .= "Jatuh tempo tgl $jatuh_tempo :\n\n";
                    foreach ($items as &$giro) {
                        
                        $nominalrupiahf = 'Rp. ' . number_format($giro['nominal'], 0, ',', '.') . ',-';
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
                        $whatsappMessage .= "MENGGUNAKAN GIRO " . $giro['namabank'] . " NO " . $giro['ac_number'] . " " . $giro['nogiro'] . " " . $prefix . " " . $nominalrupiahf . "\n\n";
                    }
                }
                // Add saldo and total cicilan information
                
                $saldo = $giroList['saldo'];
                
                $total_cicilan = array_sum(array_column($giroList, 'nominal'));
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
        }
    }
    $whatsappMessage .= "Terima kasih";
    ?>

    <a onclick="exportToWhatsapp()" class="btn btn-success">Export Whatsapp</a>
    <script>
        async function exportToWhatsapp() {
            const message = `<?php echo $whatsappMessage; ?>`;

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
                        <input class="form-check-input" type="checkbox" id="6285156132721" checked>
                        <label class="form-check-label text-start" for="6285156132721">User 1</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="628197297908" checked>
                        <label class="form-check-label text-start" for="628197297908">User 2</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="6282113889526" checked>
                        <label class="form-check-label text-start" for="6282113889526">User 3</label>
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
            if (numbers) {
                const url = 'http://115.85.74.82:5678/send-message';
                const data = {
                    message: message,
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
            }
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
                    <th>Last Update</th>

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
                                $warning = 'Saldo tidak cukup!';
                            } else {
                                $warning = '<span style="color: green;">Saldo cukup</span>';
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
                                                <th>prefix</th>
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
                                                    <td><?php
                                                        $prefix = '';
                                                        foreach ($prefix_data as $prefix_row) {
                                                            if ($prefix_row['nogiro'] === $giro['nogiro']) {
                                                                $prefix = $prefix_row['prefix'];
                                                                break;
                                                            }
                                                        }
                                                        echo htmlspecialchars($prefix);
                                                        ?></td>
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
<?php
$stmt->close();
$conn->close();
$coa_stmt->close();
$conn2->close();
?>