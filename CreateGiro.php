<?php
// Include the database connection
include 'koneksi.php'; // Make sure this sets the $conn variable

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $start_number = filter_input(INPUT_POST, 'Start_number', FILTER_SANITIZE_NUMBER_INT);
    $jumlah_giro = filter_input(INPUT_POST, 'Jumlah_giro', FILTER_SANITIZE_NUMBER_INT);
    $namabank = filter_input(INPUT_POST, 'namabank', FILTER_SANITIZE_STRING);
    $ac_number = filter_input(INPUT_POST, 'ac_number', FILTER_SANITIZE_STRING);

    // Check for empty fields
    if (empty($start_number) || empty($jumlah_giro) || empty($namabank) || empty($ac_number)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        // Calculate the last giro number
        $end_number = $start_number + $jumlah_giro - 1;
        $giro_numbers = [];

        // Loop through the specified range
        for ($i = $start_number; $i <= $end_number; $i++) {
            // Format the giro number to three digits
            $giro_number = str_pad($i, 3, '0', STR_PAD_LEFT);
            $giro_numbers[] = $giro_number;

            // Check if the giro number already exists in the database
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM data_giro WHERE nogiro = ?");
            if ($check_stmt) {
                $check_stmt->bind_param("s", $giro_number);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->close();

                // Skip if the number already exists
                if ($count > 0) {
                    echo "<script>alert('Nomor giro $giro_number sudah ada, tidak akan dimasukkan.');</script>";
                    continue;
                }
            } else {
                echo "<script>alert('Error preparing check statement: " . $conn->error . "');</script>";
                continue;
            }

            // Prepare the insertion statement
            $stmt = $conn->prepare("INSERT INTO data_giro (nogiro, namabank, ac_number, statusgiro, created_by, created_at) 
                VALUES (?, ?, ?, 'Unused', 'system', NOW())");

            if ($stmt) {
                // Bind parameters for the insert
                $stmt->bind_param("sss", $giro_number, $namabank, $ac_number);

                // Execute the statement
                if (!$stmt->execute()) {
                    echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
                }

                // Close the prepared statement
                $stmt->close();
            } else {
                echo "<script>alert('Error preparing insertion statement: " . $conn->error . "');</script>";
            }
        }

        // Display success message for inserted records
        if (!empty($giro_numbers)) {
            echo "<script>alert('New records created successfully for giro numbers: " . implode(', ', $giro_numbers) . "');</script>";
        }
    }
}

// Close the database connection
if (isset($conn) && $conn) {
    $conn->close();
}
?>

<!-- HTML Form for input -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data Giro</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #343a40;
        }
        form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 400px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #495057;
        }
        input[type="text"], input[type="number"] {
            width: calc(100% - 20px);
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .alert {
            color: red;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Input Data Giro</h1>
    <form method="POST" action="">
        <label for="Start_number">Mulai dari no. :</label>
        <input type="number" id="Start_number" name="Start_number" required>

        <label for="Jumlah_giro">Jumlah Giro:</label>
        <input type="number" id="Jumlah_giro" name="Jumlah_giro" required>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" required>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" required>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
