<?php
include 'koneksi.php';

session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get leasing data
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM leasing WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $leasing = $result->fetch_assoc();

    if (!$leasing) {
        echo "<script>alert('Leasing not found!'); window.location.href='leasing.php';</script>";
        exit();
    }

    // Get detail leasing
    $sql_detail = "SELECT * FROM detail_leasing WHERE id_leasing = ? ORDER BY id";
    $stmt_detail = $conn->prepare($sql_detail);
    $stmt_detail->bind_param("i", $id);
    $stmt_detail->execute();
    $result_detail = $stmt_detail->get_result();
    $details = $result_detail->fetch_all(MYSQLI_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload if new file is provided
    if (!empty($_FILES['perjanjian_kredit']['name'])) {
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
    } else {
        // Keep existing filename
        $fileName = $leasing['perjanjian_kredit_file'];
    }

    // Update leasing data
    $sql = "UPDATE leasing SET 
        jenis_ki = ?,
        coa = ?,
        pembayaran_atas = ?,
        total_harga = ?,
        `dp%` = ?,
        nominal_dp = ?,
        pokok_hutang = ?,
        premi_asuransi = ?,
        admin_asuransi = ?,
        tenor = ?,
        suku_bunga = ?,
        angsuran_perbulan = ?,
        tgl_jt = ?,
        methode = ?,
        payment_methode = ?,
        ac_number = ?,
        perjanjian_kredit_file = ?
    WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssddddddidsssssssi",
        $_POST['jeniski'],
        $_POST['COA'],
        $_POST['pembayaran_atas'],
        $_POST['total_harga_raw'],
        $_POST['dprate'],
        $_POST['nominal_dp'],
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
        $fileName,
        $id
    );

    if ($stmt->execute()) {
        // Delete existing detail records
        $delete_sql = "DELETE FROM detail_leasing WHERE id_leasing = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $id);
        $delete_stmt->execute();

        // Insert new detail records
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

        for ($i = 0; $i < count($_POST['rows']); $i++) {
            $detail_stmt->bind_param(
                "isddddds",
                $id,
                $_POST['rows'][$i]['due_date'],
                $_POST['rows'][$i]['pokok_hutang'],
                $_POST['rows'][$i]['angsuran_pokok'],
                $_POST['rows'][$i]['bunga'],
                $_POST['rows'][$i]['outstanding'],
                $_POST['rows'][$i]['pokok_akhir'],
                $_POST['payment_numbers'][$i]
            );

            if (!$detail_stmt->execute()) {
                echo "<script>alert('Error updating detail: " . $detail_stmt->error . "');</script>";
                break;
            }
        }

        echo "<script>alert('Data berhasil diupdate!'); window.location.href='leasing.php';</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Edit Leasing</title>
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
        <h4 class="shadow-sm p-3 mb-5 bg-body rounded">EDIT DATA LEASING</h4>
    </div>
    <div class="container" style="margin-top:30px;margin-bottom:50px;height:auto">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="col">
                <label for="pembayaran_atas">Pembiayaan Atas :</label>
                <input type="text" name="pembayaran_atas" class="form-control" value="<?= htmlspecialchars($leasing['pembayaran_atas']) ?>" required>
            </div>

            <div class="row">
                <div class="col">
                    <label for="jeniski">Jenis Ki : </label>
                    <select name="jeniski" class="form-control" id="jeniski" required>
                        <option value="">Pilih Jenis Ki</option>
                        <?php
                        $query = "SELECT id, entitas, Ket, dp, suku_bunga FROM bnl";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            $selected = ($row['id'] == $leasing['jenis_ki']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' data-sb='{$row['suku_bunga']}' data-dp='{$row['dp']}' {$selected}>{$row['Ket']} - {$row['entitas']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col"></div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="COA">COA :</label>
                    <select name="COA" class="form-control" id="coa" required>
                        <option value="">Pilih COA</option>
                        <?php
                        $conn2 = new mysqli($servername2, $username2, $password2, $dbname2);
                        $query = "SELECT * FROM coa";
                        $result = $conn2->query($query);
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($row['id'] == $leasing['coa']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' data-total='{$row['total']}' {$selected}>{$row['coa']} - {$row['pembayaranAtas']}</option>";
                        }
                        $conn2->close();
                        ?>
                    </select>
                </div>
                <div class="col"></div>
            </div>

            <div class="row mt-3">
                <div class="col">
                    <label for="perjanjian_kredit">Perjanjian Kredit :</label>
                    <input type="file" name="perjanjian_kredit" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    <small class="text-muted">Current file: <?= htmlspecialchars($leasing['perjanjian_kredit_file']) ?></small>
                </div>
                <div class="col"></div>
            </div>

            <div class="row">
                <div class="col">
                </div>
                <div class="col">
                    <label for="total_harga">Total Harga :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp. </span>
                        <input type="text" id="total_harga" name="total_harga" class="form-control" value="<?= number_format($leasing['total_harga'], 2, ',', '.') ?>" readonly required>
                        <input type="hidden" id="total_harga_raw" name="total_harga_raw" value="<?= $leasing['total_harga'] ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="dprate">DP % :</label>
                    <div class="input-group mb-3">
                        <input type="number" name="dprate" id="dprate" class="form-control" step="0.01" value="<?= $leasing['dp%'] ?>" readonly required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col">
                    <label for="nominal_dp">Nominal DP :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp. </span>
                        <input type="text" name="nominal_dp" class="form-control" value="<?= $leasing['nominal_dp'] ?>" readonly required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="sukubunga">Suku Bunga :</label>
                    <div class="input-group mb-3">
                        <input type="number" id="sukubunga" name="sukubunga" class="form-control" step="0.01" value="<?= $leasing['suku_bunga'] ?>" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>
                <div class="col">
                    <label for="pokokhutang">Pokok Hutang :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp. </span>
                        <input type="number" name="pokokhutang" class="form-control" value="<?= $leasing['pokok_hutang'] ?>" readonly required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="tgl_jt">Due Date :</label>
                    <input type="date" name="tgl_jt" class="form-control" value="<?= $leasing['tgl_jt'] ?>" readonly required>
                </div>
                <div class="col">
                    <label for="tenor">Tenor :</label>
                    <input type="number" name="tenor" class="form-control" value="<?= $leasing['tenor'] ?>" readonly required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="pembayaran">Metode Pembayaran Cicilan :</label>
                    <select name="pembayaran" class="form-control" required>
                        <option value="">Pilih Metode Pembayaran</option>
                        <option value="1" <?= $leasing['payment_methode'] == '1' ? 'selected' : '' ?>>Giro</option>
                        <option value="2" <?= $leasing['payment_methode'] == '2' ? 'selected' : '' ?>>Cek</option>
                        <option value="3" <?= $leasing['payment_methode'] == '3' ? 'selected' : '' ?>>Transfer</option>
                        <option value="4" <?= $leasing['payment_methode'] == '4' ? 'selected' : '' ?>>Autodebit</option>
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
                            $selected = ($row['no_akun'] == $leasing['ac_number']) ? 'selected' : '';
                            echo "<option value='{$row['no_akun']}' 
                                data-bank='{$row['nama_bank']}' 
                                data-noakun='{$row['no_akun']}'
                                data-identitas='{$row['id_entitas']}'
                                data-account='{$row['nama_akun']}' {$selected}>{$row['nama_akun']} - {$row['nama_bank']} - {$row['no_akun']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="premi_asuransi">Premi Asuransi :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp. </span>
                        <input type="number" name="premi_asuransi" class="form-control" value="<?= $leasing['premi_asuransi'] ?>" required>
                    </div>
                </div>
                <div class="col">
                    <label for="admin_asuransi">Admin Asuransi :</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text">Rp. </span>
                        <input type="number" name="admin_asuransi" class="form-control" value="<?= $leasing['admin_asuransi'] ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label for="pengembalian">Metode Pengembalian Plafond :</label>
                    <select name="pengembalian" class="form-control" required>
                        <option value="">Pilih Metode Pengembalian</option>
                        <option value="1" <?= $leasing['methode'] == '1' ? 'selected' : '' ?>>Pengembalian Per Pembayaran</option>
                        <option value="2" <?= $leasing['methode'] == '2' ? 'selected' : '' ?>>Pengembalian Saat Kontrak Selesai</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <br>
                    <h2>Tenor List :</h2>
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
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($details)) {
                                foreach ($details as $index => $detail) {
                                    echo "<tr>";
                                    echo "<td>" . ($index + 1) . "</td>";
                                    echo "<td>{$detail['due_date']}</td>";
                                    echo "<td>Rp. " . number_format($detail['saldo_awal'], 2, ',', '.') . "</td>";
                                    echo "<td>Rp. " . number_format($detail['angsuran_pokok'], 2, ',', '.') . "</td>";
                                    echo "<td>Rp. " . number_format($detail['bunga'], 2, ',', '.') . "</td>";
                                    echo "<td>Rp. " . number_format($detail['outstanding'], 2, ',', '.') . "</td>";
                                    echo "<td>Rp. " . number_format($detail['saldo_akhir'], 2, ',', '.') . "</td>";
                                    echo "<td>
                                            <select name='payment_numbers[]' class='form-control select2-payment' required>
                                                <option value='{$detail['payment_no']}'>{$detail['payment_no']}</option>
                                            </select>
                                            <input type='hidden' name='rows[{$index}][row_number]' value='" . ($index + 1) . "'>
                                            <input type='hidden' name='rows[{$index}][due_date]' value='{$detail['due_date']}'>
                                            <input type='hidden' name='rows[{$index}][pokok_hutang]' value='{$detail['saldo_awal']}'>
                                            <input type='hidden' name='rows[{$index}][angsuran_pokok]' value='{$detail['angsuran_pokok']}'>
                                            <input type='hidden' name='rows[{$index}][bunga]' value='{$detail['bunga']}'>
                                            <input type='hidden' name='rows[{$index}][outstanding]' value='{$detail['outstanding']}'>
                                            <input type='hidden' name='rows[{$index}][pokok_akhir]' value='{$detail['saldo_akhir']}'>
                                          </td>";
                                    echo "<td>{$detail['status']}</td>";
                                    echo "</tr>";
                                    
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col text-end">
                    <a href="leasing.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                    // Pre-fill form values
                    document.querySelector('input[name="total_harga"]').value = '<?= number_format($leasing['total_harga'], 2, ',', '.') ?>';
                    document.querySelector('input[name="total_harga_raw"]').value = '<?= $leasing['total_harga'] ?>';
                    document.querySelector('input[name="dprate"]').value = '<?= $leasing['dp%'] ?>';
                    document.querySelector('input[name="nominal_dp"]').value = '<?= $leasing['nominal_dp'] ?>';
                    document.querySelector('input[name="pokokhutang"]').value = '<?= $leasing['pokok_hutang'] ?>';
                    document.querySelector('input[name="premi_asuransi"]').value = '<?= $leasing['premi_asuransi'] ?>';
                    document.querySelector('input[name="admin_asuransi"]').value = '<?= $leasing['admin_asuransi'] ?>';
                    document.querySelector('input[name="tenor"]').value = '<?= $leasing['tenor'] ?>';
                    document.querySelector('input[name="sukubunga"]').value = '<?= $leasing['suku_bunga'] ?>';
                    document.querySelector('input[name="tgl_jt"]').value = '<?= $leasing['tgl_jt'] ?>';
                    document.querySelector('select[name="pembayaran"]').value = '<?= $leasing['payment_methode'] ?>';
                    document.querySelector('select[name="pengembalian"]').value = '<?= $leasing['methode'] ?>';
                    document.querySelector('select[name="acnumber"]').value = '<?= $leasing['ac_number'] ?>';

                    // Trigger all calculations
                    calculateValues();
                    calculateTotalPokokHutang();
                    generateTenorTable();

                    // Pre-fill payment numbers from details
                    const details = <?= json_encode($details) ?>;
                    if (details && details.length > 0) {
                        setTimeout(() => {
                            const paymentSelects = document.querySelectorAll('.select2-payment');
                            details.forEach((detail, index) => {
                                if (paymentSelects[index]) {
                                    // Add option for existing payment number if not in list
                                    let option = new Option(detail.payment_no, detail.payment_no, true, true);
                                    $(paymentSelects[index]).append(option).trigger('change');
                                }
                            });
                        }, 500); // Small delay to ensure select2 is initialized
                    }

                    // Handle payment method change
                    document.querySelector('select[name="pembayaran"]').addEventListener('change', function() {
                        var paymentMethod = this.value;
                        var paymentNumberSelects = document.querySelectorAll('.select2-payment');
                        var existingPayments = <?= json_encode(array_column($details, 'payment_no')) ?>;

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

                    // Handle account number change with existing payment numbers
                    document.querySelector('select[name="acnumber"]').addEventListener('change', function() {
                        var acnumber = this.value;
                        var paymentMethod = document.querySelector('select[name="pembayaran"]').value;
                        var paymentType = paymentMethod === '1' ? 'Giro' : paymentMethod === '2' ? 'Cek' : paymentMethod === '3' ? 'Transfer' : paymentMethod === '4' ? 'Autodebit' : '';
                        var existingPayments = <?= json_encode(array_column($details, 'payment_no')) ?>;

                        if (!paymentType || !acnumber) return;

                        var paymentSelects = document.querySelectorAll('.select2-payment');

                        if (paymentMethod == 1 || paymentMethod == 2) {
                            paymentSelects.forEach(function(select, index) {
                                select.innerHTML = `<option value="">Select ${paymentType} Number</option>`;

                                // Add existing payment number as first option
                                if (existingPayments[index]) {
                                    let option = new Option(existingPayments[index], existingPayments[index], true, true);
                                    select.add(option);
                                }

                                if (acnumber) {
                                    fetch(`get_giro.php?acnumber=${acnumber}&type=${paymentType}`)
                                        .then(response => response.json())
                                        .then(data => {
                                            data.forEach(item => {
                                                // Only add if not already selected
                                                if (!existingPayments.includes(item.number)) {
                                                    let option = new Option(item.number, item.number);
                                                    select.add(option);
                                                }
                                            });
                                            $(select).select2({
                                                placeholder: `Search for a ${paymentType.toLowerCase()} number`
                                            });
                                        })
                                        .catch(error => console.error('Error:', error));
                                }
                            });
                        } else {
                            // Handle Transfer/Autodebit cases
                            // ...existing Transfer/Autodebit code...
                        }
                    });

                    // Initialize calculations and triggers
                    calculateValues();
                    calculateTotalPokokHutang();
                    generateTenorTable();

                    // Trigger payment method change to initialize payment numbers
                    document.querySelector('select[name="pembayaran"]').dispatchEvent(new Event('change'));
                });

                // Modify generateTenorTable for editing
                function generateTenorTable() {
                    var tenor = parseInt(document.querySelector('input[name="tenor"]').value) || 0;
                    var sukuBunga = parseFloat(document.querySelector('input[name="sukubunga"]').value) || 0;
                    var pokokHutang = parseFloat(document.querySelector('input[name="pokokhutang"]').value) || 0;
                    var dueDate = new Date(document.querySelector('input[name="tgl_jt"]').value);

                    // // Only regenerate if values have changed from original
                    // if (tenor !== <?= $leasing['tenor'] ?> ||
                    //     sukuBunga !== <?= $leasing['suku_bunga'] ?> ||
                    //     pokokHutang !== <?= $leasing['pokok_hutang'] ?>) {

                    //     var tbody = document.querySelector('#tenor_table tbody');
                    //     tbody.innerHTML = '';

                    //     if (tenor > 0 && pokokHutang > 0 && sukuBunga > 0 && dueDate) {
                    //         // ...existing tenor table generation code...
                    //     }
                    // }
                }
            </script>
        </form>
    </div>
</body>

</html>