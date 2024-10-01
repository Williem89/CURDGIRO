<?php
// Database connection
include 'koneksi.php';

$message = ''; // Initialize the message variable

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input values and sanitize them
    $no_cust = filter_input(INPUT_POST, 'no_cust', FILTER_SANITIZE_STRING);
    $ac_cust = filter_input(INPUT_POST, 'ac_cust', FILTER_SANITIZE_STRING);
    $nama_cust = filter_input(INPUT_POST, 'nama_cust', FILTER_SANITIZE_STRING);
    $bank_cust = filter_input(INPUT_POST, 'bank_cust', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($no_cust) || empty($ac_cust) || empty($nama_cust) || empty($bank_cust)) {
        $message = 'Error: All fields are required.';
    } else {
        // Check for existing customer with the same no_cust or ac_cust
        $stmt = $conn->prepare("SELECT COUNT(*) FROM list_customer WHERE no_cust = ? OR ac_cust = ?");
        $stmt->bind_param("ss", $no_cust, $ac_cust);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $message = 'Error: Customer or account number already exists.';
        } else {
            // Prepare statement to insert into list_customer table
            $stmt = $conn->prepare("INSERT INTO list_customer (no_cust, ac_cust, nama_cust, bank_cust) VALUES (?, ?, ?, ?)");

            if ($stmt === false) {
                $message = 'Prepare failed: ' . htmlspecialchars($conn->error);
            } else {
                $stmt->bind_param("ssss", $no_cust, $ac_cust, $nama_cust, $bank_cust);

                if ($stmt->execute()) {
                    $message = 'Customer added successfully.';
                    // Reset fields
                    $no_cust = $ac_cust = $nama_cust = $bank_cust = '';
                } else {
                    $message = 'Error: ' . htmlspecialchars($stmt->error);
                }

                $stmt->close();
            }
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
    <title>Add Customer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #343a40;
        }
        form {
            background: white;
            border-radius: 8px;
            padding: 20px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .message {
            text-align: center;
            color: red; /* Error message color */
        }
        .success-message {
            color: green; /* Success message color */
        }
        #bank_results {
            background-color: white;
            border: 1px solid #ccc;
            z-index: 1000;
            max-height: 150px;
            overflow-y: auto;
            display: none; /* Start hidden */
            position: absolute; /* Adjust position */
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
    <h1>Add Customer</h1>
    <?php if ($message): ?>
        <div class="<?php echo strpos($message, 'Error') === false ? 'success-message' : 'message'; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="no_cust" class="form-label">Customer:</label>
            <input type="text" id="no_cust" name="no_cust" class="form-control" required value="<?php echo htmlspecialchars($no_cust ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="ac_cust" class="form-label">Account Number:</label>
            <input type="text" id="ac_cust" name="ac_cust" class="form-control" required value="<?php echo htmlspecialchars($ac_cust ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="nama_cust" class="form-label">Customer Name:</label>
            <input type="text" id="nama_cust" name="nama_cust" class="form-control" required value="<?php echo htmlspecialchars($nama_cust ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="search_bank" class="form-label">Bank Name:</label>
            <input type="text" id="search_bank" oninput="searchBank()" onfocusout="clearInput()" class="form-control" required>
            <select id="bank_results" size="5" onclick="selectBank(event)"></select>
            <input type="hidden" id="bank_cust" name="bank_cust">
        </div>

        <button type="submit" class="btn btn-primary">Add Customer</button>
        <a href="dashboard.php" class="btn btn-secondary">Back</a>
    </form>

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
                            option.value = bank.nama_bank; // Save the bank name
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
            document.getElementById('bank_cust').value = selectedBank.value; // Save the bank name to hidden input
            document.getElementById('search_bank').value = selectedBank.textContent;
            document.getElementById('bank_results').innerHTML = '';
            document.getElementById('bank_results').style.display = 'none';
        }
    </script>
</body>
</html>
