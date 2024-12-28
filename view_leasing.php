<?php
include 'koneksi.php';

if (!isset($_GET['id'])) {
    header('Location: leasing.php');
    exit();
}

$id = $_GET['id'];
$sql = "SELECT l.*, 
        dl.due_date, dl.saldo_awal, dl.angsuran_pokok, dl.bunga, dl.outstanding, dl.saldo_akhir, dl.payment_no, dl.status
        FROM leasing l
        LEFT JOIN detail_leasing dl ON l.id = dl.id_leasing
        WHERE l.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: leasing.php');
    exit();
}

$leasing = $result->fetch_assoc();
$details = array();
while ($row = $result->fetch_assoc()) {
    $details[] = $row;
}

// Function to format currency
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 2, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Leasing Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .details-section { margin-bottom: 2rem; }
        .detail-row { margin-bottom: 0.5rem; }
        .label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Leasing Details</h2>
            <div>
                <a href="leasing.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Back</a>
                <a href="edit_leasing.php?id=<?= $id ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit</a>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4>General Information</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-row">
                            <span class="label">Pembayaran Atas:</span>
                            <span><?= htmlspecialchars($leasing['pembayaran_atas']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">COA:</span>
                            <span><?= htmlspecialchars($leasing['coa']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Total Harga:</span>
                            <span><?= formatCurrency($leasing['total_harga']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Tenor:</span>
                            <span><?= htmlspecialchars($leasing['tenor']) ?> months</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="detail-row">
                            <span class="label">DP Rate:</span>
                            <span><?= $leasing['dp%'] ?>%</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Nominal DP:</span>
                            <span><?= formatCurrency($leasing['nominal_dp']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Pokok Hutang:</span>
                            <span><?= formatCurrency($leasing['pokok_hutang']) ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Suku Bunga:</span>
                            <span><?= htmlspecialchars($leasing['suku_bunga']) ?>%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h4>Items</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $items = json_decode($leasing['item'], true);
                        foreach ($items as $item) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($item['name']) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($item['nominal']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h4>Payment Schedule</h4>
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Due Date</th>
                            <th>Saldo Awal</th>
                            <th>Angsuran Pokok</th>
                            <th>Bunga</th>
                            <th>Outstanding</th>
                            <th>Saldo Akhir</th>
                            <th>Payment No</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM detail_leasing WHERE id_leasing = ? ORDER BY due_date";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $id);
                        $stmt->execute();
                        $details = $stmt->get_result();
                        
                        while ($row = $details->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . date('d/m/Y', strtotime($row['due_date'])) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($row['saldo_awal']) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($row['angsuran_pokok']) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($row['bunga']) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($row['outstanding']) . "</td>";
                            echo "<td class='text-end'>" . formatCurrency($row['saldo_akhir']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['payment_no']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($leasing['perjanjian_kredit_file']): ?>
        <div class="mt-4">
            <h4>Documents</h4>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#documentModal">
                <i class="bi bi-file-earmark-text"></i> View Credit Agreement
            </button>
        </div>
        <?php endif; ?>
    </div>

    <!-- Document Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentModalLabel">Credit Agreement Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php
                    $file_extension = strtolower(pathinfo($leasing['perjanjian_kredit_file'], PATHINFO_EXTENSION));
                    $file_path = "imgleasing/perjanjian_kredit/" . htmlspecialchars($leasing['perjanjian_kredit_file']);
                    
                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo "<img src='{$file_path}' class='img-fluid' alt='Credit Agreement'>";
                    } elseif ($file_extension === 'pdf') {
                        echo "<embed src='{$file_path}' type='application/pdf' width='100%' height='600px'>";
                    } else {
                        echo "<div class='alert alert-info'>
                                <i class='bi bi-info-circle'></i> 
                                This document cannot be previewed. 
                                <a href='{$file_path}' class='btn btn-primary btn-sm ms-3' download>
                                    <i class='bi bi-download'></i> Download
                                </a>
                              </div>";
                    }
                    ?>
                </div>
                <div class="modal-footer">
                    <a href="<?= $file_path ?>" class="btn btn-primary" download>
                        <i class="bi bi-download"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add event listener to handle modal open
        document.getElementById('documentModal').addEventListener('show.bs.modal', function (event) {
            // You can add any additional initialization logic here
        });
    </script>
</body>
</html>