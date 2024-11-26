<?php
require 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal_prepost = $_POST['tanggal_prepost'];
    $tanggal_jatuh_tempo = $_POST['tanggal_jatuh_tempo'];
    $tahapan = $_POST['tahapan'];
    $jenis_prepost = $_POST['jenis_prepost'];
    $total = $_POST['total'];

    $errors = [];

    if (empty($tanggal_prepost)) {
        $errors[] = 'Tanggal Prepost is required';
    }

    if (empty($tanggal_jatuh_tempo)) {
        $errors[] = 'Tanggal Jatuh Tempo is required';
    }

    if (empty($tahapan)) {
        $errors[] = 'Tahapan is required';
    }

    if (empty($jenis_prepost)) {
        $errors[] = 'Jenis Prepost is required';
    }

    if (empty($total) || !is_numeric($total)) {
        $errors[] = 'Total is required and must be a number';
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
        exit;
    }

    // You can now use these variables to process the form data
    // For example, you can insert them into a database
$stmt = $conn->prepare("INSERT INTO pre (tanggal_prepost, tanggal_jatuh_tempo, tahapan, jenis_prepost, post, total) VALUES (?, ?, ?, ?, '0', ?)");
if ($stmt === false) {
    die('Prepare failed: ' . htmlspecialchars($conn->error));
}
$stmt->bind_param("ssssd", $tanggal_prepost, $tanggal_jatuh_tempo, $tahapan, $jenis_prepost, $total);

if ($stmt->execute()) {
    echo "<p style='color:green;'>Record added successfully</p>";
} else {
    echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
}

$stmt->close();

mysqli_close($conn);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prepost Input</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
</head>
<body>
    <div class="container mt-5">
        <form method="POST" action="">
            
            <div class="form-group">
                <label for="jenis_prepost">Jenis Pre :</label>
                <select class="form-control" id="jenis_prepost" name="jenis_prepost">
                    
                    <option value="" style="font-style:italic;">-- Pilih jenis Prefinancing --</option>
                    <option value="BNI - Prefinancing">BNI - Prefinancing</option>
                    <!-- <option value="BNI - Post Invoice Sinarmas">BNI - Post Invoice Sinarmas</option>
                    <option value="BNI - SCF Post PLN">BNI - SCF Post PLN</option> -->
                    <option value="BRI - KMK WA">BRI - KMK WA</option>
                    <option value="BRI - KMK CO TETAP">BRI - KMK CO TETAP</option>
                    <!-- <option value="BRI -  SCF QIN IKPP">BRI -  SCF QIN IKPP</option> -->
                    <!-- <option value="Pre Mandiri GEL - PLN">Pre Mandiri GEL - PLN"</option> -->
                    <!-- <option value="-">-</option> -->
                    <!-- <option value="Mandiri - SCF KKS - IKPP">Mandiri - SCF KKS - IKPP</option> -->
                    <option value="BCA - Time Loan Revolving 1">BCA - Time Loan Revolving 1</option>
                    <!-- <option value="BCA - Time Loan Revolving 2">BCA - Time Loan Revolving 2</option> -->
                    <!-- <option value="BCA - Kredit Lokal">BCA - Kredit Lokal</option> -->
                </select>
            </div>
            <div class="form-group">
                <label for="tanggal_prepost">Tanggal Prepost :</label>
                <input type="date" class="form-control" id="tanggal_prepost" name="tanggal_prepost">
            </div>
            <div class="form-group">
                <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo :</label>
                <input type="date" class="form-control" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo">
            </div>
            <script>
                document.getElementById('tanggal_prepost').addEventListener('change', function() {
                    var tanggalPrepost = new Date(this.value);
                    var tanggalJatuhTempo = new Date(tanggalPrepost);
                    tanggalJatuhTempo.setDate(tanggalPrepost.getDate() + 90);
                    document.getElementById('tanggal_jatuh_tempo').valueAsDate = tanggalJatuhTempo;
                });
            </script>
            <div class="form-group">
                <label for="tahapan">Tahapan :</label>
                <input type="number" class="form-control" id="tahapan" name="tahapan" maxlength="4" oninput="if(this.value.length > 4) this.value = this.value.slice(0, 4);" readonly>
            </div>
        <script>
            document.getElementById('jenis_prepost').addEventListener('change', function() {
                var jenisPrepost = this.value;
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'get_max_tahapan_pre.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        document.getElementById('tahapan').value = xhr.responseText;
                    }
                };
                xhr.send('jenis_prepost=' + encodeURIComponent(jenisPrepost));
            });
        </script>
        
           
            <div>
                <label for="total">Nilai Pencairan :</label>
                <input type="number" class="form-control" id="total" name="total" step="0.01" >
            </div>
            <br>
            <button type="submit" class="btn btn-info" id="submitBtn"><i class="bi bi-floppy"></i></button>
            <span>&nbsp;</span>
            <a class="btn btn-danger" href="prepost.php"><i class="bi bi-backspace"></i></a>
           
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
            <script>
                document.getElementById('submitBtn').addEventListener('click', function(event) {
                    event.preventDefault();
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "Do you want to submit the form?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, submit it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.querySelector('form').submit();
                        }
                    });
                });
            </script>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>