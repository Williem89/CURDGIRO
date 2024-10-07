<?php
// Database connection
include 'koneksi.php';

// Start session
session_start();

// Assuming the username is stored in session after user login
$createdBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';

// Initialize arrays to hold giro numbers and customer data
$giro_data = [];
$customer_data = [];

// Fetch all giro numbers along with their bank and account numbers from the database
$fetch_stmt = $conn->prepare("SELECT nogiro, namabank, ac_number FROM data_giro WHERE statusgiro = 'Unused'");
$fetch_stmt->execute();
$fetch_stmt->bind_result($giro_number, $namabank, $ac_number);

while ($fetch_stmt->fetch()) {
    $giro_data[$giro_number] = ['namabank' => $namabank, 'ac_number' => $ac_number];
}
$fetch_stmt->close();

// Fetch all customers from the database
$customer_stmt = $conn->prepare("SELECT no_cust, ac_cust, bank_cust, nama_cust FROM list_customer");
$customer_stmt->execute();
$customer_stmt->bind_result($no_cust, $ac_cust, $bank_cust, $nama_cust);

while ($customer_stmt->fetch()) {
    $customer_data[$no_cust] = ['ac_number' => $ac_cust, 'bank_cust' => $bank_cust, 'nama_cust' => $nama_cust];
}
$customer_stmt->close();

// Check if form is submitted
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $selected_giro_number = filter_input(INPUT_POST, 'giro_number', FILTER_SANITIZE_STRING);
    $no_cust = filter_input(INPUT_POST, 'no_cust', FILTER_SANITIZE_STRING);
    $tanggal_giro = filter_input(INPUT_POST, 'tanggal_giro', FILTER_SANITIZE_STRING);
    $tanggal_jatuh_tempo = filter_input(INPUT_POST, 'tanggal_jatuh_tempo', FILTER_SANITIZE_STRING);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $Keterangan = filter_input(INPUT_POST, 'Keterangan', FILTER_SANITIZE_STRING);
    $PVRNo = filter_input(INPUT_POST, 'PVRNo', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($selected_giro_number) || empty($no_cust) || empty($tanggal_giro) || empty($tanggal_jatuh_tempo) || 
        empty($nominal) || empty($customer_data[$no_cust]['nama_cust'])) {
        $message = 'Error: All fields are required.';
    } else if ($nominal <= 0) {
        $message = 'Error: Nominal must be greater than zero.';
    } else if (!is_uploaded_file($_FILES['foto_giro']['tmp_name'])) {
        $message = 'Error: No file input';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        $filePath = $_FILES['foto_giro']['tmp_name'];
        $fileData = file_get_contents($filePath);
        $base64File = base64_encode($fileData);
        try {
            // Prepare statement to insert into the detail_giro table
            $stmt = $conn->prepare("INSERT INTO detail_giro (nogiro, tanggal_giro, tanggal_jatuh_tempo, nominal, 
                nama_penerima, ac_penerima, bank_penerima, Keterangan, PVRNo, StatGiro, image_giro, created_by, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            // Check if statement preparation was successful
            if (!$stmt) {
                throw new Exception("Error preparing statement: " . $conn->error);
            }

            // Get customer details
            $ac_penerima = $customer_data[$no_cust]['ac_number'];
            $bank_penerima = $customer_data[$no_cust]['bank_cust'];
            $nama_penerima = $customer_data[$no_cust]['nama_cust'];

            // Bind parameters
            $statGiro = 'Issued';  // Set StatGiro to 'Issued'

            $stmt->bind_param("ssssssssssss", 
                $selected_giro_number, 
                $tanggal_giro, 
                $tanggal_jatuh_tempo, 
                $nominal, 
                $nama_penerima, 
                $ac_penerima, 
                $bank_penerima, 
                $Keterangan,
                $PVRNo, 
                $statGiro,
                $base64File,
                $createdBy
            );

            // Execute the statement
            if (!$stmt->execute()) {
                throw new Exception("Error executing statement: " . $stmt->error);
            }

            // Update status of the selected giro number to 'Used'
            $update_stmt = $conn->prepare("UPDATE data_giro SET statusgiro = 'Used' WHERE nogiro = ?");
            $update_stmt->bind_param("s", $selected_giro_number);
            $update_stmt->execute();

            // Commit transaction
            $conn->commit();
            $message = 'New record created successfully and status updated to Used.';
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction failed: " . $e->getMessage());
            $message = 'An error occurred. Please try again.';
        } finally {
            // Close the statements
            if (isset($stmt)) $stmt->close();
            if (isset($update_stmt)) $update_stmt->close();
        }
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issued Giro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
        input[type="submit"], .back-button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: inline-block;
            text-align: center;
            width: calc(100% - 20px);
        }
        input[type="submit"]:hover, .back-button:hover {
            background-color: #0056b3;
        }
        .back-button {
            background-color: #6c757d;
            margin-top: 20px;
        }
        .message {
            text-align: center;
            margin: 10px 0;
            color: red; /* Error message color */
        }
        .success-message {
            color: green; /* Success message color */
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
                document.getElementById('search_giro_no').value = selectedGiro;
                document.getElementById('giro_number').style.display = "none"
            } else {
                bankInput.value = '';
                acNumberInput.value = '';
            }
        }

        function updateCustomerDetails() {
            const customerSelect = document.getElementById('no_cust');
            const selectedCust = customerSelect.value;
            const acPenerimaInput = document.getElementById('ac_penerima');
            const bankPenerimaInput = document.getElementById('bank_cust');
            const namaPenerimaInput = document.getElementById('nama_penerima');

            if (selectedCust) {
                const data = <?php echo json_encode($customer_data); ?>;
                acPenerimaInput.value = data[selectedCust].ac_number;
                bankPenerimaInput.value = data[selectedCust].bank_cust;
                namaPenerimaInput.value = data[selectedCust].nama_cust; // Set the recipient's name
            } else {
                acPenerimaInput.value = '';
                bankPenerimaInput.value = '';
                namaPenerimaInput.value = '';
            }
        }

        function setDefaultDueDate() {
            const tanggalGiro = document.getElementById('tanggal_giro');
            const tanggalJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
            tanggalJatuhTempo.value = tanggalGiro.value;
        }

        function searchGiro() {
            const input = document.getElementById('search_giro_no').value.toLowerCase();
            const select = document.getElementById('giro_number');
            const options = select.getElementsByTagName('option');

            let hasOptions = false;

            // Loop through options and filter based on input
            for (let i = 0; i < options.length; i++) {
                const optionText = options[i].textContent.toLowerCase();
                const isVisible = optionText.includes(input);
                options[i].style.display = isVisible ? 'block' : 'none';
                
                if (isVisible) {
                    hasOptions = true;
                }
            }

            // Hide or show the select element based on input
            select.style.display = input ? (hasOptions ? 'block' : 'none') : 'none';
        }

        function clearInput() {
            const input = document.getElementById('search_giro_no');
            input.value = '';
        }
    </script>
</head>
<body>
    <h1>Issued Giro</h1>
    <?php if ($message): ?>
        <div class="<?php echo strpos($message, 'Error') === false ? 'success-message' : 'message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="giro_number">No Giro:</label>
        <input type="text" id="search_giro_no" oninput="searchGiro()" onfocusout="clearInput()" required>
        <select id="giro_number" name="giro_number" size="5" required onchange="updateBankAndAccount()" style="display:none;">
            <?php foreach (array_keys($giro_data) as $giro): ?>
                <option value="<?php echo htmlspecialchars($giro); ?>"><?php echo htmlspecialchars($giro); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="namabank">Nama Bank:</label>
        <input type="text" id="namabank" name="namabank" readonly required><br><br>

        <label for="ac_number">Account Number:</label>
        <input type="text" id="ac_number" name="ac_number" readonly required><br><br>

        <label for="tanggal_giro">Tanggal Giro:</label>
        <input type="date" id="tanggal_giro" name="tanggal_giro" required onchange="setDefaultDueDate()"><br><br>

        <label for="tanggal_jatuh_tempo">Tanggal Jatuh Tempo:</label>
        <input type="date" id="tanggal_jatuh_tempo" name="tanggal_jatuh_tempo" required><br><br>

        <label for="nominal">Nominal:</label>
        <input type="number" id="nominal" name="nominal" required><br><br>

        <label for="no_cust">Customer:</label>
        <select id="no_cust" name="no_cust" required onchange="updateCustomerDetails()">
            <option value="">Select No Customer</option>
            <?php foreach (array_keys($customer_data) as $cust): ?>
                <option value="<?php echo htmlspecialchars($cust); ?>"><?php echo htmlspecialchars($cust); ?></option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="ac_penerima">Account Number Penerima:</label>
        <input type="text" id="ac_penerima" name="ac_penerima" readonly required><br><br>

        <label for="nama_penerima">Nama Penerima:</label>
        <input type="text" id="nama_penerima" name="nama_penerima" readonly required><br><br>

        <label for="bank_cust">Bank Penerima:</label>
        <input type="text" id="bank_cust" name="bank_cust" readonly required><br><br>

        <label for="PVRNo">PVR No:</label>
        <input type="text" id="PVRNo" name="PVRNo"><br><br>

        <label for="Keterangan">Keterangan:</label>
        <input type="text" id="Keterangan" name="Keterangan"><br><br>

        <label for="Keterangan">Foto Giro:</label>
        <input type="file" id="Keterangan" name="foto_giro"><br><br>

        <input type="submit" value="Submit">
        <a href="dashboard.php" class="back-button">Kembali</a>
    </form>
</body>
</html>