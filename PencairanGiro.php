<?php
session_start(); // Start the session to access user information

include 'koneksi.php';

// Assuming the user's information is stored in session
$user_logged_in = $_SESSION['username']; // Adjust this based on your session variable

// Ambil data dari tabel detail_giro dengan kondisi StatGiro = 'Issued'
$sql = "SELECT nogiro FROM detail_giro WHERE StatGiro = 'Issued'";
$result = $conn->query($sql);

// Set tanggal cair giro ke hari ini
$tanggal_cair_giro = ""; // Kosongkan nilai tanggal

// Variabel untuk pesan error
$error_message = "";

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_nogiro = $_POST['nogiro'];
    $tanggal_cair_giro = $_POST['tanggal_cair_giro'];

    // Validasi apakah nogiro dipilih
    if (empty($selected_nogiro)) {
        $error_message = "Nomor giro harus diisi.";
    } else {
        // Update tanggal_cair_giro dan StatGiro
        $updateSql = "UPDATE detail_giro SET tanggal_cair_giro = ?, StatGiro = 'Seatle', SeatleBy = ? WHERE nogiro = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssi", $tanggal_cair_giro, $user_logged_in, $selected_nogiro);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Data berhasil diperbarui!</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
    }
}

// Ambil kembali data untuk dropdown
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencairan Giro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #555;
        }
        select, input[type="date"], input[type="submit"], .btn-back {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .btn-back {
            background-color: #007BFF;
            color: white;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        #detail {
            margin-top: 20px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        .error {
            color: red;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
    <script>
        function getDetail(nogiro) {
            if (nogiro === "") {
                document.getElementById("detail").innerHTML = "";
                document.getElementById("tanggal_cair_giro").value = ""; // Kosongkan input tanggal
                return;
            }

            // Mengambil data menggunakan AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_detail.php?nogiro=" + nogiro, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);
                    var detailHTML = `
                        <h2>Detail Giro</h2>
                        <table>
                            <tr><th>No Giro</th><td>${nogiro}</td></tr>
                            <tr><th>Nomor Akun</th><td>${data.ac_number}</td></tr>
                            <tr><th>Nama Akun</th><td>${data.ac_name}</td></tr>
                            <tr><th>Nama Bank</th><td>${data.namabank}</td></tr>
                            <tr><th>Nama Penerima</th><td>${data.nama_penerima}</td></tr>
                            <tr><th>Akun Penerima</th><td>${data.ac_penerima}</td></tr>
                            <tr><th>Bank Penerima</th><td>${data.bank_penerima}</td></tr>
                            <tr><th>Nominal</th><td>Rp ${parseInt(data.nominal).toLocaleString()}</td></tr>
                            <tr><th>Status GIRO</th><td>${data.StatGiro}</td></tr>
                            <tr><th>Tanggal GIRO</th><td>${data.tanggal_giro}</td></tr>
                            <tr><th>Tanggal Jatuh Tempo</th><td>${data.tanggal_jatuh_tempo}</td></tr>
                            <tr><th>Tanggal Cair</th><td>${data.tanggal_cair_giro}</td></tr>
                            <tr><th>Tanggal Void</th><td>${data.TglVoid}</td></tr>
                            <tr><th>Tanggal Kembali ke Bank</th><td>${data.tglkembalikebank}</td></tr>
                            <tr><th>No PVR</th><td>${data.PVRNO}</td></tr>
                            <tr><th>Keterangan</th><td>${data.keterangan}</td></tr>
                        </table>
                    `;
                    document.getElementById("detail").innerHTML = detailHTML;
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
    <h1>Pencairan Giro</h1>
    <form action="" method="post">
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <label for="nogiro">Nomor Giro:</label>
        <select id="nogiro" name="nogiro" onchange="getDetail(this.value)">
            <option value="">Pilih Nomor Giro</option>
            <?php
            if ($result->num_rows > 0) {
                // Output data dari setiap baris
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["nogiro"] . '">' . $row["nogiro"] . '</option>';
                }
            } else {
                echo '<option value="">Tidak ada data</option>';
            }
            ?>
        </select>

        <label for="tanggal_cair_giro">Tanggal Cair Giro:</label>
        <input type="date" id="tanggal_cair_giro" name="tanggal_cair_giro" value="">
        
        <input type="submit" value="Submit">
        <a href="dashboard.php" class="btn-back">Back</a>
    </form>

    <div id="detail"></div>

    <?php
    // Tutup koneksi
    $conn->close();
    ?>
</body>
</html>
