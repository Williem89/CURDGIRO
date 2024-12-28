<?php
include 'koneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    $uploadDir = 'imgleasing/perjanjian_kredit/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['perjanjian_kredit']['name']);
    $uploadFile = $uploadDir . $fileName;
    $fileType = strtolower(pathinfo($uploadFile, PATHINFO_EXTENSION));

    // Validate file
    $allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    if (!in_array($fileType, $allowedTypes)) {
        echo "<script>alert('Sorry, only PDF, Word documents and images are allowed.');</script>";
        exit();
    }

    if (!move_uploaded_file($_FILES['perjanjian_kredit']['tmp_name'], $uploadFile)) {
        echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        exit();
    }

    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    // // exit();

    // Prepare the items array for JSON storage
    // $items = array_map(function ($item) {
    //     return [
    //         'name' => $item['name'],
    //         'nominal' => floatval($item['nominal'])
    //     ];
    // }, $_POST['items']);

    // Convert items array to JSON
    // $itemsJson = json_encode($items);

    // Calculate total_dp and totalpokokhutang based on a_include and cicilan_pertama
    $cicilan_pertama = isset($_POST['cicilan_pertama']) ? $_POST['angsuran_perbulan'] : 0;
    switch ($_POST['a_include']) {
        case '1':
            $total_dp = $_POST['nominal_dp'] + $_POST['premi_asuransi'] + $_POST['admin_asuransi'] + $cicilan_pertama;
            $totalpokokhutang = $_POST['pokokhutang'];
            break;
        case '2':
            $total_dp = $_POST['nominal_dp'] + $cicilan_pertama;
            $totalpokokhutang = $_POST['pokokhutang'] + $_POST['premi_asuransi'] + $_POST['admin_asuransi'];
            break;
    }

    // Prepare and execute the SQL query
    $sql = "INSERT INTO leasing (
        jenis_ki,
        coa,
        pembayaran_atas,
        item,
        total_harga,
        `dp%`,
        nominal_dp,
        pokok_hutang,
        premi_asuransi,
        admin_asuransi,
        tenor,
        suku_bunga,
        angsuran_perbulan,
        tgl_jt,
        methode,
        payment_methode,
        ac_number,
        perjanjian_kredit_file
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssssddddddiidsssss",
        $_POST['jeniski'],
        $_POST['COA'],
        $_POST['pembayaran_atas'],
        $itemsJson,
        $_POST['total_harga_raw'],
        $_POST['dprate'],
        $total_dp,
        $_POST['pokokhutang'],
        $_POST['premi_asuransi'],
        $_POST['admin_asuransi'],
        $_POST['tenor'],
        $_POST['sukubunga'],
        $_POST['angsuran_perbulan'],
        $_POST['tgl_jt'],
        $_POST['pengembalian'],
        $_POST['pembayaran'],
        $_POST['acnumber'],
        $fileName // Changed from $uploadFile to $fileName
    );

    if ($stmt->execute()) {
        $leasing_id = $conn->insert_id; // Get the ID of the inserted leasing record

        // Prepare detail leasing insert statement
        $detail_sql = "INSERT INTO detail_leasing (
            id_leasing,
            due_date,
            saldo_awal,
            angsuran_pokok,
            bunga,
            outstanding,
            saldo_akhir,
            payment_no,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'unpaid')";

        $detail_stmt = $conn->prepare($detail_sql);

        // Loop through each row and insert detail records
        for ($i = 0; $i < count($_POST['rows']); $i++) {
            // Deformat bunga value
            $_POST['rows'][$i]['bunga'] = floatval(str_replace(',', '', $_POST['rows'][$i]['bunga']));

            $detail_stmt->bind_param(
                "isddddds",
                $leasing_id,
                $_POST['rows'][$i]['due_date'],
                $_POST['rows'][$i]['pokok_hutang'],
                $_POST['rows'][$i]['angsuran_pokok'],
                $_POST['rows'][$i]['bunga'],
                $_POST['rows'][$i]['outstanding'],
                $_POST['rows'][$i]['pokok_akhir'],
                $_POST['payment_numbers'][$i]
            );

            if (!$detail_stmt->execute()) {
                // Handle error
                echo "<script>alert('Error inserting detail: " . $detail_stmt->error . "');</script>";
                break;
            }
        }

        $detail_stmt->close();

        // After detail_leasing inserts, handle autodebit/transfer records
        if ($_POST['pembayaran'] == '3' || $_POST['pembayaran'] == '4') {
            $jenis = ($_POST['pembayaran'] == '3') ? 'Transfer' : 'autodebit';

            // Prepare autodebit/transfer insert statement
            $auto_sql = "INSERT INTO data_autodebit (
                BatchId,
                noautodebit,
                namabank,
                ac_number,
                ac_name,
                ApproveAt,
                jenis_autodebit,
                id_entitas
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $autodetail_sql = "INSERT INTO detail_autodebit (
                noautodebit,
                tanggal_autodebit,
                tanggal_jatuh_tempo,
                Nominal,
                nama_penerima,
                bank_penerima,
                ac_penerima,
                StatAutodebit,
                Keterangan,
                created_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $auto_stmt = $conn->prepare($auto_sql);

            foreach ($_POST['payment_numbers'] as $payment_number) {
                // Create variables for binding
                $batchId = "System404";
                $approveAt = date('Y-m-d H:i:s');
                $bankName = $_POST['bank_name'];
                $accountNumber = $_POST['account_number'];
                $accountName = $_POST['account_name'];
                $entitasId = $_POST['id_entitas'];

                $auto_stmt->bind_param(
                    "ssssssss",
                    $batchId,
                    $payment_number,
                    $bankName,
                    $accountNumber,
                    $accountName,
                    $approveAt,
                    $jenis,
                    $entitasId
                );

                if (!$auto_stmt->execute()) {
                    echo "<script>alert('Error creating " . $jenis . " record: " . $auto_stmt->error . "');</script>";
                    break;
                }
            }

            // Prepare detail autodebit/transfer insert statement
            $autodetail_stmt = $conn->prepare($autodetail_sql);

            foreach ($_POST['payment_numbers'] as $index => $payment_number) {
                $tanggalAutodebit = $_POST['rows'][$index]['due_date'];
                $tanggalJatuhTempo = $_POST['rows'][$index]['due_date'];
                $nominal = $_POST['rows'][$index]['outstanding'];
                $namaPenerima = $_POST['nama_cust'];
                $bankPenerima = $_POST['bank_cust'];
                $acPenerima = $_POST['ac_cust'];
                $statAutodebit = 'Pending Issued';
                $keterangan = $_POST['pembayaran_atas'];
                $createdBy = $_SESSION['username'];

                $autodetail_stmt->bind_param(
                    "ssssssssss",
                    $payment_number,
                    $tanggalAutodebit,
                    $tanggalJatuhTempo,
                    $nominal,
                    $namaPenerima,
                    $bankPenerima,
                    $acPenerima,
                    $statAutodebit,
                    $keterangan,
                    $createdBy
                );
                if (!$autodetail_stmt->execute()) {
                    echo "<script>alert('Error creating " . $jenis . " detail record: " . $autodetail_stmt->error . "');</script>";
                    break;
                }
            }
            $auto_stmt->close();
            $autodetail_stmt->close();
        }
        echo "<script>alert('Data berhasil disimpan!'); window.location.href='index.php';</script>";
    } else {
        // Failed insert
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Form Leasing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery must be loaded before other scripts that depend on it -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>

<body>
    <div class="header text-center" style="margin: auto;">
        <a href="leasing.php" class="float-start" style="padding-top:10px;padding-left:35px;font-size:18pt"><i class="bi bi-backspace"></i></a>
        <h4 class="shadow-sm p-3 mb-5 bg-body rounded">INPUT DATA LEASING</h4>
    </div>
    <div class="container" style="margin-top:30px;margin-bottom:50px;height:auto">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col">
                    <label for="COA">COA :</label>
                    <select name="COA" class="form-control" id="coa" required>
                        <option value="">Pilih COA</option>
                        <?php
                        $conn2 = new mysqli($servername2, $username2, $password2, $dbname2);
                        if ($conn2->connect_error) {
                            die("Connection failed: " . $conn2->connect_error);
                        }
                        $query = "SELECT * FROM coa";
                        $result = $conn2->query($query);
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='{$row['coa']}' data-dueDate='{$row['dueDate']}' data-tenor='{$row['cicilanSampai']}' data-totalHutang='{$row['totalHutang']}' data-pembayaranAtas='{$row['pembayaranAtas']}' data-total='{$row['total']}'>{$row['coa']} - {$row['pembayaranAtas']}</option>";
                        }
                        $conn2->close();
                        ?>
                    </select>
                </div>
                <div class="col">
                    <label for="jeniski">Jenis Ki : </label>
                    <select name="jeniski" class="form-control" id="jeniski" required>
                        <option value="">Pilih Jenis Ki</option>
                        <?php
                        $query = "SELECT id, entitas, Ket, dp, suku_bunga FROM bnl";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<option value='{$row['id']}' data-sb='{$row['suku_bunga']}' data-dp='{$row['dp']}'>{$row['Ket']} - {$row['entitas']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col">
                <label for="pembayaran_atas">Pembiayaan Atas :</label>
                <input type="text" id="pembayaran_atas" name="pembayaran_atas" class="form-control" required>
            </div>
            <div class="row">
                <div class="col">
                    <label for="tgl_jt">Due Date :</label>
                    <input type="date" id="tgl_jt" name="tgl_jt" class="form-control" required>
                </div>
                <div class="col">
                    <label for="tenor">Tenor :</label>
                    <input type="number" id="tenor" name="tenor" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col">

                </div>
                <div class="col">
                    <label for="perjanjian_kredit">Perjanjian Kredit :</label>
                    <input type="file" name="perjanjian_kredit" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                    <small class="text-muted">Upload PDF, Word document, or image file</small>
                </div>
            </div>
            <!-- <div id="itemsContainer">
                    <a class="btn btn-info" id="addItem"> <i class="bi bi-plus-circle-fill"></i></a>
                </div>
                <script>
                    document.getElementById('addItem').addEventListener('click', function() {
                        var container = document.getElementById('itemsContainer');
                        var itemIndex = container.children.length - 1; // Adjust for the add button itself
                        var newItem = document.createElement('div');
                        newItem.classList.add('row', 'item-row');
                        newItem.innerHTML = `
                            <div class="col-6">
                                <label for="items_${itemIndex}_name">Nama Item :</label>
                                <input type="text" name="items[${itemIndex}][name]" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label for="items_${itemIndex}_nominal">Nominal Item :</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp. </span>
                                    <input type="text" name="items[${itemIndex}][nominal_display]" class="form-control nominal-display" required>
                                    <input type="hidden" name="items[${itemIndex}][nominal]" class="nominal-raw">
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-danger removeItem"><i class="bi bi-trash-fill"></i></button>
                            </div>
                        `;
                        container.appendChild(newItem);

                        newItem.querySelector('.removeItem').addEventListener('click', function() {
                            container.removeChild(newItem);
                            updateTotalHarga();
                        });

                        newItem.querySelector('.nominal-display').addEventListener('input', function() {
                            var rawValue = this.value.replace(/[^0-9]/g, '');
                            this.nextElementSibling.value = rawValue;
                            this.value = new Intl.NumberFormat('id-ID').format(rawValue);
                            updateTotalHarga();
                        });
                    });

                    function updateTotalHarga() {
                        var totalHarga = 0;
                        document.querySelectorAll('.nominal-raw').forEach(function(input) {
                            totalHarga += parseFloat(input.value) || 0;
                        });
                        document.querySelector('input[name="total_harga"]').value = new Intl.NumberFormat('id-ID').format(totalHarga);
                        document.querySelector('input[name="total_harga_raw"]').value = totalHarga;
                        calculateValues();
                    }
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('addItem').click();
                        updateTotalHarga();
                    });
                </script> -->

            <div class="row">
                <div class="col">
                </div>
                <div class="col">
                    <label for="total_harga">Total Harga :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp. </span>
                        <input type="text" id="total_harga" name="total_harga" class="form-control" required>
                        <input type="hidden" id="total_harga_raw" name="total_harga_raw" class="form-control" required readonly>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="sukubunga">Suku Bunga :</label>
                    <div class="input-group mb-3">
                        <input type="number" id="sukubunga" name="sukubunga" class="form-control" step="0.01" required>
                        <span class="input-group-text" id="basic-addon1">%</span>
                    </div>
                </div>
                <script>
                    document.getElementById('jeniski').addEventListener('change', function() {
                        var selectedOption = this.options[this.selectedIndex];
                        var sukubunga = selectedOption.getAttribute('data-sb');
                        document.getElementById('sukubunga').value = sukubunga;
                        calculateValues();
                    });
                </script>

                <div class="col">
                    <label for="pokokhutang">Pokok Hutang :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp. </span>
                        <input type="number" name="pokokhutang" class="form-control" readonly required>
                    </div>
                    <script>
                        document.querySelector('input[name="total_harga"]').addEventListener('input', calculateValues);
                        document.querySelector('input[name="dprate"]').addEventListener('input', calculateValues);

                        function calculateValues() {
                            var totalHarga = parseFloat(document.querySelector('input[name="total_harga_raw"]').value) || 0;
                            var dpRate = parseFloat(document.querySelector('input[name="dprate"]').value) || 0;
                            var nominalDp = totalHarga * (dpRate / 100);
                            var pokokHutang = totalHarga - nominalDp;
                            document.querySelector('input[name="nominal_dp"]').value = nominalDp.toFixed(2);
                            document.querySelector('input[name="pokokhutang"]').value = pokokHutang.toFixed(2);

                            // Calculate total_dp and totalpokokhutang based on a_include and cicilan_pertama
                            var aInclude = document.querySelector('select[name="a_include"]').value;
                            var premiAsuransi = parseFloat(document.querySelector('input[name="premi_asuransi"]').value) || 0;
                            var adminAsuransi = parseFloat(document.querySelector('input[name="admin_asuransi"]').value) || 0;
                            var cicilanPertama = document.querySelector('input[name="cicilan_pertama"]').checked ? parseFloat(document.querySelector('input[name="angsuran_perbulan"]').value) || 0 : 0;
                            var totalDp, totalPokokHutang;

                            if (aInclude === '1') {
                                totalDp = nominalDp + premiAsuransi + adminAsuransi + cicilanPertama;
                                totalPokokHutang = pokokHutang;
                            } else if (aInclude === '2') {
                                totalDp = nominalDp + cicilanPertama;
                                totalPokokHutang = pokokHutang + premiAsuransi + adminAsuransi;
                            } else {
                                totalDp = nominalDp;
                                totalPokokHutang = pokokHutang;
                            }

                            document.querySelector('input[name="tdp"]').value = totalDp.toFixed(2);
                            document.querySelector('input[name="tdp_raw"]').value = totalDp.toFixed(2);
                            document.querySelector('input[name="totalpokokhutang"]').value = totalPokokHutang.toFixed(2);
                            calculateAngsuranPerbulan();
                        }

                        function calculateAngsuranPerbulan() {
                            var totalPokokHutang = parseFloat(document.querySelector('input[name="totalpokokhutang"]').value) || 0;
                            var tenor = parseFloat(document.querySelector('input[name="tenor"]').value) || 0;
                            var angsuranPerbulan = tenor > 0 ? totalPokokHutang / tenor : 0;
                            document.querySelector('input[name="angsuran_perbulan"]').value = angsuranPerbulan.toFixed(2);
                        }

                        document.querySelector('select[name="a_include"]').addEventListener('change', calculateValues);
                        document.querySelector('input[name="premi_asuransi"]').addEventListener('input', calculateValues);
                        document.querySelector('input[name="admin_asuransi"]').addEventListener('input', calculateValues);
                        document.querySelector('input[name="total_harga"]').addEventListener('input', calculateValues);
                        document.querySelector('input[name="dprate"]').addEventListener('input', calculateValues);
                        document.querySelector('input[name="cicilan_pertama"]').addEventListener('change', calculateValues);
                    </script>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="premi_asuransi">Premi Asuransi :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp. </span>
                        <input type="number" name="premi_asuransi" class="form-control" required>
                    </div>
                </div>
                <div class="col">
                    <label for="admin_asuransi">Admin Asuransi :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp. </span>
                        <input type="number" name="admin_asuransi" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="a_include">Asuransi Include ke :</label>
                    <select name="a_include" class="form-control" required>
                        <option value="">-- Include Ke --</option>
                        <option value="1">DP</option>
                        <option value="2">Pokok Hutang</option>
                    </select>
                </div>
                <div class="col">
                    <label for="cicilan_pertama">Include Cicilan Pertama to DP:</label>
                    <input type="checkbox" name="cicilan_pertama" id="cicilan_pertama" class="form-check-input">
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <label for="dprate">DP % :</label>
                    <div class="input-group mb-3">
                        <input type="number" name="dprate" id="dprate" class="form-control" step="0.01" required>
                        <span class="input-group-text" id="basic-addon1">%</span>
                    </div>
                </div>
                <script>
                    document.getElementById('jeniski').addEventListener('change', function() {
                        var selectedOption = this.options[this.selectedIndex];
                        var dpRate = selectedOption.getAttribute('data-dp');
                        document.getElementById('dprate').value = dpRate;
                        calculateValues();
                    });
                </script>
                <div class="col">
                    <label for="nominal_dp">Nominal DP :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text" id="basic-addon1">Rp. </span>
                        <input type="text" name="nominal_dp" class="form-control" readonly required>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                    </div>
                    <div class="col">
                        <label for="tdp">Total DP :</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp. </span>
                            <input type="text" id="tdp" name="tdp" class="form-control" required>
                            <input type="hidden" id="tdp_raw" name="tdp_raw" class="form-control" required readonly>
                        </div>
                    </div>
                </div>


                <div class="row">
                    <div class="col">
                        <label for="pembayaran">Metode Pembayaran Cicilan :</label>
                        <select name="pembayaran" class="form-control" required>
                            <option value="">Pilih Metode Pembayaran</option>
                            <option value="1">Giro</option>
                            <option value="2">Cek</option>
                            <option value="3">Transfer</option>
                            <option value="4">Autodebit</option>
                        </select>
                    </div>
                    <div class="col">
                        <label for="acnumber">Account Number :</label>
                        <select name="acnumber" class="form-control" required>
                            <option value="">Select Account Number</option>
                            <?php
                            $query = "SELECT * FROM list_rekening ORDER BY id_entitas, nama_bank";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Store selected data in a data attribute
                                echo "<option value='{$row['no_akun']}' 
                                    data-bank='{$row['nama_bank']}' 
                                    data-noakun='{$row['no_akun']}'
                                    data-identitas='{$row['id_entitas']}'
                                    data-account='{$row['nama_akun']}'>{$row['nama_akun']} - {$row['nama_bank']} - {$row['no_akun']}</option>";
                            }
                            ?>
                        </select>
                        <input type="hidden" name="bank_name">
                        <input type="hidden" name="account_number">
                        <input type="hidden" name="id_entitas">
                        <input type="hidden" name="account_name">

                        <script>
                            document.querySelector('select[name="acnumber"]').addEventListener('change', function() {
                                var selected = this.options[this.selectedIndex];
                                document.querySelector('input[name="bank_name"]').value = selected.dataset.bank;
                                document.querySelector('input[name="account_number"]').value = selected.dataset.noakun;
                                document.querySelector('input[name="id_entitas"]').value = selected.dataset.identitas;
                                document.querySelector('input[name="account_name"]').value = selected.dataset.account;
                            });
                        </script>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <label for="customer">Dibayarkan Kepada :</label>
                        <select name="customer" class="form-control" required>
                            <option value="">Pilih Customer</option>
                            <?php
                            $query = "SELECT * FROM list_customer ORDER BY nama_cust";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['id']}'
                                data-namacust='{$row['nama_cust']}'
                                data-accust='{$row['ac_cust']}'
                                data-bankcust='{$row['bank_cust']}'>{$row['nama_cust']}</option>";
                            }
                            ?>
                        </select>
                        <input type="hidden" name="nama_cust">
                        <input type="hidden" name="ac_cust">
                        <input type="hidden" name="bank_cust">

                        <script>
                            document.querySelector('select[name="customer"]').addEventListener('change', function() {
                                var selected = this.options[this.selectedIndex];
                                document.querySelector('input[name="nama_cust"]').value = selected.dataset.namacust;
                                document.querySelector('input[name="ac_cust"]').value = selected.dataset.accust;
                                document.querySelector('input[name="bank_cust"]').value = selected.dataset.bankcust;
                            });
                        </script>
                    </div>
                    <div class="col">
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col">
                        <label for="totalpokokhutang">Total Pokok Hutang :</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp. </span>
                            <input type="number" name="totalpokokhutang" class="form-control" readonly required>
                        </div>
                    </div>
                    <script>
                        // Add event listeners to relevant inputs
                        document.querySelector('input[name="premi_asuransi"]').addEventListener('input', calculateTotalPokokHutang);
                        document.querySelector('input[name="admin_asuransi"]').addEventListener('input', calculateTotalPokokHutang);
                        document.querySelector('input[name="tenor"]').addEventListener('input', calculateAngsuranPerbulan);
                        document.querySelector('input[name="pokokhutang"]').addEventListener('input', calculateTotalPokokHutang);
                        document.querySelector('select[name="a_include"]').addEventListener('change', calculateTotalPokokHutang);

                        function calculateTotalPokokHutang() {
                            // Get values from inputs
                            var pokokHutang = parseFloat(document.querySelector('input[name="pokokhutang"]').value) || 0;
                            var premiAsuransi = parseFloat(document.querySelector('input[name="premi_asuransi"]').value) || 0;
                            var adminAsuransi = parseFloat(document.querySelector('input[name="admin_asuransi"]').value) || 0;
                            var nominalDp = parseFloat(document.querySelector('input[name="nominal_dp"]').value) || 0;
                            var aInclude = parseInt(document.querySelector('select[name="a_include"]').value); // Use parseInt to ensure integer comparison

                            var totalPokokHutang, totalDp;

                            // Calculate totalPokokHutang and totalDp based on aInclude value
                            if (aInclude === 1) {
                                totalPokokHutang = pokokHutang;
                                totalDp = nominalDp + premiAsuransi + adminAsuransi;
                            } else if (aInclude === 2) {
                                totalPokokHutang = pokokHutang + premiAsuransi + adminAsuransi;
                                totalDp = nominalDp;
                            } else {
                                totalPokokHutang = pokokHutang; // Default to just pokokhutang if aInclude is neither 1 nor 2
                                totalDp = nominalDp; // Default totalDp if aInclude is not valid
                            }

                            // Update the corresponding fields with formatted values
                            document.querySelector('input[name="totalpokokhutang"]').value = totalPokokHutang.toFixed(2);
                            document.querySelector('input[name="tdp"]').value = new Intl.NumberFormat('id-ID').format(totalDp);
                            document.querySelector('input[name="tdp_raw"]').value = totalDp.toFixed(2);

                            // Call function to calculate angsuran per bulan
                            calculateAngsuranPerbulan();
                        }

                        function calculateAngsuranPerbulan() {
                            var totalPokokHutang = parseFloat(document.querySelector('input[name="totalpokokhutang"]').value) || 0;
                            var tenor = parseFloat(document.querySelector('input[name="tenor"]').value) || 0;
                            var angsuranPerbulan = tenor > 0 ? totalPokokHutang / tenor : 0;
                            document.querySelector('input[name="angsuran_perbulan"]').value = angsuranPerbulan.toFixed(2);
                        }
                    </script>

                    <div class="col">
                        <label for="angsuran_perbulan">Angsuran Perbulan :</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp. </span>
                            <input type="number" name="angsuran_perbulan" class="form-control" readonly required>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col">
                        <label for="pengembalian">Metode Pengembalian Plafond :</label>
                        <select name="pengembalian" class="form-control" required>
                            <option value="">Pilih Metode Pengembalian</option>
                            <option value="1">Pengembalian Per Pembayaran</option>
                            <option value="2">Pengembalian Saat Kontrak Selesai</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col">

                        <h2 for="tenor_table">Tenor List :</h2>
                        <hr>
                        <table class="table table-bordered" id="tenor_table">
                            <thead>
                                <tr class="text-center">
                                    <th>No</th>
                                    <th>Due Date</th>
                                    <th>Pokok Hutang</th>
                                    <th>Angsuran Pokok</th>
                                    <th>Bunga</th>
                                    <th>Outstanding</th>
                                    <th>Pokok Akhir</th>
                                    <th style="width: 250px;">Payment Reference</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Rows will be generated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <script>
                    document.querySelector('input[name="tenor"]').addEventListener('input', generateTenorTable);
                    document.querySelector('input[name="sukubunga"]').addEventListener('input', generateTenorTable);
                    document.querySelector('input[name="pokokhutang"]').addEventListener('input', generateTenorTable);
                    document.querySelector('input[name="tgl_jt"]').addEventListener('input', generateTenorTable);
                    document.getElementById('jeniski').addEventListener('change', generateTenorTable);

                    function generateTenorTable() {
                        var tenor = parseInt(document.querySelector('input[name="tenor"]').value) || 0;
                        var sukuBunga = parseFloat(document.querySelector('input[name="sukubunga"]').value) || 0;
                        var pokokHutang = parseFloat(document.querySelector('input[name="pokokhutang"]').value) || 0;
                        var dueDate = new Date(document.querySelector('input[name="tgl_jt"]').value);
                        var tbody = document.querySelector('#tenor_table tbody');
                        tbody.innerHTML = '';

                        if (tenor > 0 && pokokHutang > 0 && sukuBunga > 0 && dueDate) {
                            var angsuranPokok = pokokHutang / tenor;

                            for (var i = 0; i < tenor; i++) {
                                var bunga = (pokokHutang * sukuBunga) / 12 / 100;
                                var outstanding = angsuranPokok + bunga;
                                var pokokAkhir = pokokHutang - angsuranPokok;

                                var row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${i + 1}</td>
                                    <td>${dueDate.toLocaleDateString('en-CA')}</td>
                                    <td>${formatCurrency(pokokHutang)}</td>
                                    <td>${formatCurrency(angsuranPokok)}</td>
                                    <td><input type="number" name="rows[${i}][bunga]" value="${bunga.toFixed(2)}" class="form-control bunga-input" step="0.01" required></td>
                                    <td>${formatCurrency(outstanding)}</td>
                                    <td>${formatCurrency(pokokAkhir)}</td>
                                    <td>
                                        <select name="payment_numbers[]" class="form-control select2-payment" required>
                                            <option value="">Select Number</option>
                                        </select>
                                    </td>
                                `;
                                // Add hidden inputs for each row's data
                                var rowNumber = i + 1;
                                var rowData = `
                                    <input type="hidden" name="rows[${i}][row_number]" value="${rowNumber}">
                                    <input type="hidden" name="rows[${i}][due_date]" value="${dueDate.toLocaleDateString('en-CA')}">
                                    <input type="hidden" name="rows[${i}][pokok_hutang]" value="${pokokHutang}">
                                    <input type="hidden" name="rows[${i}][angsuran_pokok]" value="${angsuranPokok}">
                                    <input type="hidden" name="rows[${i}][outstanding]" value="${outstanding}">
                                    <input type="hidden" name="rows[${i}][pokok_akhir]" value="${pokokAkhir}">
                                `;
                                row.insertAdjacentHTML('beforeend', rowData);
                                tbody.appendChild(row);

                                pokokHutang = pokokAkhir;
                                dueDate.setMonth(dueDate.getMonth() + 1);
                            }

                            // Trigger change event to populate giro numbers
                            document.querySelector('select[name="acnumber"]').dispatchEvent(new Event('change'));
                        }
                        document.querySelectorAll('.bunga-input').forEach(function(input, index) {
                            input.addEventListener('input', function() {
                                var updatedBunga = parseFloat(this.value) || 0;
                                var angsuranPokok = parseFloat(document.querySelector(`input[name="rows[${index}][angsuran_pokok]"]`).value) || 0;
                                var outstanding = angsuranPokok + updatedBunga;
                                document.querySelector(`input[name="rows[${index}][outstanding]"]`).value = outstanding.toFixed(2);
                                document.querySelector(`#tenor_table tbody tr:nth-child(${index + 1}) td:nth-child(6)`).innerText = formatCurrency(outstanding);
                            });
                            input.addEventListener('blur', function() {
                                this.value = parseFloat(this.value).toFixed(2);
                            });
                        });
                    }

                    function formatCurrency(value) {
                        return new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(value);
                    }

                    document.addEventListener("DOMContentLoaded", function() {
                        // Handle payment method change
                        document.querySelector('select[name="pembayaran"]').addEventListener('change', function() {
                            var paymentMethod = this.value;
                            var paymentNumberSelects = document.querySelectorAll('.select2-payment');

                            // Update column header based on payment method
                            var headerCell = document.querySelector('#tenor_table th:last-child');
                            if (paymentMethod === '1') {
                                headerCell.textContent = 'No Giro';
                            } else if (paymentMethod === '2') {
                                headerCell.textContent = 'No Cek';
                            } else {
                                headerCell.textContent = 'Payment Reference';
                            }

                            // Trigger account number change to refresh payment numbers
                            document.querySelector('select[name="acnumber"]').dispatchEvent(new Event('change'));
                        });

                        // Handle account number change
                        document.querySelector('select[name="acnumber"]').addEventListener('change', function() {
                            var acnumber = this.value;
                            var paymentMethod = document.querySelector('select[name="pembayaran"]').value;
                            var paymentType = paymentMethod === '1' ? 'Giro' : paymentMethod === '2' ? 'Cek' : paymentMethod === '3' ? 'Transfer' : paymentMethod === '4' ? 'Autodebit' : '';

                            if (!paymentType || !acnumber) return;

                            <?php
                            $query = "SELECT MAX(noautodebit) as max_noautodebit FROM data_autodebit WHERE jenis_autodebit='autodebit'";
                            $result = mysqli_query($conn, $query);
                            $row = mysqli_fetch_assoc($result);
                            $maxAutoDebit = $row['max_noautodebit'];

                            $query2 = "SELECT MAX(noautodebit) as max_transfer FROM data_autodebit WHERE jenis_autodebit='Transfer'";
                            $result2 = mysqli_query($conn, $query2);
                            $row2 = mysqli_fetch_assoc($result2);
                            $maxTransfer = $row2['max_transfer'];
                            ?>

                            var maxAutoDebit = <?php echo json_encode($maxAutoDebit); ?>;
                            var maxTransfer = <?php echo json_encode($maxTransfer); ?>;

                            console.log(maxAutoDebit);
                            console.log(maxTransfer);

                            var lastTransferNumber = maxTransfer.match(/\d+$/);
                            var lastTransferInt = lastTransferNumber ? parseInt(lastTransferNumber[0], 10) : 0;

                            var lastAutoDebitNumber = maxAutoDebit.match(/\d+$/);
                            var lastAutoDebitInt = lastAutoDebitNumber ? parseInt(lastAutoDebitNumber[0], 10) : 0;


                            var paymentSelects = document.querySelectorAll('.select2-payment');

                            if (paymentMethod == 1 || paymentMethod == 2) {
                                paymentSelects.forEach(function(select) {
                                    select.innerHTML = `<option value="">Select ${paymentType} Number</option>`;

                                    if (acnumber) {
                                        fetch(`get_giro.php?acnumber=${acnumber}&type=${paymentType}`)
                                            .then(response => response.json())
                                            .then(data => {
                                                if (data.length > 0) {
                                                    data.forEach(item => {
                                                        var option = document.createElement('option');
                                                        option.value = item.number;
                                                        option.textContent = item.number;
                                                        select.appendChild(option);
                                                    });
                                                } else {
                                                    var option = document.createElement('option');
                                                    option.value = '';
                                                    option.textContent = `No ${paymentType} Numbers Found`;
                                                    select.appendChild(option);
                                                }
                                                // Reinitialize Select2
                                                $(select).select2({
                                                    placeholder: `Search for a ${paymentType.toLowerCase()} number`,
                                                });
                                            })
                                            .catch(error => console.error('Error fetching numbers:', error));
                                    }
                                });
                            } else if (paymentMethod == 3) {
                                paymentSelects.forEach(function(select) {
                                    lastTransferInt++;
                                    select.innerHTML = `<option value="TF-${lastTransferInt}">TF-${lastTransferInt}</option>`;
                                });
                            } else if (paymentMethod == 4) {
                                paymentSelects.forEach(function(select) {
                                    lastAutoDebitInt++;
                                    select.innerHTML = `<option value="Auto-${lastAutoDebitInt}">Auto-${lastAutoDebitInt}</option>`;
                                });
                            }

                        });
                    });
                </script>

                <div class="row mt-4">
                    <div class="col text-end">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>

        </form>
    </div>
    </div>
</body>
<script>
    const pembayaran_atas = document.getElementById('pembayaran_atas');
    const coa = document.getElementById('coa');
    const total_harga = document.getElementById('total_harga');
    const total_harga_raw = document.getElementById('total_harga_raw');
    const tenor = document.getElementById('tenor');
    const tgljt = document.getElementById('tgl_jt');

    document.getElementById('coa').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var totalHutang = selectedOption.getAttribute('data-totalHutang');
        totalHutang = totalHutang.replace(/\./g, '').replace(',', '.');
        total_harga.value = new Intl.NumberFormat('id-ID').format(totalHutang);
        total_harga_raw.value = totalHutang;

        //fire total harga input event fo calculateValues
        total_harga.dispatchEvent(new Event('input'));

        // Update pembayaran atas based on COA selection
        var pembayaranAtas = selectedOption.getAttribute('data-pembayaranAtas');
        var tenordata = selectedOption.getAttribute('data-tenor');
        tenor.value = tenordata;
        pembayaran_atas.value = pembayaranAtas;

        // Update tgl_jt based on COA selection
        var dueDate = selectedOption.getAttribute('data-dueDate');
        tgljt.value = dueDate;

        tenor.dispatchEvent(new Event('input'));
    });
</script>

</html>