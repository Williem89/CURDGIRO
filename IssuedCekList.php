<?php
include 'koneksi.php';

$sql = "SELECT e.nama_entitas, 
       d.namabank, 
       d.ac_number, 
       dg.ac_penerima, 
       dg.nama_penerima, 
       dg.bank_penerima, 
       dg.nocek, 
       SUM(dg.nominal) AS total_nominal, 
       dg.tanggal_jatuh_tempo, 
       dg.PVRNo, 
       dg.keterangan, 
       dg.nominal as Nominal
FROM detail_cek AS dg
INNER JOIN data_cek AS d ON dg.nocek = d.nocek
INNER JOIN list_entitas AS e ON d.id_entitas = e.id_entitas
WHERE dg.Statcek='Issued'
GROUP BY e.nama_entitas, 
         d.namabank, 
         d.ac_number, 
         dg.ac_penerima, 
         dg.nama_penerima, 
         dg.bank_penerima, 
         dg.nocek, 
         dg.tanggal_jatuh_tempo, 
         dg.PVRNo, 
         dg.keterangan
ORDER BY e.nama_entitas, nocek;";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

$stmt->execute();
$result = $stmt->get_result();

$issued_cek_records = [];
while ($row = $result->fetch_assoc()) {
    $issued_cek_records[] = $row;
}

$stmt->close();
$conn->close();

$subtotals = [];
$grand_total = 0;

foreach ($issued_cek_records as $cek) {
    $entity = $cek['nama_entitas'];
    if (!isset($subtotals[$entity])) {
        $subtotals[$entity] = 0;
    }
    $subtotals[$entity] += $cek['total_nominal'];
    $grand_total += $cek['total_nominal'];
}
// var_dump($subtotals);

$total_records = count($issued_cek_records);
$records_per_page = 30;
$total_pages = ceil($total_records / $records_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;
$current_records = array_slice($issued_cek_records, $offset, $records_per_page);

$current_entity = '';
$subtotal = 0;
$no = $offset + 1;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar cek Issued</title>
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
    <div class="container" style="width: 100%; max-width:2000px">
        <h1 class="text-center">Daftar cek Issued</h1>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                <th style="width:5px; text-align:center;">No</th>
                <th style="width:90px;text-align:center;">Tanggal cek</th>
                <th style="width:90px; text-align:center;">No cek</th>
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
                <?php if (empty($current_records)): ?>
                    <tr>
                        <td colspan="11" class="no-data">Tidak ada data cek.</td>
                    </tr>
                <?php else: ?>
                    <?php
                    foreach ($current_records as $cek):
                        // Check for new entity
                        if ($current_entity !== $cek['nama_entitas']) {
                            // Output subtotal for the previous entity
                            if ($current_entity !== '') {
                                echo '<tr class="subtotal"><td colspan="10">Subtotal</td><td>' . 'Rp. ' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                            }

                            // Reset subtotal for new entity
                            $subtotal = $cek['total_nominal'];
                            $current_entity = $cek['nama_entitas'];

                            // Output entity header
                            echo '<tr class="group-header"><td colspan="11">' . htmlspecialchars($current_entity) . '</td></tr>';
                        } else {
                            // Accumulate subtotal for the same entity
                            $subtotal += $cek['total_nominal'];
                        }
                    ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date('d-M-Y', strtotime($cek['tanggal_jatuh_tempo'])); ?></td>
                            <td><?php echo htmlspecialchars($cek['nocek']); ?></td>
                            <td><?php echo htmlspecialchars($cek['ac_number']); ?></td>
                            <td><?php echo htmlspecialchars($cek['namabank']); ?></td>
                            <td><?php echo htmlspecialchars($cek['ac_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($cek['nama_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($cek['bank_penerima']); ?></td>
                            <td><?php echo htmlspecialchars($cek['PVRNo']); ?></td>
                            <td><?php echo htmlspecialchars($cek['keterangan']); ?></td>
                            <td><?php echo 'Rp. ' . number_format($cek['Nominal'], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Output subtotal for the last entity -->
                    <?php if ($current_entity !== ''): ?>
                        <tr class="subtotal">
                            <td colspan="10">Subtotal</td>
                            <td><?php echo 'Rp. ' . number_format($subtotals[$current_entity], 2, ',', '.'); ?></td>
                        </tr>
                    <?php endif; ?>

                    <!-- Output grand total -->
                    <tr class="grand-total">
                        <td colspan="10">Grand Total</td>
                        <td><?php echo 'Rp. ' . number_format($grand_total, 2, ',', '.'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

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
            <a href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>