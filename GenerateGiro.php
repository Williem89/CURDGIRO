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
    $ac_name = filter_input(INPUT_POST, 'ac_name', FILTER_SANITIZE_STRING);

    // Check for empty fields
    if (empty($start_number) || empty($jumlah_giro) || empty($namabank) || empty($ac_number) || empty($ac_name)) {
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
            $stmt = $conn->prepare("INSERT INTO data_giro (nogiro, namabank, ac_number, ac_name, statusgiro, created_by, created_at) 
                VALUES (?, ?, ?, ?, 'Unused', 'system', NOW())");

            if ($stmt) {
                // Bind parameters for the insert
                $stmt->bind_param("ssss", $giro_number, $namabank, $ac_number, $ac_name);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data Giro</title>
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
            max-width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }

        input[type="number"],
        input[type="text"] {
            width: 100%;
            padding: 5px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"],
        a  {
            display: inline-block;
            width: 25%; /* Make buttons the same width */
            padding: 8px 5px; /* Adjust padding for larger buttons */
            border-radius: 8px; /* Rounded corners */
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.2s; /* Smooth transition */
            margin: 6px 0; /* Space between buttons */
            border: none; /* Remove border */
            font-size: 16px; /* Increase font size */
        }

        input[type="submit"] {
            background-color: #28a745; /* Green color */
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #218838; /* Darker green on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        a {
            color: white;
            background-color: #007bff; /* Blue color */
            margin-top: 15px; /* Add margin to create space above this button */
        }

        a:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
        }
    </style>
</head>
<body>
    <h1>Generate Giro</h1>
    <form method="POST" action="">
        <label for="Start_number">Mulai dari no. :</label>
        <input type="number" id="Start_number" name="Start_number" required>

        <label for="Jumlah_giro">Jumlah Giro:</label>
        <input type="number" id="Jumlah_giro" name="Jumlah_giro" required>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" required>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" required>

        <label for="ac_name">Account Name:</label>
        <input type="text" id="ac_name" name="ac_name" required>

        <input type="submit" value="Submit">
        <a href="dashboard.php">Kembali</a>
    </form>
</body>
</html>
