<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        mysqli_begin_transaction($conn);

        // Get form data
        $entitas = $_POST['entitas'];
        $rekening = $_POST['rekening'];
        $tgl_um = $_POST['tgl_um'];
        $nominal_um = $_POST['nominal_um'];
        $porsi70_um = $_POST['porsi70_um'];
        $porsi30_um = $_POST['porsi30_um'];
        $transaksi = $_POST['transaksi'];
        $nominal_uk = $_POST['nominal_uk'];
        $margin_deposit = $_POST['margin_deposit'];

        // Convert nominal_pd array to JSON
        $pindah_dana = [];
        if (isset($_POST['tgl_pd']) && isset($_POST['nominal_pd'])) {
            foreach ($_POST['tgl_pd'] as $index => $tgl_pd) {
                $pindah_dana[] = [
                    'tgl_pd' => $tgl_pd,
                    'nominal_pd' => $_POST['nominal_pd'][$index]
                ];
            }
        }
        $pindah_dana_json = json_encode($pindah_dana);

        // Single table insert - note tgl_uk and jatuh_tempo are not included from form
        $query = "INSERT INTO dhe_transactions (
            id_entitas, 
            no_rekening, 
            tgl_um, 
            nominal_um, 
            porsi70_um, 
            porsi30_um, 
            transaksi,
            pindah_dana,
            nominal_uk,
            margin_deposit
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt,
            "sssdddssdd",
            $entitas,
            $rekening,
            $tgl_um,
            $nominal_um,
            $porsi70_um,
            $porsi30_um,
            $transaksi,
            $pindah_dana_json,
            $nominal_uk,
            $margin_deposit
        );

        mysqli_stmt_execute($stmt);
        mysqli_commit($conn);
        echo "<script>alert('Data berhasil disimpan!');</script>";
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Create DHE</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-section hr {
            margin-top: 0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="mb-4">DHE Input</h1>
        <form method="post">
            <div class="form-section">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="entitas">Entitas</label>
                        <select name="entitas" id="entitas" class="form-control" onchange="getRekening(this.value)">
                            <option value="">--select entitas--</option>
                            <?php
                            $query = "SELECT DISTINCT id_entitas,nama_entitas FROM list_entitas";
                            $result = mysqli_query($conn, $query);
                            if ($result) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<option value='" . $row['id_entitas'] . "'>" . $row['nama_entitas'] . "</option>";
                                }
                            } else {
                                echo "<option value=''>No data available</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="rekening">No. Rekening</label>
                        <select name="rekening" id="rekening" class="form-control">
                            <option value=''>Select an entitas first</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <p><strong>Uang Masuk</strong></p>
                <hr>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="tgl_um">Tanggal</label>
                        <input type="date" name="tgl_um" id="tgl_um" class="form-control">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="nominal_um">Nominal</label>
                        <input type="number" step="0.01" name="nominal_um" id="nominal_um" class="form-control" oninput="calculatePorsi()">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="porsi70_um">Alokasi Uang masuk 70% Langsung ke Operational</label>
                        <input type="text" name="porsi70_um" id="porsi70_um" class="form-control" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="porsi30_um">Alokasi uang 30%</label>
                        <input type="text" name="porsi30_um" id="porsi30_um" class="form-control" readonly>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <p><strong>Pindah Dana 70%</strong></p>
                <hr>
                <div id="pindah-dana-container">
                    <div class="pindah-dana-row form-row">
                        <div class="form-group col-md-5">
                            <label for="tgl_pd[]">Tanggal</label>
                            <input type="date" name="tgl_pd[]" class="form-control">
                        </div>
                        <div class="form-group col-md-5">
                            <label for="nominal_pd[]">Nominal</label>
                            <input type="number" step="0.01" name="nominal_pd[]" class="form-control nominal-input" oninput="calculateSum()">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-danger mt-4" onclick="removePindahDana(this)">Remove</button>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-success" onclick="addPindahDana()">Add Pindah Dana</button>
                <div class="form-row mt-3">
                    <div class="form-group col-md-6">
                        <label>Total:</label>
                        <input type="text" id="total" class="form-control" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label>Balance Status:</label>
                        <input type="text" id="balance-status" class="form-control" readonly>
                    </div>
                </div>
                <script>
                    function addPindahDana() {
                        const container = document.getElementById('pindah-dana-container');
                        const newRow = document.createElement('div');
                        newRow.className = 'pindah-dana-row';
                        newRow.classList.add('form-row');
                        newRow.innerHTML = `
                            <div class="form-group col-md-5">
                                <label for="tgl_pd[]">Tanggal</label>
                                <input type="date" name="tgl_pd[]" class="form-control">
                            </div>
                            <div class="form-group col-md-5">
                                <label for="nominal_pd[]">Nominal</label>
                                <input type="number" step="0.01" name="nominal_pd[]" class="form-control nominal-input" oninput="calculateSum()">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger mt-4" onclick="removePindahDana(this)">Remove</button>
                            </div>
                        `;
                        container.appendChild(newRow);
                    }

                    function removePindahDana(button) {
                        button.closest('.pindah-dana-row').remove();
                        calculateSum();
                    }

                    function calculateSum() {
                        const inputs = document.getElementsByClassName('nominal-input');
                        let sum = 0;
                        for (let input of inputs) {
                            sum += Number(input.value) || 0;
                        }
                        document.getElementById('total').value = sum;
                        checkBalance(sum);
                    }

                    function checkBalance(sum) {
                        const porsi70 = Number(document.getElementById('porsi70_um').value) || 0;
                        const balanceStatus = document.getElementById('balance-status');
                        if (sum === porsi70) {
                            balanceStatus.value = 'Balanced';
                            balanceStatus.classList.remove('is-invalid');
                            balanceStatus.classList.add('is-valid');
                        } else {
                            balanceStatus.value = 'Not Balanced';
                            balanceStatus.classList.remove('is-valid');
                            balanceStatus.classList.add('is-invalid');
                        }
                    }

                    document.addEventListener('DOMContentLoaded', function() {
                        const porsi70 = Number(document.getElementById('porsi70_um').value) || 0;
                        document.querySelector('.nominal-input').value = porsi70;
                        calculateSum();
                    });
                </script>
            </div>

            <div class="form-section">
                <p><strong>Uang Keluar 30%</strong></p>
                <hr>
                <p>BTB or FX</p>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="porsi30">Alokasi uang 30%</label>
                        <input type="text" name="porsi30" id="porsi30_uk" class="form-control" readonly>
                    </div>
                    <!-- <div class="form-group col-md-6">
                        <label for="margin_deposit">Margin Deposit 6%</label>
                        <input type="number" step="0.01" name="margin_deposit" id="margin_deposit" class="form-control" readonly>
                    </div> -->
                </div>
                <!-- <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="nominal_uk">Nominal</label>
                        <input type="number" step="0.01" name="nominal_uk" id="nominal_uk" class="form-control" readonly>
                    </div>
                </div> -->
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function getRekening(entitas) {
            if (entitas === "") {
                document.getElementById("rekening").innerHTML = "<option value=''>Select an entitas first</option>";
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "get_rekening.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById("rekening").innerHTML = xhr.responseText;
                }
            };
            xhr.send("entitas=" + entitas);
        }

        function calculatePorsi() {
            const nominal = document.getElementById('nominal_um').value;
            const porsi70 = nominal * 0.7;
            const porsi30 = nominal * 0.3;
            // const marginDeposit = porsi30 * 0.06;
            // const nominalUk = porsi30 - marginDeposit;

            document.getElementById('porsi70_um').value = porsi70;
            document.getElementById('porsi30_um').value = porsi30;
            document.getElementById('porsi30_uk').value = porsi30;
            // document.getElementById('margin_deposit').value = marginDeposit;
            // document.getElementById('nominal_uk').value = nominalUk;
            document.querySelector('.nominal-input').value = porsi70;
            calculateSum();
        }
    </script>
</body>

</html>