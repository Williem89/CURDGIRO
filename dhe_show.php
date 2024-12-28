<?php
include 'koneksi.php';

$id = $_GET['id'];

// Fetch transaction details
$query = "SELECT 
    dt.*,
    le.nama_entitas
    FROM dhe_transactions dt
    LEFT JOIN list_entitas le ON dt.id_entitas = le.id_entitas
    WHERE dt.id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaction = mysqli_fetch_assoc($result);

function formatCurrency($number)
{
    return number_format($number, 2, ',', '.');
}

function formatDate($date)
{
    return date('d-m-Y', strtotime($date));
}

function formatPindahDana($jsonString)
{
    $pindah_dana = json_decode($jsonString, true);
    if (!$pindah_dana) return '-';

    $formatted = array_map(function ($item) {
        return formatDate($item['tgl_pd']) . ': ' . formatCurrency($item['nominal_pd']);
    }, $pindah_dana);

    return implode('<br>', $formatted);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>DHE Transaction Details</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        .container {
            margin-top: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
        }

        .card-body {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4">DHE Transaction Details</h1>
        <div class="card">
            <div class="card-header">
                <h3>Transaction Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Entitas:</strong> <?= htmlspecialchars($transaction['nama_entitas']) ?></p>
                        <p><strong>No. Rekening:</strong> <?= htmlspecialchars($transaction['no_rekening']) ?></p>
                        <p><strong>Tipe Transaksi:</strong> <?= htmlspecialchars($transaction['transaksi']) ?></p>
                        <p><strong>Tanggal UM:</strong> <?= formatDate($transaction['tgl_um']) ?></p>
                        <p><strong>Nominal UM:</strong> <?= formatCurrency($transaction['nominal_um']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>70%:</strong> <?= formatCurrency($transaction['porsi70_um']) ?></p>
                        <p><strong>30%:</strong> <?= formatCurrency($transaction['porsi30_um']) ?></p>
                        <p><strong>Margin Deposit:</strong> <?= formatCurrency($transaction['margin_deposit']) ?></p>
                        <p><strong>Nominal UK:</strong> <?= formatCurrency($transaction['nominal_uk']) ?></p>
                        <p><strong>Created:</strong> <?= formatDate($transaction['created_at']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>Pindah Dana</h3>
            </div>
            <div class="card-body">
                <ul>
                    <?php foreach (json_decode($transaction['pindah_dana'], true) as $item) : ?>
                        <li><?= formatDate($item['tgl_pd']) ?>: <?= formatCurrency($item['nominal_pd']) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3>Additional Information</h3>
            </div>
            <div class="card-body">
                <p><strong>Project:</strong> <?= htmlspecialchars($transaction['project']) ?></p>
                <p><strong>Keterangan:</strong> <?= htmlspecialchars($transaction['keterangan']) ?></p>
            </div>
        </div>
        <div class="mb-3">
            <a href="dhe_list.php" class="btn btn-primary">Back to List</a>
            <?php if ($transaction['status'] == 0) { ?>
                <button class="btn btn-primary" onclick="editData(<?= $transaction['id'] ?>, <?= $transaction['porsi30_um'] ?>, <?= $transaction['status'] ?>)">Edit</button>
            <?php } else if ($transaction['status'] == 1) { ?>
                <button class="btn btn-primary" onclick="editData2(<?= $transaction['id'] ?>, <?= $transaction['porsi30_um'] ?>, <?= $transaction['status'] ?>)">Edit2</button>
            <?php } else if ($transaction['status'] == 2) { ?>
                <button class="btn btn-primary" onclick="editData3(<?= $transaction['id'] ?>, <?= $transaction['porsi30_um'] ?>, <?= $transaction['status'] ?>)">Edit3</button>
            <?php } ?>
            <button class="btn btn-secondary" onclick="editAdditionalInfo(<?= $transaction['id'] ?>)">Edit Additional Info</button>
        </div>
    </div>

    <script>
        function editData(id, porsi30, status) {
            Swal.fire({
                title: 'Edit Data',
                html: `
                    <select id="transaksi" class="swal2-input" onchange="updateMaxNominal(${porsi30})">
                        <option value="BTB">BTB</option>
                        <option value="FX">FX</option>
                    </select>
                    <label>Tanggal</label>
                    <input type="date" id="tgl_uk" class="swal2-input" placeholder="Tanggal SWAP">
                    <label>Jatuh Tempo</label>
                    <input type="date" id="tgl_jt" class="swal2-input" placeholder="Tanggal SWAP">
                    <input type="number" id="nominal_uk" class="swal2-input" placeholder="Nominal" oninput="checkNominal(${porsi30})">
                    <div id="status" class="mt-2"></div>
                    <div id="max-value" class="mt-2"></div>
                    <div id="margindeposit" class="mt-2"></div>
                    <div class="text-muted mt-2">Margin BTB: 10%, Margin FX: 6%</div>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const transaksi = document.getElementById('transaksi').value;
                    const tgl_uk = document.getElementById('tgl_uk').value;
                    const tgl_jt = document.getElementById('tgl_jt').value;
                    const nominal_uk = document.getElementById('nominal_uk').value;
                    return {
                        transaksi,
                        tgl_uk,
                        tgl_jt,
                        nominal_uk
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const transaksi = result.value.transaksi;
                    const marginPercentage = transaksi === 'BTB' ? 0.10 : 0.06;
                    const marginDeposit = porsi30 * marginPercentage;
                    const maxNominalUk = porsi30 - marginDeposit;

                    if (result.value.nominal_uk > maxNominalUk) {
                        Swal.fire('Error!', `Nominal UK cannot be more than ${maxNominalUk.toFixed(2)}`, 'error');
                        return;
                    }

                    $.ajax({
                        url: 'update_dhe.php',
                        type: 'POST',
                        data: {
                            id: id,
                            transaksi: result.value.transaksi,
                            tgl_uk: result.value.tgl_uk,
                            tgl_jt: result.value.tgl_jt,
                            nominal_uk: result.value.nominal_uk,
                            marginDeposit: marginDeposit,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire('Saved!', '', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an error saving the data.', 'error');
                        }
                    });
                }
            });

            // Initialize max value display
            updateMaxNominal(porsi30);
        }

        function editData2(id, porsi30, status) {
            Swal.fire({
                title: 'Edit Data',
                html: `
                    <label>Tanggal pembayaran FX SWAP</label>
                    <input type="date" id="tgl_pembayaran_uk" class="swal2-input" placeholder="Tanggal SWAP">
                    <div id="status" class="mt-2"></div>
                    <div id="max-value" class="mt-2"></div>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const tgl_pembayaran_uk = document.getElementById('tgl_pembayaran_uk').value;
                    return {
                        tgl_pembayaran_uk
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'update_dhe.php',
                        type: 'POST',
                        data: {
                            id: id,
                            tgl_pembayaran_uk: result.value.tgl_pembayaran_uk,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire('Saved!', '', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an error saving the data.', 'error');
                        }
                    });
                }
            });
        }

        function editData3(id, porsi30, status) {
            Swal.fire({
                title: 'Edit Data',
                html: `
                    <label>Tanggal pindah dana 30%</label>
                    <input type="date" id="tgl_pindahdana30" class="swal2-input" placeholder="Tanggal SWAP">
                    <div id="status" class="mt-2"></div>
                    <div id="max-value" class="mt-2"></div>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const tgl_pindahdana30 = document.getElementById('tgl_pindahdana30').value;
                    return {
                        tgl_pindahdana30
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'update_dhe.php',
                        type: 'POST',
                        data: {
                            id: id,
                            tgl_pindahdana30: result.value.tgl_pindahdana30,
                            status: status
                        },
                        success: function(response) {
                            Swal.fire('Saved!', '', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an error saving the data.', 'error');
                        }
                    });
                }
            });
        }

        function editAdditionalInfo(id) {
            Swal.fire({
                title: 'Edit Additional Info',
                html: `
                    <label>Project</label>
                    <input type="text" id="project" class="swal2-input" placeholder="Project" value="<?= htmlspecialchars($transaction['project']) ?>">
                    <label>Keterangan</label>
                    <textarea id="keterangan" class="swal2-textarea" placeholder="Keterangan" style="height: 150px;"><?= htmlspecialchars($transaction['keterangan']) ?></textarea>
                `,
                focusConfirm: false,
                preConfirm: () => {
                    const project = document.getElementById('project').value;
                    const keterangan = document.getElementById('keterangan').value;
                    return {
                        project,
                        keterangan
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'update_dhe.php',
                        type: 'POST',
                        data: {
                            id: id,
                            project: result.value.project,
                            keterangan: result.value.keterangan
                        },
                        success: function(response) {
                            Swal.fire('Saved!', '', 'success').then(() => {
                                location.reload();
                            });
                        },
                        error: function() {
                            Swal.fire('Error!', 'There was an error saving the data.', 'error');
                        }
                    });
                }
            });
        }

        function updateMaxNominal(porsi30) {
            const transaksi = document.getElementById('transaksi').value;
            const marginPercentage = transaksi === 'BTB' ? 0.10 : 0.06;
            const marginDeposit = porsi30 * marginPercentage;
            const maxNominalUk = porsi30 - marginDeposit;
            document.getElementById('nominal_uk').max = maxNominalUk;
            document.getElementById('max-value').innerHTML = `Max Nominal UK: ${maxNominalUk.toFixed(2)}`;
            document.getElementById('margindeposit').innerHTML = `Margin Deposit: ${marginDeposit.toFixed(2)}`;
        }

        function checkNominal(porsi30) {
            const transaksi = document.getElementById('transaksi').value;
            const marginPercentage = transaksi === 'BTB' ? 0.10 : 0.06;
            const marginDeposit = porsi30 * marginPercentage;
            const maxNominalUk = porsi30 - marginDeposit;
            const nominalUk = parseFloat(document.getElementById('nominal_uk').value);

            if (nominalUk > maxNominalUk) {
                document.getElementById('status').innerHTML = `Error: Nominal UK cannot be more than ${maxNominalUk.toFixed(2)}`;
                document.getElementById('status').classList.add('text-danger');
                document.getElementById('status').classList.remove('text-success');
            } else {
                document.getElementById('status').innerHTML = `Nominal UK is valid`;
                document.getElementById('status').classList.add('text-success');
                document.getElementById('status').classList.remove('text-danger');
            }
        }
    </script>
</body>

</html>