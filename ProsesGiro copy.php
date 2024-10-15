<?php
include 'koneksi.php';
session_start();

// Pastikan pengguna terautentikasi
if (!isset($_SESSION['username'])) {
    header('Location: login.html');
    exit();
}

// Ambil parameter pencarian dari permintaan GET
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$selected_type = isset($_GET['type']) ? trim($_GET['type']) : '';
$selected_status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Validasi dan sanitasi istilah pencarian
$search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8');

// Prepare SQL query
$sql = "
    SELECT 
        'Giro' AS jenis,
        e.nama_entitas,
        d.namabank,
        d.ac_number,
        dg.StatGiro AS status,
        dg.nogiro AS nomor,
        SUM(dg.Nominal) AS total_nominal,
        dg.tanggal_jatuh_tempo,
        dg.TglVoid,
        dg.image_giro
    FROM 
        detail_giro AS dg
    INNER JOIN 
        data_giro AS d ON dg.nogiro = d.nogiro
    INNER JOIN 
        list_entitas AS e ON d.id_entitas = e.id_entitas
    WHERE 
        dg.StatGiro != 'Posted'
        AND (dg.nogiro LIKE ? OR e.nama_entitas LIKE ? OR d.namabank LIKE ?)
        " . ($selected_type ? "AND d.jenis_giro = ?" : "") . "
        " . ($selected_status ? "AND dg.StatGiro = ?" : "") . "
    GROUP BY 
        e.nama_entitas, d.namabank, d.ac_number, dg.nogiro, dg.tanggal_jatuh_tempo, dg.TglVoid, dg.image_giro

    UNION ALL

    SELECT 
        'Cek' AS jenis,
        e.nama_entitas,
        c.namabank,
        c.ac_number,
        dc.StatCek AS status,
        dc.nocek AS nomor,
        SUM(dc.nominal) AS total_nominal,
        dc.tanggal_jatuh_tempo,
        dc.TglVoid,
        dc.image_giro
    FROM 
        detail_cek AS dc
    INNER JOIN 
        data_cek AS c ON dc.nocek = c.nocek
    INNER JOIN 
        list_entitas AS e ON c.id_entitas = e.id_entitas
    WHERE 
        dc.StatCek != 'Posted'
        AND (dc.nocek LIKE ? OR e.nama_entitas LIKE ? OR c.namabank LIKE ?)
        " . ($selected_type ? "AND c.jenis_cek = ?" : "") . "
        " . ($selected_status ? "AND dc.StatCek = ?" : "") . "
    GROUP BY 
        e.nama_entitas, c.namabank, c.ac_number, dc.nocek, dc.tanggal_jatuh_tempo, dc.TglVoid, dc.image_giro

    UNION ALL

    SELECT 
        'Loa' AS jenis,
        e.nama_entitas,
        l.namabank,
        l.ac_number,
        dl.StatLoa AS status,
        dl.noloa AS nomor,
        SUM(dl.nominal) AS total_nominal,
        dl.tanggal_jatuh_tempo,
        dl.TglVoid,
        dl.image_giro
    FROM 
        detail_loa AS dl
    INNER JOIN 
        data_loa AS l ON dl.noloa = l.noloa
    INNER JOIN 
        list_entitas AS e ON l.id_entitas = e.id_entitas
    WHERE 
        dl.StatLoa != 'Posted'
        AND (dl.noloa LIKE ? OR e.nama_entitas LIKE ? OR l.namabank LIKE ?)
        " . ($selected_type ? "AND l.jenis_loa = ?" : "") . "
        " . ($selected_status ? "AND dl.StatLoa = ?" : "") . "
    GROUP BY 
        e.nama_entitas, l.namabank, l.ac_number, dl.noloa, dl.tanggal_jatuh_tempo, dl.TglVoid, dl.image_giro

    ORDER BY 
        tanggal_jatuh_tempo ASC;
";


// Prepare parameters for binding
$sqlParams = [];
$sqlParams[] = '%' . $search_term . '%'; // for nogiro
$sqlParams[] = '%' . $search_term . '%'; // for nama_entitas
$sqlParams[] = '%' . $search_term . '%'; // for namabank

if ($selected_type) {
    $sqlParams[] = $selected_type; // Parameter for type
}
if ($selected_status) {
    $sqlParams[] = $selected_status; // Parameter for status
}

// Adjusting the parameters for the other parts of the query
$sqlParams[] = '%' . $search_term . '%'; // for nocek
$sqlParams[] = '%' . $search_term . '%'; // for nama_entitas
$sqlParams[] = '%' . $search_term . '%'; // for namabank

if ($selected_type) {
    $sqlParams[] = $selected_type; // Parameter for type
}
if ($selected_status) {
    $sqlParams[] = $selected_status; // Parameter for status
}

$sqlParams[] = '%' . $search_term . '%'; // for noloa
$sqlParams[] = '%' . $search_term . '%'; // for nama_entitas
$sqlParams[] = '%' . $search_term . '%'; // for namabank

if ($selected_type) {
    $sqlParams[] = $selected_type; // Parameter for type
}
if ($selected_status) {
    $sqlParams[] = $selected_status; // Parameter for status
}

// Siapkan statement
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Preparation failed: " . htmlspecialchars($conn->error, ENT_QUOTES, 'UTF-8'));
}

// Determine types for binding
$types = str_repeat('s', count($sqlParams)); // 's' for string
$stmt->bind_param($types, ...$sqlParams);


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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/magnific-popup/dist/jquery.magnific-popup.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/magnific-popup/dist/magnific-popup.css">
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
        <h1 class="text-center">Daftar Data Issued</h1>

        <!-- Form Pencarian -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro, Entitas, atau Bank" value="<?php echo $search_term; ?>">
                <select name="type" class="form-select">
                    <option value="">Pilih Type</option>
                    <option value="Giro" <?php echo ($selected_type == 'Giro') ? 'selected' : ''; ?>>Giro</option>
                    <option value="Cek" <?php echo ($selected_type == 'Cek') ? 'selected' : ''; ?>>Cek</option>
                    <option value="loa" <?php echo ($selected_type == 'loa') ? 'selected' : ''; ?>>Loa</option>
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
                            <td><?php echo $giro['nomor']; ?></td>
                            <td><?php echo $giro['status']; ?></td>
                            <td><?php echo $giro['tanggal_jatuh_tempo']; ?></td>
                            <td><?php echo $giro['TglVoid']; ?></td>
                            <td><?php echo $giro['namabank']; ?></td>
                            <td><?php echo $giro['ac_number']; ?></td>
                            <td><?php echo number_format($giro['total_nominal'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary cair-btn" <?php echo $giro['status'] == "Void" ? "disabled" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Post"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-send-check"></i>
                                </button>

                                <button class="btn btn-sm btn-danger void-btn" <?php echo $giro['status'] == "Void" ? "disabled" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Void"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-x-circle"></i>
                                </button>

                                <button class="btn btn-sm btn-info return-btn" <?php echo $giro['status'] == "Issued" ? "disabled" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Return"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-backspace"></i>
                                </button>

                                <?php if ($_SESSION['UsrLevel'] == 2): ?>
                                    <button class="btn btn-sm btn-success aprv-btn" <?php echo $giro['status'] != "Pending" ? "disabled" : ""; ?>
                                        data-toggle="tooltip" data-placement="top" title="Approve"
                                        data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                        data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-warning edit-btn"
                                    data-toggle="tooltip" data-placement="top" title="Edit Data"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button class="btn btn-sm btn-secondary view-attachment-btn"
                                    data-toggle="tooltip" data-placement="top" title="Tampilkan Lampiran"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-image="<?php echo htmlspecialchars($giro['image_giro']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-paperclip"></i>
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
            const options = {
                timeZone: 'Asia/Bangkok', // GMT+7
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false // 24-hour format
            };

            const tanggal = new Date().toLocaleString('sv-SE', options).replace(' ', 'T');



            $(function() {
                $('[data-toggle="tooltip"]').tooltip()
            })
            document.querySelectorAll('.edit-btn').forEach(button => {
                button.addEventListener('click', async () => {
                    const {
                        value: formValues
                    } = await Swal.fire({
                        title: 'Edit Data',
                        html: '<div class="form-group">' +
                            '<label for="swal-input1" class="form-label">PVR No.</label>' +
                            '<input id="swal-input1" class="form-control" rows="3"></input>' +
                            '</div>' +
                            '<div class="form-group mt-3">' +
                            '<label for="swal-input2" class="form-label">Keterangan</label>' +
                            '<input id="swal-input2" class="form-control" rows="3"></input>' +
                            '</div>',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (formValues) {
                        Swal.fire('Submitted', formValues, 'success');
                    }
                });
            });

            document.querySelectorAll('.view-attachment-btn').forEach(button => {
                const imageGiro = button.getAttribute('data-image');

                button.addEventListener('click', () => {
                    console.log(imageGiro);
                    $(button).magnificPopup({
                        items: {
                            src: "imggiro/" + imageGiro,
                        },
                        type: 'image'
                    }).magnificPopup('open');
                });
            });

            function handleButtonClick(selector, action) {
                document.querySelectorAll(selector).forEach(button => {
                    button.addEventListener('click', async () => {
                        const nogiro = button.getAttribute('data-nogiro');
                        const entitas = button.getAttribute('data-entitas');
                        const jenis = button.getAttribute('data-type');

                        console.log(nogiro, entitas, jenis);

                        const {
                            value: formValues
                        } = await Swal.fire({
                            title: action === 'cair' ? "Tanggal Cair" : action === 'return' ? "Tanggal Return" : "Tanggal Void",
                            html: '<div class="form-group">' +
                                '<label for="swal-input1" class="form-label">Tanggal</label>' +
                                '<input id="swal-input1" class="form-control" type="date" max="${today}">' +
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
                                        tanggal: tanggal, // Use formatted date
                                        alasan: action === 'void' ? formValues.reason : '',
                                        statgiro: action === 'cair' ? 'Posted' : action === 'return' ? 'Return' : 'Void',
                                        action: action,
                                        jenis: jenis
                                    })
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire(action === 'cair' ?
                                            (jenis === 'Giro' ? "Giro Berhasil di Posting" : jenis === 'Cek' ? "Cek Berhasil di Posting" : "Loa Berhasil di Posting") :
                                            action === 'return' ?
                                            (jenis === 'Giro' ? "Giro Sudah tercatat kembali ke Bank" : jenis === 'Cek' ? "Cek Sudah tercatat kembali ke Bank" : "Loa Sudah tercatat kembali ke Bank") :
                                            (jenis === 'Giro' ? "Giro berhasil di void" : jenis === 'Cek' ? "Cek berhasil di void" : "Loa berhasil di void")
                                        ).then(() => {
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
            handleButtonClick('.aprv-btn', 'acc');
            handleButtonClick('.cair-btn', 'cair');
            handleButtonClick('.void-btn', 'void');
            handleButtonClick('.return-btn', 'return');
        </script>
    </div>
</body>

</html>