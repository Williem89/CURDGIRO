<?php
// Database connection
include 'koneksi.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $start_number = filter_input(INPUT_POST, 'Start_number', FILTER_SANITIZE_NUMBER_INT);
    $jumlah_giro = filter_input(INPUT_POST, 'Jumlah_giro', FILTER_SANITIZE_NUMBER_INT);
    $namabank = filter_input(INPUT_POST, 'namabank', FILTER_SANITIZE_STRING);
    $ac_number = filter_input(INPUT_POST, 'ac_number', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($start_number) || empty($jumlah_giro) || empty($namabank) || empty($ac_number)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        // Hitung end_number
        $end_number = $start_number + $jumlah_giro - 1;
        $giro_numbers = [];

        for ($i = $start_number; $i <= $end_number; $i++) {
            // Format nomor giro menjadi 3 digit
            $giro_number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $giro_numbers[] = $giro_number;

            // Cek apakah nomor giro sudah ada
            $check_stmt = $connection->prepare("SELECT COUNT(*) FROM data_giro WHERE nogiro = ?");
            $check_stmt->bind_param("s", $giro_number);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                echo "<script>alert('Nomor giro $giro_number sudah ada, tidak akan dimasukkan.');</script>";
                continue; // Lewati nomor yang sudah ada
            }

            // Prepare statement
            $stmt = $connection->prepare("INSERT INTO data_giro (nogiro, namabank, ac_number, statusgiro, created_by, created_at) 
                 VALUES (?, ?, ?, 'Unused', 'system', NOW())");

            if (!$stmt) {
                echo "<script>alert('Error preparing statement: " . $connection->error . "');</script>";
                continue;
            }

            // Bind parameters
            $stmt->bind_param("sss", $giro_number, $namabank, $ac_number);

            // Execute statement
            if (!$stmt->execute()) {
                echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
                continue;
            }

            // Close the statement
            $stmt->close();
        }

        // Menampilkan nomor giro yang berhasil dimasukkan
        echo "<script>alert('New records created successfully for giro numbers: " . implode(', ', $giro_numbers) . "');</script>";
    }
}

// Close the connection
$connection->close();
?>

<!-- HTML Form for input -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data Giro</title>
</head>
<body>
    <form method="POST" action="">
        <label for="Start_number">Mulai dari no. :</label>
        <input type="text" id="Start_number" name="Start_number"><br><br>

        <label for="Jumlah_giro">Jumlah Giro:</label>
        <input type="text" id="Jumlah_giro" name="Jumlah_giro"><br><br>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank"><br><br>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number"><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
