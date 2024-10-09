<?php
include 'koneksi.php';
session_start();

// Pastikan pengguna terautentikasi
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Ambil parameter pencarian dari permintaan GET
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_type = isset($_GET['type']) ? trim($_GET['type']) : '';
$selected_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Validasi dan sanitasi istilah pencarian
$search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

// Siapkan kueri SQL
$sql = "
    SELECT 
        d.jenis_giro AS jenis, 
        e.nama_entitas, 
        d.namabank, 
        d.ac_number, 
        dg.StatGiro, 
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
        AND dg.StatGiro != 'Posted'
";

if ($selected_type) {
    $sql .= " AND d.jenis_giro = ?";
}

if ($selected_status) {
    $sql .= " AND dg.StatGiro = ?";
}

$sql .= "
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
        c.jenis_cek AS jenis, 
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
        AND dc.StatCek != 'Posted'
";

if ($selected_type) {
    $sql .= " AND c.jenis_cek = ?";
}

if ($selected_status) {
    $sql .= " AND dc.StatCek = ?";
}

$sql .= "
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

// Siapkan parameter untuk binding
$sqlParams = [];

// Parameter untuk detail_giro
$search_like = '%' . $search_term . '%';
$sqlParams[] = $search_like;  // Parameter 1
$sqlParams[] = $search_like;  // Parameter 2
$sqlParams[] = $search_like;  // Parameter 3

if ($selected_type) {
    $sqlParams[] = $selected_type;  // Parameter 4
}

if ($selected_status) {
    $sqlParams[] = $selected_status;  // Parameter 5
}

// Parameter untuk detail_cek
$sqlParams[] = $search_like;  // Parameter 6
$sqlParams[] = $search_like;  // Parameter 7
$sqlParams[] = $search_like;  // Parameter 8

if ($selected_type) {
    $sqlParams[] = $selected_type;  // Parameter 9
}

if ($selected_status) {
    $sqlParams[] = $selected_status;  // Parameter 10
}

// Siapkan statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Preparation failed: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

// Buat string tipe
$types = str_repeat('s', count($sqlParams)); // 's' untuk string

// Bind parameter
$stmt->bind_param($types, ...$sqlParams);

// Eksekusi statement
$stmt->execute();
$result = $stmt->get_result();

// Inisialisasi array untuk menyimpan record
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}

// Tutup statement dan koneksi
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
            font-family: Arial, sans-serif;
            /* Menggunakan font yang lebih modern */
            font-size: 14px;
            /* Ukuran font lebih kecil */
            line-height: 1.5;
            /* Jarak antar baris yang lebih baik */
        }

        h1 {
            margin-bottom: 20px;
            color: #0056b3;
            font-size: 1.75rem;
            /* Ukuran font h1 lebih kecil */
        }

        table {
            margin-top: 20px;
            border-collapse: collapse;
            /* Menghilangkan jarak antar border */
            width: 100%;
            /* Membuat tabel responsif */
        }

        th,
        td {
            padding: 12px;
            /* Memberikan padding yang lebih baik */
            border: 1px solid #dee2e6;
            /* Border pada cell tabel */
        }

        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            /* Menebalkan teks header */
        }

        td {
            background-color: white;
            color: #343a40;
            /* Warna teks lebih gelap untuk kontras yang lebih baik */
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

        button {
            margin-right: 5px;
            /* Memberikan jarak antar tombol */
        }
    </style>

</head>

<body>
    <div class="container">
        <h1 class="text-center">Daftar Giro Issued</h1>

        <!-- Form Pencarian -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro, Entitas, atau Bank" value="<?php echo $search_term; ?>">
                <select name="type" class="form-select">
                    <option value="">Pilih Type</option>
                    <option value="Giro" <?php echo ($selected_type == 'Giro') ? 'selected' : ''; ?>>Giro</option>
                    <option value="Cek" <?php echo ($selected_type == 'Cek') ? 'selected' : ''; ?>>Cek</option>
                </select>
                <select name="status" class="form-select">
                    <option value="">Pilih Status</option>
                    <option value="Void" <?php echo ($selected_status == 'Void') ? 'selected' : ''; ?>>Void</option>
                    <option value="Pending" <?php echo ($selected_status == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="Issued" <?php echo ($selected_status == 'Issued') ? 'selected' : ''; ?>>Issued</option>
                </select>
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>
        <div class="text-left mt-4">
            <a href="index.php" class="btn btn-primary">Kembali ke Halaman Utama</a>
        </div>

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
                        <td colspan="10" class="no-data">Tidak ada data giro.</td>
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
                    ?>
                        <tr>
                            <td><?php echo $giro['jenis']; ?></td>
                            <td><?php echo $giro['nama_entitas']; ?></td>
                            <td><?php echo $giro['nogiro'] ?: $giro['nocek']; ?></td>
                            <td><?php echo $giro['StatGiro'] ?: $giro['StatCek']; ?></td>
                            <td><?php echo $giro['tanggal_jatuh_tempo']; ?></td>
                            <td><?php echo $giro['TglVoid']; ?></td>
                            <td><?php echo $giro['namabank']; ?></td>
                            <td><?php echo $giro['ac_number']; ?></td>
                            <td><?php echo number_format($giro['total_nominal'], 2); ?></td>
                            <td>
                                <!--
                            <button class="btn btn-sm btn-primary edit-btn" <?php echo $giro['StatGiro'] == "Issued" ? "disabled" : ""; ?> 
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-send-check"></i>
                            </button>
                            <button class="btn btn-sm btn-primary aprv-btn" <?php echo $giro['StatGiro'] == "Issued" ? "disabled" : ""; ?> 
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>" 
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                <i class="bi bi-send-check"></i>
                            </button>
                            -->
                                <button class="btn btn-sm btn-primary cair-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-send-check"></i>
                                </button>

                                <button class="btn btn-sm btn-danger void-btn" <?php echo $giro['StatGiro'] == "Void" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-x-circle"></i>
                                </button>

                                <button class="btn btn-sm btn-info return-btn" <?php echo $giro['StatGiro'] == "Issued" ? "disabled" : ""; ?>
                                    data-nogiro="<?php echo htmlspecialchars($giro['nogiro']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-backspace"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal">
                        <td colspan="8" class="text-end">Subtotal</td>
                        <td><?php echo number_format($subtotal, 2); ?></td>
                        <td></td>
                    </tr>
                    <tr class="grand-total">
                        <td colspan="8" class="text-end">Grand Total</td>
                        <td><?php echo number_format($grand_total, 2); ?></td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            function handleButtonClick(selector, action) {
                document.querySelectorAll(selector).forEach(button => {
                    button.addEventListener('click', async () => {
                        const nogiro = button.getAttribute('data-nogiro');
                        const entitas = button.getAttribute('data-entitas');

                        const {value: formValues} = await Swal.fire({
                            title: action === 'cair' ? "Tanggal Cair" : action === 'return' ? "Tanggal Return" : "Tanggal Void",
                            html: '<div class="form-group">' +
                                '<label for="swal-input1" class="form-label">Tanggal</label>' +
                                '<input id="swal-input1" class="form-control" type="date">' +
                                '</div>' +
                                (action === 'void' ? 
                                '<div class="form-group mt-3">' +
                                '<label for="swal-input2" class="form-label">Alasan</label>' +
                                '<textarea id="swal-input2" class="form-control" placeholder="Masukkan alasan Void" rows="3"></textarea>' +
                                '</div>' : ''),
                            focusConfirm: false,
                            showCancelButton: true,
                            confirmButtonText: 'Submit',
                            cancelButtonText: 'Cancel',
                            preConfirm: () => {
                                const date = document.getElementById('swal-input1').value;
                                if (!date) {
                                    Swal.showValidationMessage('Both fields are required');
                                }
                                return {
                                    date: date,
                                    reason: action === 'void' ? document.getElementById('swal-input2').value : ''
                                };
                            }
                        });

                        if (formValues) {
                            fetch('update_statgiro.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify({
                                        nogiro: nogiro,
                                        tanggal: formValues.date,
                                        alasan: action === 'void' ? formValues.reason : '',
                                        statgiro: action === 'cair' ? 'Posted' : action === 'return' ? 'Return' : 'Void',
                                        action: action + "giro"
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire(action === 'cair' ? "Giro Berhasil di Posting" : action === 'return' ? "Giro Sudah tercatat kembali ke Bank" : "Giro berhasil di void").then(() => {
                                            location.reload(); // Refresh the page
                                        });
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
            handleButtonClick('.edit-btn', 'edit');
            handleButtonClick('.aprv-btn', 'approve');
            handleButtonClick('.cair-btn', 'cair');
            handleButtonClick('.void-btn', 'void');
            handleButtonClick('.return-btn', 'return');
        </script>
    </div>
</body>

</html>