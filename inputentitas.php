<?php
// Sertakan koneksi database
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_entitas = trim($_POST['nama_entitas']);
    $keterangan = trim($_POST['keterangan']);

    // Validasi input
    if (empty($nama_entitas)) {
        $error_message = "Nama entitas tidak boleh kosong.";
    } else {
        // Cek apakah nama_entitas sudah ada
        $stmt = $conn->prepare("SELECT COUNT(*) FROM list_entitas WHERE nama_entitas = ?");
        $stmt->bind_param("s", $nama_entitas);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();

        if ($count > 0) {
            $error_message = "Error: Nama entitas sudah ada.";
        } else {
            // Masukkan data ke database
            try {
                $stmt->close(); // Menutup statement sebelumnya sebelum membuat yang baru
                $stmt = $conn->prepare("INSERT INTO list_entitas (nama_entitas, keterangan) VALUES (?, ?)");
                $stmt->bind_param("ss", $nama_entitas, $keterangan);
                $stmt->execute();
                $success_message = "Data berhasil ditambahkan!";
            } catch (mysqli_sql_exception $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        }

        // Menutup statement jika masih ada
        if ($stmt) {
            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Entitas</title>
    <link rel="icon" type="image/x-icon" href="img/icon.png">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            color: #333;
        }
        form {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"],
        .btn-back {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            width: 48%;
            display: inline-block;
            text-align: center;
        }
        input[type="submit"]:hover,
        .btn-back:hover {
            background-color: #4cae4c;
        }
        .btn-back {
            background-color: #007bff;
        }
        .btn-back:hover {
            background-color: #0069d9;
        }
        .message {
            margin: 10px 0;
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Input Entitas</h1>

    <?php if (isset($error_message)): ?>
        <div class="message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)): ?>
        <div class="success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nama_entitas">Nama Entitas:</label>
        <input type="text" name="nama_entitas" required>
        
        <label for="keterangan">Keterangan (opsional):</label>
        <textarea name="keterangan"></textarea>
        
        <input type="submit" value="Simpan">
        <a href="dashboard.php" class="btn-back">Kembali</a>
    </form>
</body>
</html>
