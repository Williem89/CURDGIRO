<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_post = $_POST['tanggal_post'];
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'];
    $jenis_post = $_POST['jenis_post'];
    $tahapan_post = $_POST['tahapan_post'];
    $entries = $_POST['entries']; // Array of entries
    $released = $_POST['released'];
    $ket = $_POST['ket'];
    $errors = [];
    $jenis_pre = $_POST['jenis_pre'];

    if (empty($tanggal_post)) {
        $errors[] = 'Tanggal Prepost is required';
    }

    if (empty($tanggal_jatuh_tempo)) {
        $errors[] = 'Tanggal Jatuh Tempo is required';
    }

    if (empty($entries) || !is_array($entries)) {
        $errors[] = 'At least one entry is required';
    } else {
        foreach ($entries as $entry) {
            if (empty($entry['tahapan'])) {
                $errors[] = 'Tahapan is required for each entry';
                break;
            }
            if (empty($entry['nominal']) || !is_numeric($entry['nominal'])) {
                $errors[] = 'Nominal is required and must be a number for each entry';
                break;
            }
        }
    }

    if (empty($jenis_post)) {
        $errors[] = 'Jenis Prepost is required';
    }

    if (empty($released) || !is_numeric($released)) {
        $errors[] = 'Nilai Pencairan is required and must be a number';
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        exit;
    }

    $total_nominal = 0;
    foreach ($entries as $entry) {
        $total_nominal += (float) $entry['nominal']; // Hitung total nominal
    }

    if ($total_nominal != (float) $released) {
        echo "<p style='color:red;'>Total nominal (Rp. " . number_format($total_nominal, 2, ',', '.') . ") tidak sesuai dengan nilai released (Rp. " . number_format($released, 2, ',', '.') . ").</p>";
        exit;
    }

    if ((float)$released <= 0) {
        echo "<p style='color:red;'>Released harus diisi dan lebih besar dari 0.</p>";
        exit;
    }

    // Insert data into database
    $stmt = $conn->prepare("INSERT INTO post (tanggal_post, tanggal_jatuh_tempo, tahapan_post, jenis_post, released, ket) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $tanggal_post, $tanggal_jatuh_tempo, $tahapan_post,  $jenis_post, $released, $ket);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Record added successfully</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}

// Handle AJAX request to get 'tahapan' options
if (isset($_GET['action']) && $_GET['action'] == 'getTahapan' && isset($_GET['jenis_pre'])) {
    $jenis_pre = $_GET['jenis_pre'];

    $stmt = $conn->prepare("SELECT DISTINCT tahapan FROM pre WHERE jenis_prepost = ?");
    $stmt->bind_param("s", $jenis_pre);
    $stmt->execute();
    $result = $stmt->get_result();

    $options = [];
    while ($row = $result->fetch_assoc()) {
        $options[] =  $row['tahapan'];
    }
    echo json_encode($options);
    exit;
}

// echo "<pre>";
// print_r($entries);
// echo "</pre>";


foreach ($entries as $entry) {
    $tahapan = $entry['tahapan'];
    $nominal = $entry['nominal'];

    if (empty($tahapan) || !is_numeric($nominal)) {
        echo "<p style='color:red;'>Invalid data for entry: Tahapan {$tahapan}</p>";
        continue;
    }
    
    $add_tutuppre_stmt = $conn->prepare("INSERT INTO tutup_pre (tanggal_post, jenis_post, tahapan_post, jenis_pre, tahapan_pre, nominal) VALUES (?, ?, ?, ?, ?, ?)");
    $add_tutuppre_stmt->bind_param("sssssd", $tanggal_post, $jenis_post, $tahapan_post, $jenis_pre, $tahapan, $nominal);

    if ($update_stmt->execute() && $add_tutuppre_stmt->execute()) {
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
            <H2>Proses Pengajuan Post Financing</H2>
            <br>
            <div style="border: 1px solid; padding: 30px;">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="jenis_post">Jenis Post Financing :</label>
                            <select class="form-control" id="jenis_post" name="jenis_post">
                                <option value="">-- Select Jenis Post Financing --</option>
                                <option value="BNI - Post Invoice Sinarmas">BNI - Post Invoice Sinarmas</option>
                                <option value="BNI - SCF Post PLN">BNI - SCF Post PLN</option>
                                <option value="BCA - Time Loan Revolving 2">BCA - Time Loan Revolving 2</option>
                                <option value="BCA - Kredit Lokal">BCA - Kredit Lokal</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="tahapan_post">Tahap Post :</label>
                            <input type="number" class="form-control" id="tahapan_post" name="tahapan_post" maxlength="4" oninput="if(this.value.length > 4) this.value = this.value.slice(0, 4);" readonly>
                        </div>
                        <script>
                            document.getElementById('jenis_post').addEventListener('change', async function() {
                                const jenisPrepost = this.value;
                                try {
                                    const response = await fetch('get_max_tahapan_post.php', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `jenis_post=${encodeURIComponent(jenisPrepost)}`
                                    });
                                    if (response.ok) {
                                        const result = await response.text();
                                        document.getElementById('tahapan_post').value = result;
                                    } else {
                                        console.error('Failed to fetch data');
                                    }
                                } catch (error) {
                                    console.error('Error:', error);
                                }
                            });
                        </script>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="tanggal_post">Tanggal Prepost :</label>
                            <input type="date" class="form-control" id="tanggal_post" name="tanggal_post">
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <div class="form-group">
                                <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo :</label>
                                <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo">
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <label for="released_show">Nilai Pencairan :</label>
                    <input type="text" class="form-control" id="released_show" name="released_show" oninput="formatNumber(this); syncReleasedValue(this)">
                    <input type="number" class="form-control" id="released" name="released" hidden>
                </div>
                <script>
                    function syncReleasedValue(input) {
                        const numericValue = input.value.replace(/[^0-9.]/g, '');
                        document.getElementById('released').value = numericValue;
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
                            <label for="jenis_pre"><strong>Untuk pembayaran Pre <strong>:</label>
                            <select class="form-control jenis_pre" name="jenis_pre">

                                <option value="" style="font-style:italic;">-- Pilih jenis Prefinancing --</option>
                                <option value="BNI - Prefinancing">BNI - Prefinancing</option>
                                <option value="BRI - KMK WA">BRI - KMK WA</option>
                                <option value="BRI - KMK CO TETAP">BRI - KMK CO TETAP</option>
                                <option value="BRI -  SCF QIN IKPP">BRI - SCF QIN IKPP</option>
                                <option value="Pre Mandiri GEL - PLN">Pre Mandiri GEL - PLN"</option>
                                <option value="-">-</option>
                                <option value="Mandiri - SCF KKS - IKPP">Mandiri - SCF KKS - IKPP</option>
                                <option value="BCA - Time Loan Revolving 1">BCA - Time Loan Revolving 1</option>

                            </select>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="tahapan">Tahap :</label>
                                <select class="form-control tahapan-select" name="entries[0][tahapan]">
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
                        const jenisPre = document.querySelector('.jenis_pre').value;
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
                <a class="btn btn-danger" href="prepost.php#post_financing"><i class="bi bi-backspace"></i></a>
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

        document.querySelector('.jenis_pre').addEventListener('change', function() {
            console.log(this.value);
            var jenisPrepost = this.value;
            fetch('?action=getTahapan&jenis_pre=' + encodeURIComponent(jenisPrepost))
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
            var jenisPrepost = document.querySelector('.jenis_pre').value;
            fetch('?action=getTahapan&jenis_pre=' + encodeURIComponent(jenisPrepost))
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