<?php
session_start(); // Start the session to access user information

include 'koneksi.php';

// Assuming the user's information is stored in session
$user_logged_in = $_SESSION['username']; // Adjust this based on your session variable

// Ambil data dari tabel detail_cek dengan kondisi Statcek = 'Issued'
$sql = "SELECT nocek FROM detail_cek WHERE Statcek = 'Issued'";
$result = $conn->query($sql);

// Set tanggal cair cek ke hari ini
$tanggal_cair_cek = ""; // Kosongkan nilai tanggal

// Variabel untuk pesan error
$error_message = "";

// Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_nocek = $_POST['nocek'];
    $tanggal_cair_cek = $_POST['tanggal_cair_cek'];

    // Validasi apakah nocek dipilih
    if (empty($selected_nocek)) {
        $error_message = "Nomor cek harus diisi.";
    } else {
        // Update tanggal_cair_cek dan Statcek
        $updateSql = "UPDATE detail_cek SET tanggal_cair_cek = ?, Statcek = 'Posted', PostedBy = ? WHERE nocek = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssi", $tanggal_cair_cek, $user_logged_in, $selected_nocek);
        
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
    <title>Pencairan cek</title>
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
        function getDetail(nocek) {
            if (nocek === "") {
                document.getElementById("detail").innerHTML = "";
                document.getElementById("tanggal_cair_cek").value = ""; // Kosongkan input tanggal
                return;
            }

            // Mengambil data menggunakan AJAX
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "get_detail.php?nocek=" + nocek, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var data = JSON.parse(xhr.responseText);
                    var detailHTML = `
                        <h2>Detail cek</h2>
                        <table>
                            <tr><th>No cek</th><td>${nocek}</td></tr>
                            <tr><th>Nomor Akun</th><td>${data.ac_number}</td></tr>
                            <tr><th>Nama Akun</th><td>${data.ac_name}</td></tr>
                            <tr><th>Nama Bank</th><td>${data.namabank}</td></tr>
                            <tr><th>Nama Penerima</th><td>${data.nama_penerima}</td></tr>
                            <tr><th>Akun Penerima</th><td>${data.ac_penerima}</td></tr>
                            <tr><th>Bank Penerima</th><td>${data.bank_penerima}</td></tr>
                            <tr><th>Nominal</th><td>Rp ${parseInt(data.nominal).toLocaleString()}</td></tr>
                            <tr><th>Status cek</th><td>${data.Statcek}</td></tr>
                            <tr><th>Tanggal cek</th><td>${data.tanggal_cek}</td></tr>
                            <tr><th>Tanggal Jatuh Tempo</th><td>${data.tanggal_jatuh_tempo}</td></tr>
                            <tr><th>Tanggal Cair</th><td>${data.tanggal_cair_cek}</td></tr>
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
    <h1>Pencairan cek</h1>
    <form action="" method="post">
        <?php if ($error_message): ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <label for="nocek">Nomor cek:</label>
        <select id="nocek" name="nocek" onchange="getDetail(this.value)">
            <option value="">Pilih Nomor cek</option>
            <?php
            if ($result->num_rows > 0) {
                // Output data dari setiap baris
                while ($row = $result->fetch_assoc()) {
                    echo '<option value="' . $row["nocek"] . '">' . $row["nocek"] . '</option>';
                }
            } else {
                echo '<option value="">Tidak ada data</option>';
            }
            ?>
        </select>

        <label for="tanggal_cair_cek">Tanggal Cair cek:</label>
        <input type="date" id="tanggal_cair_cek" name="tanggal_cair_cek" value="">
        
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
