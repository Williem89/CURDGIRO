<?php
// Include the database connection
include 'koneksi.php'; // Ensure this sets the $conn variable

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch entities from list_entitas
$entities = [];
$result = $conn->query("SELECT id_entitas, nama_entitas FROM list_entitas");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entities[] = $row;
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $no_akun = trim($_POST['no_akun']);
    $id_bank = trim($_POST['id_bank']); // Assume this is a valid ID
    $nama_akun = trim($_POST['nama_akun']);
    $id_entitas = (int)$_POST['id_entitas']; // Cast to int for safety

    // Retrieve the bank name based on the selected id_bank
    $bankNameResult = $conn->query("SELECT nama_bank FROM list_bank WHERE id = '$id_bank'");
    $bankNameRow = $bankNameResult->fetch_assoc();
    $nama_bank = $bankNameRow['nama_bank'] ?? '';

    // Check for empty fields
    if (empty($no_akun) || empty($nama_bank) || empty($nama_akun) || empty($id_entitas)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        // Prepare the insertion statement
        $stmt = $conn->prepare("INSERT INTO list_rekening (no_akun, nama_bank, nama_akun, id_entitas) VALUES (?, ?, ?, ?)");
        
        if ($stmt) {
            // Bind parameters for the insert
            $stmt->bind_param("sssi", $no_akun, $nama_bank, $nama_akun, $id_entitas);

            // Execute the statement
            if ($stmt->execute()) {
                echo "<script>alert('Data has been added successfully!');</script>";
            } else {
                echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
            }

            // Close the prepared statement
            $stmt->close();
        } else {
            echo "<script>alert('Error preparing insertion statement: " . $conn->error . "');</script>";
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
    <title>Input Data Rekening</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
            margin-bottom: 30px;
        }

        .form-container {
            max-width: 400px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            color: #555;
            margin-top: 10px;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .btn-primary,
        .btn-success {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
            margin-top: 15px;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        #bank_results {

            background-color: white;
            border: 1px solid #ccc;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            width: 100; /* Matches the input field width */
            border-radius: 4px;
            display: none; /* Start hidden */
        }


        #bank_results option {
            padding: 8px;
            cursor: pointer;
        }

        #bank_results option:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <h1>Input Data Rekening</h1>
    <div class="form-container">
        <form method="POST" action="">
            <label for="id_entitas">Entitas:</label>
            <select id="id_entitas" name="id_entitas" required onchange="setDefaultNamaAkun()">
                <option value="">-- Pilih Entitas --</option>
                <?php foreach ($entities as $entity): ?>
                    <option value="<?php echo $entity['id_entitas']; ?>">
                        <?php echo htmlspecialchars($entity['nama_entitas']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="nama_akun">Nama Akun:</label>
            <input type="text" id="nama_akun" name="nama_akun" required>

            <label for="search_bank">Nama Bank:</label>
            <input type="text" id="search_bank" oninput="searchBank()" onfocusout="clearInput()" required>
            <select id="bank_results" size="5" onclick="selectBank(event)" style="display:none;"></select>

            <label for="no_akun">No Akun:</label>
            <input type="text" id="no_akun" name="no_akun" required>      

            <input type="hidden" id="id_bank" name="id_bank">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="button" class="btn btn-success" onclick="window.location.href='dashboard.php';">Kembali</button>
        </form>
    </div>

    <script>
        function clearInput() {
            document.getElementById('search_bank').value = "";
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
                            const option = document.createElement('option');
                            option.value = bank.id;
                            option.textContent = bank.nama_bank;
                            resultsContainer.appendChild(option);
                        });
                        resultsContainer.style.display = data.length ? 'block' : 'none';
                    });
            } else {
                resultsContainer.style.display = 'none';
            }
        }

        function selectBank(event) {
            const selectedBank = event.target;
            document.getElementById('id_bank').value = selectedBank.value;
            document.getElementById('search_bank').value = selectedBank.textContent;
            document.getElementById('bank_results').innerHTML = '';
            document.getElementById('bank_results').style.display = 'none';
        }

        function setDefaultNamaAkun() {
            const entitasSelect = document.getElementById('id_entitas');
            const namaAkunInput = document.getElementById('nama_akun');

            const selectedOption = entitasSelect.options[entitasSelect.selectedIndex];
            namaAkunInput.value = selectedOption ? selectedOption.text : '';
        }
    </script>
</body>
</html>