<?php
include 'koneksi.php';
session_start();

// Ensure user is authenticated
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the search term, selected month, and selected year from the GET request
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Prepare the SQL statement for both Giro and Cek
$sql = "
    SELECT 
        d.jenis_giro, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        dg.StatGiro , 
        dg.nogiro, 
        SUM(dg.Nominal) AS total_nominal, 
        dg.tanggal_jatuh_tempo, 
        dg.TglVoid 
    FROM 
        detail_giro AS dg
    INNER JOIN 
        data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        (dg.nogiro LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?)
        AND MONTH(dg.tanggal_jatuh_tempo) = ? 
        AND YEAR(dg.tanggal_jatuh_tempo) = ? 
        AND dg.StatGiro != 'Posted'  
    GROUP BY 
        dg.tanggal_jatuh_tempo, 
        d.jenis_giro, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        dg.nogiro, 
        dg.TglVoid

    UNION ALL

    SELECT 
        c.jenis_cek, 
        e.nama_entitas, 
        c.namabank, 
        c.ac_number, 
        dc.StatCek,
        dc.nocek, 

        SUM(dc.nominal) AS total_nominal, 
        dc.tanggal_jatuh_tempo, 
        dc.TglVoid 
    FROM 
        detail_cek AS dc
    INNER JOIN 
        data_cek AS c ON dc.nocek = c.nocek
    INNER JOIN 
        list_entitas AS e ON c.id_entitas = e.id_entitas
    WHERE 
        (dc.nocek LIKE ? OR e.nama_entitas LIKE ? OR c.namabank LIKE ?)
        AND MONTH(dc.tanggal_jatuh_tempo) = ? 
        AND YEAR(dc.tanggal_jatuh_tempo) = ? 
        AND dc.StatCek != 'Posted'
    GROUP BY 
        dc.tanggal_jatuh_tempo, 
        c.jenis_cek, 
        e.nama_entitas, 
        c.namabank, 
        c.ac_number, 
        dc.nocek, 
        dc.TglVoid

    ORDER BY 
        tanggal_jatuh_tempo ASC;
";


$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Preparation failed: " . $conn->error);
}

// Bind parameters
$search_like = '%' . $search_term . '%';
$stmt->bind_param("ssiiiissii", $search_like, $search_like, $search_like, $selected_month, $selected_year, $search_like, $search_like, $search_like, $selected_month, $selected_year);

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Initialize an array to hold records
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Giro Void</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
        }
        th {
            background-color: #007bff;
            color: white;
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
        <h1 class="text-center">Daftar Giro Issued</h1>

        <!-- Search Form -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro, Entitas, atau Bank" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <select name="month" class="form-select">
                        <option value="">Pilih Bulan</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php echo ($m == $selected_month) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col">
                    <select name="year" class="form-select">
                        <option value="">Pilih Tahun</option>
                        <?php for ($y = date('Y') - 5; $y <= date('Y') + 5; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($y == $selected_year) ? 'selected' : ''; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Entitas</th>
                    <th>No</th>
                    <th>Status</th>
                    <th>Tanggal Jatuh Tempo</th>
                    <th>Tanggal Cair</th>
                    <th>Bank</th>
                    <th>No. Rekening</th>
                    <th>Nominal</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($records)): ?>
                <tr>
                    <td colspan="9" class="no-data">Tidak ada data giro.</td>
                </tr>
            <?php else: ?>
                <?php 
                $current_entity = '';
                $current_bank = '';
                $subtotal = 0;
                $grand_total = 0;

                foreach ($records as $giro): 
                    $subtotal += $giro['total_nominal'];
                    $grand_total += $giro['total_nominal'];

                    if ($current_entity !== $giro['nama_entitas']) {
                        if ($current_entity !== '') {
                            echo '<tr class="subtotal"><td colspan="7">Subtotal</td><td>' . number_format($subtotal, 2, ',', '.') . '</td></tr>';
                        }

                        $subtotal = $giro['total_nominal'];
                        $current_entity = $giro['nama_entitas'];

                        echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_entity) . '</td></tr>';
                    }

                    if ($current_bank !== $giro['namabank']) {
                        $current_bank = $giro['namabank'];
                        echo '<tr class="group-header"><td colspan="7">' . htmlspecialchars($current_bank) . '</td></tr>';
                    }
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($giro['jenis_giro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['nama_entitas']); ?></td>
                        <td><?php echo htmlspecialchars($giro['nogiro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['StatGiro']); ?></td>
                        <td><?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?></td>
                        <td><?php echo htmlspecialchars($giro['TglVoid']); ?></td>
                        <td><?php echo htmlspecialchars($giro['namabank']); ?></td>
                        <td><?php echo htmlspecialchars($giro['ac_number']); ?></td>
                        <td><?php echo number_format($giro['total_nominal'], 2, ',', '.'); ?></td>
                        <td <?php echo $giro['StatGiro'] == "Posted" ? "hidden" : ""; ?>>
                            <button class="btn btn-sm btn-primary cair-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?> 
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-send-check"></i>
                            </button>
                            <button class="btn btn-sm btn-info return-btn" <?php echo $giro['StatGiro'] == "Issued" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-backspace"></i>
                            </button>
                            <button class="btn btn-sm btn-danger void-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="subtotal"><td colspan="7">Subtotal</td><td><?php echo number_format($subtotal, 2, ',', '.'); ?></td></tr>
                <tr class="grand-total"><td colspan="7">Grand Total</td><td><?php echo number_format($grand_total, 2, ',', '.'); ?></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function handleButtonClick(selector, action) {
            document.querySelectorAll(selector).forEach(button => {
                button.addEventListener('click', async () => {
                    const nogiro = button.getAttribute('data-nogiro');
                    const entitas = button.getAttribute('data-entitas');
                    
                    const { value: date } = await Swal.fire({
                        title: action === 'cair' ? "Tanggal Cair" : action === 'return' ? "Tanggal Return" : "Tanggal Void",
                        input: "date",
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (date) {
                        fetch('update_statgiro.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                nogiro: nogiro,
                                tanggal: date,
                                statgiro: action === 'cair' ? 'Posted' : action === 'return' ? 'Return' : 'Void',
                                action: action + "giro"
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(action === 'cair' ? "Giro Berhasil di Posting" : action === 'return' ? "Giro Sudah tercatat kembali ke Bank" : "Giro Void");
                            } else {
                                Swal.fire("Error", data.message, "error");
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire("Error", "An error occurred while updating.", "error");
                        });
                    }
                });
            });
        }

        handleButtonClick('.cair-btn', 'cair');
        handleButtonClick('.return-btn', 'return');
        handleButtonClick('.void-btn', 'void');
    </script>
</body>
</html>
