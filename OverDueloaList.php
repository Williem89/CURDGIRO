<?php
include 'koneksi.php';

$sql = "SELECT e.nama_entitas, 
               d.namabank, 
               d.ac_number, 
               dg.ac_penerima, 
               dg.nama_penerima, 
               dg.bank_penerima, 
               dg.noloa, 
               SUM(dg.nominal) AS total_nominal, 
               dg.tanggal_jatuh_tempo, 
               dg.PVRNo, 
               dg.keterangan, 
               dg.nominal AS Nominal
        FROM detail_loa AS dg
        INNER JOIN data_loa AS d ON dg.noloa = d.noloa
        INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
        WHERE dg.Statloa = 'Issued' AND dg.tanggal_jatuh_tempo < CURDATE()
        GROUP BY e.nama_entitas, 
                 d.namabank, 
                 d.ac_number, 
                 dg.ac_penerima, 
                 dg.nama_penerima, 
                 dg.bank_penerima, 
                 dg.noloa, 
                 dg.tanggal_jatuh_tempo, 
                 dg.PVRNo, 
                 dg.keterangan
        ORDER BY e.nama_entitas, dg.noloa";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Preparation failed: " . $conn->error);
}

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$issued_loa_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$subtotals = [];
$grand_total = 0;

// Calculate subtotals and grand total
foreach ($issued_loa_records as $loa) {
    $entity = $loa['nama_entitas'];
    $subtotals[$entity] = ($subtotals[$entity] ?? 0) + $loa['total_nominal'];
    $grand_total += $loa['total_nominal'];
}

// Pagination logic
$total_records = count($issued_loa_records);
$records_per_page = 30;
$total_pages = ceil($total_records / $records_per_page);
$current_page = max(1, isset($_GET['page']) ? (int)$_GET['page'] : 1);
$offset = ($current_page - 1) * $records_per_page;
$current_records = array_slice($issued_loa_records, $offset, $records_per_page);

// Start HTML output
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DAFTAR loa OVERDUE</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 30px;
        }
        h1 {
            margin-bottom: 20px;
            color: #0056b3;
        }
        table {
            margin-top: 20px;
            border: 1px solid #dee2e6;
            font-size: 12px;
        }
        th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }
        td {
            background-color: white;
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
    <div class="container">
        <h1 class="text-center">DAFTAR loa OVERDUE</h1>
        <button class="btn btn-success mx-2 ms-2" onclick="generatePDF()">Download PDF</button>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal loa</th>
                        <th>No loa</th>
                        <th>No Rek Asal</th>
                        <th>Bank Asal</th>
                        <th>Rekening Tujuan</th>
                        <th>Atas Nama</th>
                        <th>Bank Tujuan</th>
                        <th>No PVR</th>
                        <th>Keterangan</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($current_records)): ?>
                        <tr>
                            <td colspan="11" class="no-data">Tidak ada data loa.</td>
                        </tr>
                    <?php else: ?>
                        <?php
                        $current_entity = '';
                        $subtotal = 0;
                        $no = $offset + 1;

                        foreach ($current_records as $loa) {
                            if ($current_entity !== $loa['nama_entitas']) {
                                if ($current_entity !== '') {
                                    echo '<tr class="subtotal"><td colspan="10">Subtotal</td><td>' . '$ ' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                                }

                                $subtotal = $loa['total_nominal'];
                                $current_entity = $loa['nama_entitas'];
                                echo '<tr class="group-header"><td colspan="11">' . htmlspecialchars($current_entity) . '</td></tr>';
                            } else {
                                $subtotal += $loa['total_nominal'];
                            }
                        ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo date('d-M-Y', strtotime($loa['tanggal_jatuh_tempo'])); ?></td>
                                <td><?php echo htmlspecialchars($loa['noloa']); ?></td>
                                <td><?php echo htmlspecialchars($loa['ac_number']); ?></td>
                                <td><?php echo htmlspecialchars($loa['namabank']); ?></td>
                                <td><?php echo htmlspecialchars($loa['ac_penerima']); ?></td>
                                <td><?php echo htmlspecialchars($loa['nama_penerima']); ?></td>
                                <td><?php echo htmlspecialchars($loa['bank_penerima']); ?></td>
                                <td><?php echo htmlspecialchars($loa['PVRNo']); ?></td>
                                <td><?php echo htmlspecialchars($loa['keterangan']); ?></td>
                                <td><?php echo '$ ' . number_format($loa['Nominal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if ($current_entity !== ''): ?>
                            <tr class="subtotal">
                                <td colspan="10">Subtotal</td>
                                <td><?php echo '$ ' . number_format($subtotal, 2, ',', '.'); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="grand-total">
                            <td colspan="10">Grand Total</td>
                            <td><?php echo '$ ' . number_format($grand_total, 2, ',', '.'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation">
            <div class="d-flex justify-content-center">
                <ul class="pagination">
                    <?php if ($current_page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?page=1">First</a></li>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page - 1; ?>">Previous</a></li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $current_page - 4);
                    $end_page = min($total_pages, $start_page + 9);

                    if ($current_page <= 5) {
                        $end_page = min(10, $total_pages);
                    }

                    if ($current_page > $total_pages - 5) {
                        $start_page = max(1, $total_pages - 9);
                    }

                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $current_page + 1; ?>">Next</a></li>
                        <li class="page-item"><a class="page-link" href="?page=<?php echo $total_pages; ?>">Last</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>

        <div class="text-center mt-4">
            <a title="isi" href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script>
        const {
            jsPDF
        } = window.jspdf;
        var reportData = <?php echo json_encode($issued_loa_records); ?>;
        let entity = {};

        reportData.forEach(function(data) {
            if (!entity[data.nama_entitas]) {
                entity[data.nama_entitas] = {
                    data: [],
                    subtotal: 0
                };
            }
            entity[data.nama_entitas].data.push(data);
            entity[data.nama_entitas].subtotal += data.Nominal;
        });
        

        function generatePDF() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF('landscape');

            let startY = 10;

            // Iterate over each entity in the reportData
            Object.keys(entity).forEach(entityName => {
                doc.setFontSize(12);
                doc.text(`Entity: ${entityName}`, 10, startY);
                startY += 10;

                const entries = entity[entityName].data.map(loa => [
                    new Date(loa.tanggal_jatuh_tempo).toLocaleDateString(), // Tanggal Jatuh Tempo
                    loa.noloa, // No loa
                    loa.ac_number, // No Rek Asal
                    loa.nama_penerima, // Atas Nama
                    loa.ac_penerima, // Rekening Tujuan
                    loa.namabank, // Bank Asal
                    loa.bank_penerima, // Bank Tujuan
                    loa.PVRNo, // No PVR
                    loa.keterangan, // Keterangan
                    (loa.total_nominal !== null ? Number(loa.total_nominal).toLocaleString('id-ID', {
                        style: 'currency',
                        currency: 'IDR'
                    }) : 'Rp0') // Total Nominal
                ]);

                // Create a table for the entries
                doc.autoTable({
                    head: [
                        ['Tanggal loa', 'No loa', 'No Rek Asal', 'Atas Nama', 'Rekening Tujuan', 'Bank Asal', 'Bank Tujuan', 'No PVR', 'Keterangan', 'Nominal']
                    ],
                    body: entries,
                    startY: startY,
                    margin: { top: 10, bottom: 10, left: 2, right: 2 },
                    columnStyles: {
                        9: {cellWidth: 37} // Nominal
                    }
                });
                
                startY = doc.lastAutoTable.finalY + 10;

                // Add subtotal for the entity
                doc.setFontSize(10);
                doc.text(`Subtotal untuk ${entityName}: ${Number(entity[entityName].subtotal).toLocaleString('id-ID', { style: 'currency', currency: 'IDR' })}`, 10, startY);
                startY += 20;

            });

            // Save the PDF
            const today = new Date();
            const formattedDate = `${today.getDate().toString().padStart(2, '0')}-${(today.getMonth() + 1).toString().padStart(2, '0')}-${today.getFullYear()}`; // Format date as DD-MM-YYYY
            doc.save(`Overdue loa List - ${formattedDate}.pdf`);
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
