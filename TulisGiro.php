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
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $selected_giro_number = filter_input(INPUT_POST, 'giro_number', FILTER_SANITIZE_STRING);
    $tanggal_giro = filter_input(INPUT_POST, 'tanggal_giro', FILTER_SANITIZE_STRING);
    $tanggal_jatuh_tempo = filter_input(INPUT_POST, 'tanggal_jatuh_tempo', FILTER_SANITIZE_STRING);
    $nominal = filter_input(INPUT_POST, 'nominal', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $nama_penerima = filter_input(INPUT_POST, 'nama_penerima', FILTER_SANITIZE_STRING);
    $bank_penerima = filter_input(INPUT_POST, 'bank_penerima', FILTER_SANITIZE_STRING);
    $ac_penerima = filter_input(INPUT_POST, 'ac_penerima', FILTER_SANITIZE_STRING);
    $StatGiro = filter_input(INPUT_POST, 'StatGiro', FILTER_SANITIZE_STRING);
    $Keterangan = filter_input(INPUT_POST, 'Keterangan', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($selected_giro_number) || empty($tanggal_giro) || empty($tanggal_jatuh_tempo) || 
        empty($nominal) || empty($nama_penerima) || empty($bank_penerima) || empty($ac_penerima)) {
        $message = 'Error: All fields are required.';
    } elseif ($nominal <= 0) {
        $message = 'Error: Nominal must be greater than zero.';
    } else {
        // Begin transaction
        $conn->begin_transaction();
        try {
            
            // Define the status and creator variables
                $statGiro = 'Issued';
                $createdBy = 'system';

                // Prepare statement to insert into the detail_giro table
                $stmt = $conn->prepare("INSERT INTO detail_giro (nogiro, tanggal_giro, tanggal_jatuh_tempo, nominal, 
                    nama_penerima, bank_penerima, ac_penerima, Keterangan, StatGiro, created_by, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

                // Check if statement preparation was successful
                if (!$stmt) {
                    throw new Exception("Error preparing statement: " . $conn->error);
                }

                // Bind parameters - Adjusted to 10 placeholders
                    $stmt->bind_param("ssssssssss", 
                    $selected_giro_number, 
                    $tanggal_giro, 
                    $tanggal_jatuh_tempo, 
                    $nominal, 
                    $nama_penerima, 
                    $bank_penerima, 
                    $ac_penerima, 
                    $Keterangan, 
                    $statGiro, 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
            } else {
                bankInput.value = '';
                acNumberInput.value = '';
            }
        }

        function setDefaultDueDate() {
            const tanggalGiro = document.getElementById('tanggal_giro');
            const tanggalJatuhTempo = document.getElementById('tanggal_jatuh_tempo');
            tanggalJatuhTempo.value = tanggalGiro.value;
        }

        function searchBank() {
            const query = document.getElementById('search_bank').value;
            const resultsContainer = document.getElementById('bank_results');
            resultsContainer.innerHTML = '';

            if (query.length > 0) {
                fetch(`search_bank.php?q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(bank => {
                            const bankDiv = document.createElement('div');
                            bankDiv.textContent = bank.nama_bank;
                            bankDiv.style.padding = '10px';
                            bankDiv.style.cursor = 'pointer';
                            bankDiv.onclick = () => selectBank(bank.nama_bank, bank.id);
                            resultsContainer.appendChild(bankDiv);
                        });
                        resultsContainer.style.display = data.length ? 'block' : 'none';
                    });
            } else {
                resultsContainer.style.display = 'none';
            }
        }

        function selectBank(bankName, bankId) {
        document.getElementById('search_bank').value = bankName;
        document.getElementById('id_bank').value = bankId;
        document.getElementById('bank_penerima').value = bankName; // Set the selected bank name
        document.getElementById('bank_results').innerHTML = '';
        document.getElementById('bank_results').style.display = 'none';
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
    <form method="POST" action="">
        <label for="giro_number">No Giro:</label>
        <select id="giro_number" name="giro_number" required onchange="updateBankAndAccount()">
            <option value="">Select No Giro</option>
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

        <label for="nama_penerima">Nama Penerima:</label>
        <input type="text" id="nama_penerima" name="nama_penerima" required><br><br>

        <label for="search_bank">Bank Penerima:</label>
        <input type="text" id="search_bank" oninput="searchBank()" required autocomplete="off">
        <div id="bank_results" style="display:none; border: 1px solid #ced4da; max-height: 150px; overflow-y: auto;"></div>
        <input type="hidden" id="id_bank" name="id_bank"> <!-- Hidden input for the bank ID -->

        <!-- Add a hidden input to save the selected bank name -->
        <input type="hidden" id="bank_penerima" name="bank_penerima">

        <label for="ac_penerima">Account Penerima:</label>
        <input type="text" id="ac_penerima" name="ac_penerima" required><br><br>

        <label for="Keterangan">Keterangan:</label>
        <input type="text" id="Keterangan" name="Keterangan"><br><br>

        <input type="submit" value="Submit">
        <a href="dashboard.php" class="back-button">Kembali</a>
    </form>
</body>
</html>
