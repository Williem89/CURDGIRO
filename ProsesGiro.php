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
        dg.image_giro,
        dg.PVRNo,
        dg.Keterangan,
        dg.a_void,
        dg.nama_penerima,
        dg.ac_penerima,
        dg.bank_penerima
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
        dc.image_giro,
        dc.PVRNo,
        dc.Keterangan,
        dc.a_void,
        dc.nama_penerima,
        dc.ac_penerima,
        dc.bank_penerima
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
        dl.image_giro,
        dl.PVRNo,
        dl.Keterangan,
        dl.a_void,
        dl.nama_penerima,
        dl.ac_penerima,
        dl.bank_penerima
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

    UNION ALL

    SELECT 
        'AutoDebit' AS jenis,
        e.nama_entitas,
        a.namabank,
        a.ac_number,
        ad.Statautodebit AS status,
        ad.noautodebit AS nomor,
        SUM(ad.nominal) AS total_nominal,
        ad.tanggal_jatuh_tempo,
        ad.TglVoid,
        ad.image_autodebit,
        ad.PVRNo,
        ad.Keterangan,
        ad.a_void,
        ad.nama_penerima,
        ad.ac_penerima,
        ad.bank_penerima
    FROM 
        detail_autodebit AS ad
    INNER JOIN 
        data_autodebit AS a ON ad.noautodebit = a.noautodebit
    INNER JOIN 
        list_entitas AS e ON a.id_entitas = e.id_entitas
    WHERE 
        ad.Statautodebit != 'Posted'
        AND (ad.noautodebit LIKE ? OR e.nama_entitas LIKE ? OR a.namabank LIKE ?)
        " . ($selected_type ? "AND a.jenis_autodebit = ?" : "") . "
        " . ($selected_status ? "AND ad.Statautodebit = ?" : "") . "
    GROUP BY 
        e.nama_entitas, a.namabank, a.ac_number, ad.noautodebit, ad.tanggal_jatuh_tempo, ad.TglVoid, ad.image_autodebit

    ORDER BY 
        tanggal_jatuh_tempo ASC

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

$sqlParams[] = '%' . $search_term . '%'; // for noautodebit
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
    <title>Prosess Giro/Cek/LOA</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
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

        .mfp-iframe-holder .mfp-content {
            max-width: 1400px !important;
        }
    </style>

</head>

<body>
    <div class="container">
        <h1 class="text-center">PROSES GIRO / CEK/ LOA</h1>

        <!-- Form Pencarian -->
        <form method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan No Giro, Entitas, atau Bank" value="<?php echo $search_term; ?>">
                <select name="type" class="form-select">
                    <option value="">Pilih Type</option>
                    <option value="Giro" <?php echo ($selected_type == 'Giro') ? 'selected' : ''; ?>>Giro</option>
                    <option value="Cek" <?php echo ($selected_type == 'Cek') ? 'selected' : ''; ?>>Cek</option>
                    <option value="loa" <?php echo ($selected_type == 'loa') ? 'selected' : ''; ?>>Loa</option>
                    <option value="AutoDebit" <?php echo ($selected_type == 'AutoDebit') ? 'selected' : ''; ?>>AutoDebit</option>
                    <option value="Transfer" <?php echo ($selected_type == 'Transfer') ? 'selected' : ''; ?>>Transfer</option>
                </select>
                <select name="status" class="form-select">
                    <option value="">Pilih Status</option>
                    <option value="Void" <?php echo ($selected_status == 'Void') ? 'selected' : ''; ?>>Void</option>
                    <option value="Pending Issued" <?php echo ($selected_status == 'Pending Issued') ? 'selected' : ''; ?>>Pending Issued</option>
                    <option value="Pending Post" <?php echo ($selected_status == 'Pending Post') ? 'selected' : ''; ?>>Pending Post</option>
                    <option value="Pending Void" <?php echo ($selected_status == 'Pending Void') ? 'selected' : ''; ?>>Pending Void</option>
                    <option value="Pending Return" <?php echo ($selected_status == 'Pending Return') ? 'selected' : ''; ?>>Pending Return</option>
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
                    <th>Keterangan</th>
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
                            <td><?php echo $giro['Keterangan']; ?></td>
                            <td><?php echo number_format($giro['total_nominal'], 2); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary cair-btn"
                                    <?php echo ($giro['status'] == "Void" || $giro['status'] == "Pending Issued" || $giro['status'] == "Pending Post" || $giro['status'] == "Pending Void" || $giro['status'] == "Pending Return") ? "hidden" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Post"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-send-check"></i>
                                </button>

                                <button class="btn btn-sm btn-danger void-btn"
                                    <?php echo ($giro['status'] == "Void" || $giro['status'] == "Pending Issued" || $giro['status'] == "Pending Post" || $giro['status'] == "Pending Void" || $giro['status'] == "Pending Return") ? "hidden" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Void"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-x-circle"></i>
                                </button>

                                <button class="btn btn-sm btn-info return-btn"
                                    <?php echo ($giro['status'] == "Issued" || $giro['status'] == "Pending Issued" || $giro['status'] == "Pending Post" || $giro['status'] == "Pending Void" || $giro['status'] == "Pending Return") ? "hidden" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Return"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-backspace"></i>
                                </button>

                                <?php if ($_SESSION['UsrLevel'] == 2): ?>
                                    <button class="btn btn-sm btn-success aprv-btn-issued" <?php echo $giro['status'] != "Pending Issued" ? "hidden" : ""; ?>
                                        data-action="accIssued"
                                        data-toggle="tooltip" data-placement="top" title="Approve Issued"
                                        data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                        data-namabank="<?php echo htmlspecialchars($giro['namabank']); ?>"
                                        data-acnumber="<?php echo htmlspecialchars($giro['ac_number']); ?>"
                                        data-tanggalgiro="<?php echo htmlspecialchars($giro['tanggal_jatuh_tempo']); ?>"
                                        data-nominal="<?php echo htmlspecialchars($giro['total_nominal']); ?>"
                                        data-namapenerima="<?php echo htmlspecialchars($giro['nama_penerima']); ?>"
                                        data-acpenerima="<?php echo htmlspecialchars($giro['ac_penerima']); ?>"
                                        data-bankpenerima="<?php echo htmlspecialchars($giro['bank_penerima']); ?>"
                                        data-grno="<?php echo htmlspecialchars($giro['PVRNo']); ?>"
                                        data-keterangan="<?php echo htmlspecialchars($giro['Keterangan']); ?>"
                                        data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($_SESSION['UsrLevel'] == 2): ?>
                                    <button class="btn btn-sm btn-success aprv-btn-void" <?php echo $giro['status'] != "Pending Void" ? "hidden" : ""; ?>
                                        data-alasan="<?php echo htmlspecialchars($giro['a_void']); ?>"
                                        data-action="accVoid"
                                        data-toggle="tooltip" data-placement="top" title="Approve Void"
                                        data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                        data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($_SESSION['UsrLevel'] == 2): ?>
                                    <button class="btn btn-sm btn-success aprv-btn-return" <?php echo $giro['status'] != "Pending Return" ? "hidden" : ""; ?>
                                        data-action="accReturn"
                                        data-toggle="tooltip" data-placement="top" title="Approve Return"
                                        data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                        data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>

                                <?php if ($_SESSION['UsrLevel'] == 2): ?>
                                    <button class="btn btn-sm btn-success aprv-btn-post" <?php echo $giro['status'] != "Pending Post" ? "hidden" : ""; ?>
                                        data-action="accPost"
                                        data-toggle="tooltip" data-placement="top" title="Approve Post"
                                        data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                        data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                        data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>

                                <button class="btn btn-sm btn-warning edit-btn" <?php echo $giro['status'] != "Pending Issued" ? "hidden" : ""; ?>
                                    data-toggle="tooltip" data-placement="top" title="Edit Data"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-grno="<?php echo htmlspecialchars($giro['PVRNo']); ?>"
                                    data-keterangan="<?php echo htmlspecialchars($giro['Keterangan']); ?>"
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

                                <button class="btn btn-sm btn-secondary add-attachment-btn"
                                    data-toggle="tooltip" data-placement="top" title="Tambahkan Lampiran"
                                    data-nogiro="<?php echo htmlspecialchars($giro['nomor']); ?>"
                                    data-type="<?php echo htmlspecialchars($giro['jenis']); ?>"
                                    data-image="<?php echo htmlspecialchars($giro['image_giro']); ?>"
                                    data-entitas="<?php echo htmlspecialchars($giro['nama_entitas']); ?>">
                                    <i class="bi bi-camera"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="subtotal">
                        <td colspan="9" class="text-end">Subtotal</td>
                        <td><?php echo number_format($subtotal, 2); ?></td>
                        <td></td>
                    </tr>
                    <tr class="grand-total">
                        <td colspan="9" class="text-end">Grand Total</td>
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
                    const nogiro = button.getAttribute('data-nogiro');
                    const entitas = button.getAttribute('data-entitas');
                    const jenis = button.getAttribute('data-type');
                    // const aprvAction = button.getAttribute('data-action');
                    const alasanVoid = button.getAttribute('data-alasan');
                    const grno = button.getAttribute('data-grno');
                    const keterangan = button.getAttribute('data-keterangan');

                    const {
                        value: formValues
                    } = await Swal.fire({
                        title: 'Edit Data',
                        html: '<div class="form-group">' +
                            '<label for="swal-input1" class="form-label">GR No.</label>' +
                            `<input id="swal-input1" class="form-control" rows="3" value="${grno}"></input>` +
                            '</div>' +
                            '<div class="form-group mt-3">' +
                            '<label for="swal-input2" class="form-label">Keterangan</label>' +
                            `<input id="swal-input2" class="form-control" rows="3" value="${keterangan}"></input>` +
                            '</div>',
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Submit',
                        cancelButtonText: 'Cancel'
                    });

                    if (formValues) {
                        const grNo = document.getElementById('swal-input1').value;
                        const keterangan = document.getElementById('swal-input2').value;

                        const formData = new FormData();
                        formData.append('nogiro', nogiro);
                        formData.append('grNo', grNo);
                        formData.append('keterangan', keterangan);
                        formData.append('action', "edit");
                        formData.append('jenis', jenis);
                        fetch('update_statgiro.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: "Data berhasil diproses",
                                        icon: 'success'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Error",
                                        text: data.message,
                                        icon: "error"
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    title: "Error",
                                    text: "An error occurred while updating.",
                                    icon: "error"
                                });
                            });
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
                        type: 'iframe'
                    }).magnificPopup('open');
                });
            });

            function handleButtonClick(selector, action) {
                document.querySelectorAll(selector).forEach(button => {
                    button.addEventListener('click', async () => {
                        const nogiro = button.getAttribute('data-nogiro');
                        const entitas = button.getAttribute('data-entitas');
                        const jenis = button.getAttribute('data-type');
                        // const aprvAction = button.getAttribute('data-action');
                        const alasanVoid = button.getAttribute('data-alasan');
                        const namabank = button.getAttribute('data-namabank');
                        const acnumber = button.getAttribute('data-acnumber');
                        const tanggalgiro = button.getAttribute('data-tanggalgiro');
                        const nominal = button.getAttribute('data-nominal');
                        const namapenerima = button.getAttribute('data-namapenerima');
                        const acpenerima = button.getAttribute('data-acpenerima');
                        const bankpenerima = button.getAttribute('data-bankpenerima');
                        const grno = button.getAttribute('data-grno');
                        const keterangan = button.getAttribute('data-keterangan');

                        console.log(nogiro, entitas, jenis);
                        console.log(action);

                        const {
                            value: formValues
                        } = await Swal.fire({
                            title: action === 'cair' ? "Tanggal Cair" : action === 'return' ? "Tanggal Return" : action === 'acc' ? "Konfirmasi Approve" : action === 'add' ? "Tambah Lampiran" : action === 'void' ? "Tanggal Void" : "Are You Sure?",
                            html: ((action == "cair") ? `<div class="form-group">
                                <label for="swal-input1" class="form-label">Tanggal</label>
                                <input id="swal-input1" class="form-control" type="date" max="<?php echo date('Y-m-d'); ?>">
                                </div>` : '') +
                                (action === 'void' ?
                                    '<div class="form-group mt-3">' +
                                    '<input hidden id="swal-input1" class="form-control" type="text" value="<?php echo date('Y-m-d'); ?>" readonly>' +
                                    '</div>' +
                                    '<div class="form-group mt-3">' +
                                    '<label for="swal-input2" class="form-label">Alasan</label>' +
                                    '<textarea id="swal-input2" class="form-control" placeholder="Masukkan alasan Void" rows="3"></textarea>' +
                                    '</div>' : '') +
                                ((action === 'return' || action === 'add') ? `<div class="form-group mt-3">
                                            <label for="swal-input3" class="form-label">File lampiran</label>
                                            <input id="swal-input3" class="form-control" type="file">
                                            </div>` : '') +
                                (action === 'acc' ? `Apakah Anda yakin ingin approve ${jenis} dengan nomor <b>${nogiro}</b> dari <b>${entitas}</b>?
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Nama Bank :</strong>
                                                <p>${namabank}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">No. Rekening :</strong>
                                                <p>${acnumber}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Tanggal Giro :</strong>
                                                <p>${tanggalgiro}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Nominal :</strong>
                                                <p>${parseFloat(nominal).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Nama Penerima :</strong>
                                                <p>${namapenerima}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">No. Rekening Penerima :</strong>
                                                <p>${acpenerima}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Bank Penerima :</strong>
                                                <p>${bankpenerima}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">PVR No. :</strong>
                                                <p>${grno}</p>
                                            </div>
                                            <div class="form-group mt-3 d-flex justify-content-between">
                                                <strong class="form-label">Keterangan :</strong>
                                                <p>${keterangan}</p>
                                            </div>
                                            
                                            ` : ''),
                            focusConfirm: false,
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            cancelButtonText: 'No',
                            preConfirm: () => {
                                const date = action == 'cair' ? document.getElementById('swal-input1').value : '<?php echo date('Y-m-d'); ?>';

                                // Check if fields are required based on the action
                                if (action !== 'cair' && !date) {
                                    Swal.showValidationMessage('Fields are required');
                                }

                                return {
                                    date: date,
                                    reason: (action === 'void') ? document.getElementById('swal-input2').value : '',
                                    file: (action === 'return' || action === 'add') ? document.getElementById('swal-input3').files[0] : ''
                                };

                            }
                        });

                        if (formValues) {
                            const formData = new FormData();
                            formData.append('nogiro', nogiro);
                            formData.append('tanggal', tanggal); // Use formatted date
                            formData.append('alasan', action === 'void' ? formValues.reason : '');
                            formData.append('statgiro', action === 'cair' ? 'Posted' : action === 'return' ? 'Return' : action === 'void' ? 'Void' : '');
                            if (action === 'return' || action === 'add') {
                                formData.append('file', formValues.file);
                            }
                            formData.append('action', action);
                            formData.append('jenis', jenis);

                            fetch('update_statgiro.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: "Data berhasil diproses",
                                            icon: 'success'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "Error",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: "Error",
                                        text: "An error occurred while updating.",
                                        icon: "error"
                                    });
                                });
                        }
                    });
                });
            }
            handleButtonClick('.aprv-btn-issued', 'acc');
            // handleButtonClick('.aprv-btn-post', 'app');
            handleButtonClick('.aprv-btn-void', 'apv');
            // handleButtonClick('.aprv-btn-return', 'apr');
            handleButtonClick('.cair-btn', 'cair');
            handleButtonClick('.void-btn', 'void');
            handleButtonClick('.return-btn', 'return');
            handleButtonClick('.add-attachment-btn', 'add');
        </script>
    </div>
</body>

</html>