<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tgl = $_POST['tgl'];
    $sumber_dana = $_POST['sumber_dana'];
    $nominal_terima = $_POST['nominal_terima'];
    $entries = $_POST['entries'];
    $ket = $_POST['ket'];

    // Validate and process the form data
    if (empty($tgl) || empty($sumber_dana) || empty($nominal_terima) || empty($entries)) {
        echo "<p style='color:red;'>Please fill in all required fields.</p>";
    } else {
        // Insert the data into the database
        $stmt = $conn->prepare("INSERT INTO um (tgl, sumber_dana, nominal_terima, ket) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $tgl, $sumber_dana, $nominal_terima, $ket);

        if ($stmt->execute()) {
            echo "<p style='color:green;'>Data successfully inserted.</p>";
        } else {
            echo "<p style='color:red;'>Error inserting data: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
}
// Handle AJAX request to get 'tahapan' options
if (isset($_GET['action']) && $_GET['action'] == 'getTahapan' && isset($_GET['jenis_post'])) {
    $jenis_post = $_GET['jenis_post'];

    $stmt = $conn->prepare("SELECT DISTINCT tahapan_post FROM post WHERE jenis_post = ?");
    $stmt->bind_param("s", $jenis_post);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] =  $row['tahapan_post'];
    }
    echo json_encode($options);
    exit;
}



foreach ($entries as $entry) {
    $tahapan = $entry['tahapan'];
    $nominal = $entry['nominal'];

    if (empty($tahapan) || !is_numeric($nominal)) {
        echo "<p style='color:red;'>Invalid data for entry: Tahapan {$tahapan}</p>";
        continue;
    }

    // Proses SQL untuk memperbarui data
    $update_stmt = $conn->prepare("
            UPDATE post 
            SET os = COALESCE(os, 0) + ? 
            WHERE jenis_post = ? AND tahapan_post = ?
        ");
    $update_stmt->bind_param("dss", $nominal, $jenis_post, $tahapan);

    if ($update_stmt->execute()) {
        echo "<p style='color:green;'>Successfully updated Tahapan {$tahapan} with additional value {$nominal}</p>";
    } else {
        echo "<p style='color:red;'>Error updating record for Tahapan {$tahapan}: " . $update_stmt->error . "</p>";
    }
    $update_stmt->close();
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Financing Create</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>

    <div class="container mt-5">
        <form method="POST" action="">
            <H2>Uang Masuk</H2>
            <br>
            <div style="border: 1px solid; padding: 30px;">

                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="tgl"><strong>Tanggal Uang Masuk :</strong></label>
                            <input type="date" class="form-control" id="tgl" name="tgl">
                        </div>
                    </div>
                    <div class="col">

                    </div>
                </div>
                <div class="form-group">
                    <label for="sumber_dana"><strong>Sumber Dana : <strong>:</label>
                    <select class="form-control sumber_dana" name="sumber_dana">
                        <option value="">-- Sumber Dana --</option>
                        <option value="PLN">PLN</option>
                        <option value="Sinarmas ">Sinarmas</option>
                    </select>
                </div>
                <div>
                    <label for="nominal_terima_show">Nominal_terima :</label>
                    <input type="text" class="form-control" id="nominal_terima_show" name="nominal_terima_show" oninput="formatNumber(this); syncnominal_terimaValue(this)">
                    <input type="number" class="form-control" id="nominal_terima" name="nominal_terima" hidden>
                </div>
                <script>
                    function syncnominal_terimaValue(input) {
                        const numericValue = input.value.replace(/[^0-9.]/g, '');
                        document.getElementById('nominal_terima').value = numericValue;
                    }
                </script>
            </div>
            <br>
            <div style="border: 1px solid; padding: 30px;">
                <button type="button" class="btn btn-info mt-3" id="add-entry"><i class="bi bi-plus-square"></i></button>
                <div id="entries-container">
                    <br>
                    <div class="entry">

                        <div class="form-group">
                            <label for="jenis_post"><strong>Untuk Penututpan Post : </strong></label>
                            <select class="form-control jenis_post" name="jenis_post">
                                <option value="">-- Select Jenis Post Financing --</option>
                                <option value="BNI - Post Invoice Sinarmas">BNI - Post Invoice Sinarmas</option>
                                <option value="BNI - SCF Post PLN">BNI - SCF Post PLN</option>
                                <option value="BCA - Time Loan Revolving 2">BCA - Time Loan Revolving 2</option>
                                <option value="BCA - Kredit Lokal">BCA - Kredit Lokal</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tahapan">Tahap :</label>
                                <select class="form-control tahapan-select" name="entries[0][tahapan]">
                                    <!-- Options will be loaded dynamically -->
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="nominal">Nominal :</label>
                                <input type="text" class="form-control" name="entries[0][nominal_show]" oninput="formatNumber(this); syncnominalValue(this)" placeholder="0.00">
                                <input type="number" class="form-control" name="entries[0][nominal]" hidden>
                            </div>
                            <script>
                                function syncnominalValue(input) {
                                    const numericValue = input.value.replace(/[^0-9.]/g, '');
                                    const hiddenInput = input.nextElementSibling;
                                    hiddenInput.value = numericValue;
                                }
                            </script>
                        </div>

                        <button type="button" class="btn btn-danger remove-entry"><i class="bi bi-trash"></i></button>
                    </div>

                </div>
                <div class="form-group">
                    <label for="ket">Keterangan :</label>
                    <textarea class="form-control" id="ket" name="ket" rows="3"></textarea>
                </div>
                <script>
                    function getket() {
                        const jenisPre = document.querySelector('.jenis_post').value;
                        const entries = document.querySelectorAll('.entry');
                        const kets = [`Untuk Pembayaran Pre : ${jenisPre}`];

                        entries.forEach((entry, index) => {
                            const tahapanSelect = entry.querySelector('.tahapan-select');
                            const tahapan = tahapanSelect ? tahapanSelect.value : '';
                            const nominalInput = entry.querySelector('input[name^="entries"][name$="[nominal_show]"]');
                            const nominal = nominalInput ? nominalInput.value : '';

                            if (tahapan || nominal) {
                                kets.push(`#${index + 1} : ${tahapan}, Dengan Nominal Penutupan: Rp. ${nominal} `);

                            }
                        });

                        return kets.join('\n');
                    }

                    // Update ket on initial load
                    document.getElementById('ket').value = getket();

                    // Add listener to the single jenis_pre select
                    const jenisPreSelect = document.querySelector('.jenis_pre');
                    if (jenisPreSelect) {
                        jenisPreSelect.addEventListener('change', () => {
                            document.getElementById('ket').value = getket();
                        });
                    }

                    // Function to add listeners to an entry (excluding jenis_pre)
                    function addEntryListeners(entry) {
                        const inputs = [
                            entry.querySelector('.tahapan-select'),
                            entry.querySelector('input[name$="[nominal_show]"]')
                        ];

                        inputs.forEach(input => {
                            if (input) {
                                input.addEventListener('change', () => {
                                    document.getElementById('ket').value = getket();
                                });
                                input.addEventListener('input', () => {
                                    document.getElementById('ket').value = getket();
                                });
                            }
                        });
                    }

                    // Add listeners to initial entry
                    document.querySelectorAll('.entry').forEach(entry => {
                        addEntryListeners(entry);
                    });

                    // Update for new entries
                    document.getElementById('add-entry').addEventListener('click', function() {
                        setTimeout(function() {
                            const entries = document.querySelectorAll('.entry');
                            const newEntry = entries[entries.length - 1];
                            addEntryListeners(newEntry);
                        }, 100);
                    });

                    // Update ket when an entry is removed
                    document.addEventListener('click', function(e) {
                        if (e.target.classList.contains('remove-entry') || e.target.closest('.remove-entry')) {
                            setTimeout(() => {
                                document.getElementById('ket').value = getket();
                            }, 100);
                        }
                    });
                </script>
            </div>
            <br>
            <div>
                <button type="submit" class="btn btn-primary">Submit</button>
                <span>&nbsp;</span>
                <a class="btn btn-danger" href="prepost.php#uang_masuk"><i class="bi bi-backspace"></i></a>

            </div>
            <br>
        </form>
        <p id="total-nominal-feedback" style="font-weight: bold; color: red;">Total Nominal: 0</p>

    </div>
    <script>
        //number format
        function formatNumber(input) {
            // Remove all non-numeric characters except the decimal point
            let value = input.value.replace(/[^0-9.]/g, '');

            // Split the number into whole and decimal parts
            let parts = value.split('.');
            let integerPart = parts[0];
            let decimalPart = parts.length > 1 ? '.' + parts[1].slice(0, 2) : '';

            // Format the integer part with commas
            integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            // Set the formatted value back to the input
            input.value = integerPart + decimalPart;
        }

        document.querySelector('.jenis_post').addEventListener('change', function() {
            console.log(this.value);
            var jenisPrepost = this.value;
            fetch('?action=getTahapan&jenis_post=' + encodeURIComponent(jenisPrepost))
                .then(response => response.json())
                .then(data => {
                    updateTahapanOptions(data);
                });
        });

        function updateTahapanOptions(options) {
            var tahapanSelects = document.querySelectorAll('.tahapan-select');
            tahapanSelects.forEach(function(select) {
                var currentValue = select.value;
                select.innerHTML = '';
                options.forEach(function(tahapan) {
                    var option = document.createElement('option');
                    option.value = tahapan;
                    option.textContent = tahapan;
                    select.appendChild(option);
                });
                select.value = currentValue;
            });
        }

        document.getElementById('add-entry').addEventListener('click', function() {
            var entriesContainer = document.getElementById('entries-container');
            var entryCount = entriesContainer.getElementsByClassName('entry').length;
            var newEntry = document.createElement('div');
            newEntry.className = 'entry';
            newEntry.innerHTML = `
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="tahapan"><strong>Tahapan :</strong></label>
                            <select class="form-control tahapan-select" name="entries[${entryCount}][tahapan]">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="nominal"><strong>Nominal :</strong></label>
                            <input type="text" class="form-control" name="entries[${entryCount}][nominal_show]" oninput="formatNumber(this); syncnominalValue(this)" placeholder="0.00">
                            <input type="hidden" class="form-control" name="entries[${entryCount}][nominal]" >
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger remove-entry"><i class="bi bi-trash"></i></button>
                `;
            entriesContainer.appendChild(newEntry);
            // Populate tahapan options for the new entry
            var jenisPrepost = document.querySelector('.jenis_post').value;
            fetch('?action=getTahapan&jenis_post=' + encodeURIComponent(jenisPrepost))
                .then(response => response.json())
                .then(function(data) {
                    updateTahapanOptions(data);
                });
            // Add event listener for delete button
            newEntry.querySelector('.remove-entry').addEventListener('click', function() {
                this.parentElement.remove();
            });
        });
        // Add event listener for delete button on initial entry
        document.querySelector('.remove-entry').addEventListener('click', function() {
            this.parentElement.remove();
        });
        // // Ensure tahapan options are loaded on page load
        // document.getElementById('jenis_pre').dispatchEvent(new Event('change'));

        document.getElementById('tanggal_post').addEventListener('change', function() {
            var tanggalPrepost = new Date(this.value);
            var tanggalJatuhTempo = new Date(tanggalPrepost);
            tanggalJatuhTempo.setDate(tanggalPrepost.getDate() + 120);
            document.getElementById('tanggal_jatuh_tempo').valueAsDate = tanggalJatuhTempo;
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('tanggal_post').addEventListener('change', function() {
            var tanggalPrepost = new Date(this.value);
            var tanggalJatuhTempo = new Date(tanggalPrepost);
            tanggalJatuhTempo.setDate(tanggalPrepost.getDate() + 120);
            document.getElementById('tanggal_jatuh_tempo').valueAsDate = tanggalJatuhTempo;
        });

        function validateForm() {
            // Ambil nilai dari input
            const releasedValue = parseFloat(document.getElementById('released').value || 0); // Nilai released (hidden)
            const nominalInputs = document.querySelectorAll('input[name$="[nominal]"]'); // Semua nominal
            const feedback = document.getElementById('total-nominal-feedback');
            const submitButton = document.querySelector('button[type="submit"]');

            let totalNominal = 0;

            // Hitung total nominal
            nominalInputs.forEach(input => {
                totalNominal += parseFloat(input.value || 0);
            });

            // Perbarui feedback
            feedback.textContent = `Total Nominal: ${totalNominal.toLocaleString()} / Released: ${releasedValue.toLocaleString()}`;

            // Logika validasi
            if (releasedValue > 0 && totalNominal > 0) {
                if (totalNominal === releasedValue) {
                    feedback.style.color = 'green';
                    submitButton.disabled = false; // Aktifkan tombol submit
                } else {
                    feedback.style.color = 'red';
                    submitButton.disabled = true; // Nonaktifkan tombol submit
                }
            } else {
                feedback.textContent = `Pastikan nilai Released dan semua Nominal diisi dengan benar.`;
                feedback.style.color = 'red';
                submitButton.disabled = true; // Nonaktifkan tombol submit
            }
        }

        // Tambahkan event listener untuk validasi real-time
        document.getElementById('released_show').addEventListener('input', function() {
            syncReleasedValue(this); // Pastikan hidden input diperbarui
            validateForm(); // Panggil validasi
        });

        document.getElementById('entries-container').addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('[nominal_show]')) {
                validateForm(); // Panggil validasi saat ada perubahan pada nominal
            }
        });

        // Validasi awal saat halaman dimuat
        document.addEventListener('DOMContentLoaded', validateForm);


        document.getElementById('entries-container').addEventListener('input', function(e) {
            if (e.target.name && e.target.name.includes('[nominal]')) {
                validateForm();
            }
        });
    </script>
</body>
</html>