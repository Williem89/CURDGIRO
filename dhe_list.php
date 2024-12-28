<?php
include 'koneksi.php';

// Fetch all transactions
$query = "SELECT 
    dt.*,
    le.nama_entitas
    FROM dhe_transactions dt
    LEFT JOIN list_entitas le ON dt.id_entitas = le.id_entitas
    ORDER BY dt.created_at DESC";
$result = mysqli_query($conn, $query);

function formatCurrency($number)
{
    return number_format($number, 2, ',', '.');
}

function formatDate($date)
{
    return date('d-m-Y', strtotime($date));
}

function formatNominalPd($jsonString)
{
    $nominals = json_decode($jsonString, true);
    if (!$nominals) return '-';

    $formatted = array_map(function ($nominal) {
        return formatCurrency($nominal);
    }, $nominals);

    return implode('<br>', $formatted);
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
    <title>DHE Transactions</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="row mb-3">
            <div class="col">
                <h2>DHE Transactions</h2>
            </div>
            <div class="col text-right">
                <a href="dhe.php" class="btn btn-primary">Add New Transaction</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="dheTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Entitas</th>
                        <th>No. Rekening</th>
                        <th>Tipe Transaksi</th>
                        <th>Tanggal UM</th>
                        <th>Nominal UM</th>
                        <th>70%</th>
                        <th>30%</th>
                        <th>Pindah Dana</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['nama_entitas']) ?></td>
                            <td><?= htmlspecialchars($row['no_rekening']) ?></td>
                            <td><?= htmlspecialchars($row['transaksi']) ?></td>
                            <td><?= formatDate($row['tgl_um']) ?></td>
                            <td class="text-right"><?= formatCurrency($row['nominal_um']) ?></td>
                            <td class="text-right"><?= formatCurrency($row['porsi70_um']) ?></td>
                            <td class="text-right"><?= formatCurrency($row['porsi30_um']) ?></td>
                            <td><?= formatPindahDana($row['pindah_dana']) ?></td>
                            <td><?= formatDate($row['created_at']) ?></td>
                            <td>
                                <a href="dhe_show.php?id=<?= $row['id'] ?>" class="btn btn-info btn-sm">View</a>
                                <?php if ($row['status'] == 0) { ?>
                                    <button class="btn btn-primary btn-sm" onclick="editData(<?= $row['id'] ?>, <?= $row['porsi30_um'] ?>, <?= $row['status'] ?>)">Edit</button>
                                <?php } else if ($row['status'] == 1) { ?>
                                    <button class="btn btn-primary btn-sm" onclick="editData2(<?= $row['id'] ?>, <?= $row['porsi30_um'] ?>, <?= $row['status'] ?>)">Edit2</button>
                                <?php } else if ($row['status'] == 2) { ?>
                                    <button class="btn btn-primary btn-sm" onclick="editData3(<?= $row['id'] ?>, <?= $row['porsi30_um'] ?>, <?= $row['status'] ?>)">Edit3</button>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dheTable').DataTable({
                "order": [
                    [10, "desc"]
                ], // Sort by created_at column by default
                "pageLength": 25
            });
        });

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