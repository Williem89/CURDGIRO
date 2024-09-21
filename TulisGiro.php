<?php
// Database connection
include 'koneksi.php';

// Initialize an array to hold giro numbers and their corresponding bank and account details
$giro_data = [];

// Fetch all giro numbers along with their bank and account numbers from the database
$fetch_stmt = $conn->prepare("SELECT nogiro, namabank, ac_number FROM data_giro WHERE statusgiro = 'Unused'");
$fetch_stmt->execute();
$fetch_stmt->bind_result($giro_number, $namabank, $ac_number);

while ($fetch_stmt->fetch()) {
    $giro_data[$giro_number] = ['namabank' => $namabank, 'ac_number' => $ac_number];
}
$fetch_stmt->close();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $selected_giro_number = filter_input(INPUT_POST, 'giro_number', FILTER_SANITIZE_STRING);
    $tanggal_giro = filter_input(INPUT_POST, 'tanggal_giro', FILTER_SANITIZE_STRING);
    $tanggal_jatuh_tempo = filter_input(INPUT_POST, 'tanggal_jatuh_tempo', FILTER_SANITIZE_STRING);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_SANITIZE_STRING);
    $nama_penerima = filter_input(INPUT_POST, 'nama_penerima', FILTER_SANITIZE_STRING);
    $bank_penerima = filter_input(INPUT_POST, 'bank_penerima', FILTER_SANITIZE_STRING);
    $ac_penerima = filter_input(INPUT_POST, 'ac_penerima', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($selected_giro_number) || empty($tanggal_giro) || empty($tanggal_jatuh_tempo) || empty($nominal) || empty($nama_penerima) || empty($bank_penerima) || empty($ac_penerima)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        // Prepare statement to insert into the detail_giro table
        $stmt = $conn->prepare("INSERT INTO detail_giro (nogiro, tanggal_giro, tanggal_jatuh_tempo, nominal, nama_penerima, bank_penerima, ac_penerima, created_by, created_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'system', NOW())");

        if (!$stmt) {
            echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
        } else {
            // Bind parameters
            $stmt->bind_param("sssssss", $selected_giro_number, $tanggal_giro, $tanggal_jatuh_tempo, $nominal, $nama_penerima, $bank_penerima, $ac_penerima);

            // Execute statement
            if ($stmt->execute()) {
                // Update the status of the selected giro number to 'Used'
                $update_stmt = $conn->prepare("UPDATE data_giro SET statusgiro = 'Used' WHERE nogiro = ?");
                $update_stmt->bind_param("s", $selected_giro_number);
                $update_stmt->execute();
                $update_stmt->close();

                echo "<script>alert('New record created successfully and status updated to Used.');</script>";
            } else {
                echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Close the connection
$conn->close();
?>

<!-- HTML Form for input -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Giro</title>
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
            max-width: 500px;
            margin: auto;
        }
        label {
            display: block;
            margin: 10px 0 5px;
            color: #495057;
        }
        input[type="text"], input[type="date"], input[type="number"], select {
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
    </style>
    <script>
        function updateBankAndAccount() {
            const giroSelect = document.getElementById('giro_number');
            const selectedGiro = giroSelect.value;
            const bankInput = document.getElementById('namabank');
            const acNumberInput = document.getElementById('ac_number');

            if (selectedGiro) {
                const data = <?php echo json_encode($giro_data); ?>;
                bankInput.value = data[selectedGiro].namabank;
                acNumberInput.value = data[selectedGiro].ac_number;
            } else {
                bankInput.value = '';
                acNumberInput.value = '';
            }
        }
    </script>
</head>
<body>
    <h1>Input Data Giro</h1>
    <form method="POST" action="">
        <label for="giro_number">No Giro:</label>
        <select id="giro_number" name="giro_number" required onchange="updateBankAndAccount()">
            <option value="">Select No Giro</option>
            <?php foreach (array_keys($giro_data) as $giro): ?>
                <option value="<?php echo $giro; ?>"><?php echo $giro; ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" required readonly><br><br>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" required readonly><br><br>

        <label for="tanggal_giro">Tanggal Giro:</label>
        <input type="date" id="tanggal_giro" name="tanggal_giro" required><br><br>

        <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo:</label>
        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required><br><br>

        <label for="nominal">Nominal:</label>
        <input type="number" id="nominal" name="nominal" required><br><br>

        <label for="nama_penerima">Nama Penerima:</label>
        <input type="text" id="nama_penerima" name="nama_penerima" required><br><br>

        <label for="bank_penerima">Bank Penerima:</label>
        <input type="text" id="bank_penerima" name="bank_penerima" required><br><br>

        <label for="ac_penerima">Account Penerima:</label>
        <input type="text" id="ac_penerima" name="ac_penerima" required><br><br>

        <input type="submit" value="Submit">
    </form>
</body>
</html>
