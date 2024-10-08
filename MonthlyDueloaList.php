<?php
include 'koneksi.php'; // Ensure the database connection is correct

require 'vendor/autoload.php'; // Import PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Initialize an empty array to store the due cheques
$due_cheques = [];

// Get the selected month and year or default to current
$selected_month = isset($_POST['month']) ? intval($_POST['month']) : date('n');
$selected_year = isset($_POST['year']) ? intval($_POST['year']) : date('Y');

// Query for fetching the cheques due in the selected month and year
$sql = "SELECT d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.noloa, 
               SUM(dg.Nominal) AS total_nominal, dg.tanggal_jatuh_tempo, dg.PVRNo, dg.keterangan 
        FROM detail_loa AS dg
        INNER JOIN data_loa AS d ON dg.noloa = d.noloa
        WHERE dg.Statloa = 'Issued' 
        AND MONTH(dg.tanggal_jatuh_tempo) = $selected_month 
        AND YEAR(dg.tanggal_jatuh_tempo) = $selected_year
        GROUP BY dg.tanggal_jatuh_tempo, d.namabank, d.ac_name, dg.ac_penerima, dg.nama_penerima, dg.noloa, dg.PVRNo, dg.keterangan
        ORDER BY dg.tanggal_jatuh_tempo ASC;";

$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $due_cheques[] = $row;
    }
} else {
    echo "Error: " . $conn->error;
}

// Close the connection
$conn->close();

// Function to export data to Excel
function exportToExcel($due_cheques, $selected_month, $selected_year)
{
    try {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header
        $sheet->setCellValue('A1', 'Tanggal Jatuh Tempo');
        $sheet->setCellValue('B1', 'No. loa');
        $sheet->setCellValue('C1', 'Pemegang');
        $sheet->setCellValue('D1', 'Nama Penerima');
        $sheet->setCellValue('E1', 'Ak. Penerima');
        $sheet->setCellValue('F1', 'Bank Penerima');
        $sheet->setCellValue('G1', 'No PVR');
        $sheet->setCellValue('H1', 'Keterangan');
        $sheet->setCellValue('I1', 'Jumlah');

        $row = 2; // Start from the second row

        // Loop through due cheques and populate Excel
        foreach ($due_cheques as $cheque) {
            $sheet->setCellValue("A$row", $cheque['tanggal_jatuh_tempo']);
            $sheet->setCellValue("B$row", $cheque['noloa']);
            $sheet->setCellValue("C$row", $cheque['ac_name']);
            $sheet->setCellValue("D$row", $cheque['nama_penerima']);
            $sheet->setCellValue("E$row", $cheque['ac_penerima']);
            $sheet->setCellValue("F$row", $cheque['namabank']);
            $sheet->setCellValue("G$row", $cheque['PVRNo']); // Ensure this matches your database column
            $sheet->setCellValue("H$row", $cheque['keterangan']);
            $sheet->setCellValue("I$row", $cheque['total_nominal']);
            $row++;
        }

        // Save Excel file
        $filename = "loa_Jatuh_Tempo_{$selected_month}_{$selected_year}.xlsx";
        $writer = new Xlsx($spreadsheet);

        // Output to browser
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $writer->save('php://output');
        exit;
    } catch (Exception $e) {
        echo 'Error exporting to Excel: ' . $e->getMessage();
    }
}

// Check if export button was clicked
if (isset($_POST['export'])) {
    exportToExcel($due_cheques, $selected_month, $selected_year);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>loa Jatuh Tempo Bulan Ini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            padding: 30px;
            font-family: Arial, sans-serif;
        }

        header {
            background-color: #0056b3;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .btn-back {
            margin-top: 20px;
            display: inline-block;
            background-color: #0056b3;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-back:hover {
            background-color: #004494;
        }

        .section-title {
            font-weight: bold;
            font-size: 1.1em;
            background-color: #e9ecef;
        }

        @media print {
            header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 50px;
                background-color: #f8f9fa;
                text-align: center;
                line-height: 50px;
            }

            body {
                margin-top: 60px;
                /* Adjust for header space */
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>loa Jatuh Tempo <?php echo htmlspecialchars(date('F Y', mktime(0, 0, 0, $selected_month, 1, $selected_year))); ?></h1>
    </header>

    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <select name="month" class="form-select">
                    <?php
                    $current_month = date('n');
                    for ($m = 1; $m <= 12; $m++) {
                        echo '<option value="' . $m . '"' . ($current_month == $m ? ' selected' : '') . '>' . date('F', mktime(0, 0, 0, $m, 1)) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <select name="year" class="form-select">
                    <?php
                    $current_year = date('Y');
                    for ($y = $current_year - 5; $y <= $current_year + 5; $y++) {
                        echo '<option value="' . $y . '"' . ($current_year == $y ? ' selected' : '') . '>' . $y . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Tampilkan</button>
        <button type="submit" name="export" class="btn btn-success mt-2">Ekspor ke Excel</button>

    </form>
    <button id="pdfexport" class="btn btn-success mt-2">Ekspor ke PDF</button>

    <div class="container table-container mt-4" id="contentExport">
        <h2 class="mb-4">Daftar loa yang Jatuh Tempo</h2>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>No. loa</th>
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
                                <td colspan="9" class="section-title">
                                    <h4><?php echo htmlspecialchars(date('d-m-Y', strtotime($date))); ?></h4>
                                </td>
                            </tr>
                            <?php foreach ($banks as $bank_name => $bank_info): ?>
                                <tr>
                                    <td colspan="9" class="section-title"><strong><?php echo htmlspecialchars($bank_name); ?></strong></td>
                                </tr>
                                <?php foreach ($bank_info['entries'] as $cheque): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date('d-m-Y', strtotime($cheque['tanggal_jatuh_tempo']))); ?></td>
                                        <td><?php echo htmlspecialchars($cheque['noloa']); ?></td>
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
                            <td colspan="9" class="text-center">Tidak ada loa yang jatuh tempo bulan ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
    <a href="dashboard.php" class="btn-back">Kembali</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
        const {
            jsPDF
        } = window.jspdf;
        const doc = new jsPDF();
        var dueCheques = <?php echo json_encode($due_cheques); ?>;
        let bankData;
        console.log(dueCheques);

        function organizeCheques(dueCheques) {
            bankData = {};
            dueCheques.forEach(cheque => {
                const dateKey = cheque.tanggal_jatuh_tempo;
                const bankKey = cheque.namabank;

                if (!bankData[dateKey]) {
                    bankData[dateKey] = {};
                }
                if (!bankData[dateKey][bankKey]) {
                    bankData[dateKey][bankKey] = {
                        entries: [],
                        subtotal: 0
                    };
                }

                bankData[dateKey][bankKey].entries.push(cheque);
                bankData[dateKey][bankKey].subtotal += Number(cheque.total_nominal);
            });
            return bankData;
        }

        // Generate PDF using jsPDF and autoTable
        function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();
            const bankData = organizeCheques(dueCheques);

            let startY = 10;

            Object.keys(bankData).forEach(date => {
                doc.setFontSize(12);
                doc.text(`Date: ${new Date(date).toLocaleDateString()}`, 10, startY);
                startY += 5;

                Object.keys(bankData[date]).forEach(bankName => {
                    doc.text(`Bank Name: ${bankName}`, 10, startY);
                    startY += 5;

                    const entries = bankData[date][bankName].entries.map(cheque => [
                        new Date(cheque.tanggal_jatuh_tempo).toLocaleDateString(),
                        cheque.noloa,
                        cheque.ac_name,
                        cheque.nama_penerima,
                        cheque.ac_penerima,
                        cheque.namabank,
                        cheque.PVRNo,
                        cheque.keterangan,
                        (cheque.total_nominal !== null ? Number(cheque.total_nominal).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' }) : 'Rp0')
                    ]);

                    doc.autoTable({
                        head: [
                            ['Tanggal Jatuh Tempo', 'No loa', 'AC Name', 'Nama Penerima', 'AC Penerima', 'Nama Bank', 'PVR No.', 'Keterangan', 'Total Nominal']
                        ],
                        body: entries,
                        startY
                    });

                    startY = doc.lastAutoTable.finalY + 5;
                    doc.text(`Total untuk ${bankName}: ${Number(bankData[date][bankName].subtotal).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}`, 50, startY);
                    startY += 10;
                });

                startY += 10;
            });

            doc.save('cheques.pdf');
        }
        document.getElementById('pdfexport').addEventListener('click', function() {
            console.log("clikced")
            generatePDF();

            // const element = document.getElementById('contentExport');
            // const opt = {
            //     margin: 0.5,
            //     filename: 'document.pdf',
            //     html2canvas: {
            //         scale: 2,
            //         backgroundColor: '#ffffff'
            //     },
            //     jsPDF: {
            //         unit: 'cm',
            //         format: 'a4',
            //         orientation: 'landscape'
            //     }
            // };

            // // Generate the PDF
            // html2pdf().from(element).set(opt).save();
        });
    </script>
</body>

</html>