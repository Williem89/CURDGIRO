<?php
// Include the database connection
include 'koneksi.php';

// Start the session
session_start();

// Fetch entities from list_entitas
$entities = [];
$result = $conn->query("SELECT id_entitas, nama_entitas FROM list_entitas");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $entities[] = $row;
    }
}

// Initialize account numbers array
$account_numbers = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $prefix = filter_input(INPUT_POST, 'prefix', FILTER_SANITIZE_STRING);
    $start_number = filter_input(INPUT_POST, 'Start_number', FILTER_SANITIZE_NUMBER_INT);
    $jumlah_giro = filter_input(INPUT_POST, 'Jumlah_giro', FILTER_SANITIZE_NUMBER_INT);
    $namabank = filter_input(INPUT_POST, 'namabank', FILTER_SANITIZE_STRING);
    $ac_number = filter_input(INPUT_POST, 'ac_number', FILTER_SANITIZE_STRING);
    $ac_name = filter_input(INPUT_POST, 'ac_name', FILTER_SANITIZE_STRING);
    $jenis_giro = filter_input(INPUT_POST, 'jenis_giro', FILTER_SANITIZE_STRING);
    $id_entitas = filter_input(INPUT_POST, 'nama_entitas', FILTER_SANITIZE_NUMBER_INT);

    // Get the logged-in user's username
    $created_by = $_SESSION['username'] ?? 'system';

    // Check for empty fields
    if (empty($prefix) || empty($start_number) || empty($jumlah_giro) || empty($namabank) || empty($ac_number) || empty($ac_name) || empty($jenis_giro) || empty($id_entitas)) {
        echo "<script>alert('Error: All fields are required.');</script>";
    } else {
        $end_number = $start_number + $jumlah_giro - 1;
        $giro_numbers = [];

        for ($i = $start_number; $i <= $end_number; $i++) {
            // Include prefix in the giro number
            $giro_number = $prefix . "-" . str_pad($i, 3, '0', STR_PAD_LEFT);
            $giro_numbers[] = $giro_number;

            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM data_giro WHERE nogiro = ?");
            $check_stmt->bind_param("s", $giro_number);
            $check_stmt->execute();
            $check_stmt->bind_result($count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($count > 0) {
                echo "<script>alert('Nomor giro $giro_number sudah ada, tidak akan dimasukkan.');</script>";
                continue;
            }

            $stmt = $conn->prepare("INSERT INTO data_giro (nogiro, namabank, ac_number, ac_name, statusgiro, created_by, created_at, jenis_giro, id_entitas) 
                VALUES (?, ?, ?, ?, 'Unused', ?, NOW(), ?, ?)");

            if ($stmt) {
                $stmt->bind_param("sssssii", $giro_number, $namabank, $ac_number, $ac_name, $created_by, $jenis_giro, $id_entitas);
                if (!$stmt->execute()) {
                    echo "<script>alert('Error executing statement: " . $stmt->error . "');</script>";
                }
                $stmt->close();
            }
        }

        if (!empty($giro_numbers)) {
            echo "<script>alert('New records created successfully for giro numbers: " . implode(', ', $giro_numbers) . "');</script>";
        }
    }
}

// Close the database connection
$conn->close();
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
        input[type="text"],
        select {
            width: 100%;
            padding: 5px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        input[type="submit"],
        a {
            display: inline-block;
            width: 25%;
            padding: 8px 5px;
            border-radius: 8px;
            text-align: center;
            text-decoration: none;
            margin: 6px 0;
            border: none;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #218838;
        }
        a {
            color: white;
            background-color: #007bff;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function fetchAccountNumbers() {
            const entitySelect = document.getElementById('nama_entitas');
            const entityId = entitySelect.value;

            const acNumberSelect = document.getElementById('ac_number');
            acNumberSelect.innerHTML = '<option value="">-- Pilih Account Number --</option>';

            if (entityId) {
                fetch(`fetch_accounts.php?id_entitas=${entityId}`)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(account => {
                            const option = document.createElement('option');
                            option.value = account.no_akun;
                            option.textContent = account.no_akun;
                            acNumberSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching account numbers:', error);
                    });
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('nama_entitas').addEventListener('change', fetchAccountNumbers);
            document.getElementById('ac_number').addEventListener('change', function() {
                const selectedAccount = this.value;
                if (selectedAccount) {
                    fetch(`fetch_account_details.php?ac_number=${selectedAccount}`)
                        .then(response => response.json())
                        .then(data => {
                            document.getElementById('bank_name_display').textContent = data.bank_name;
                            document.getElementById('namabank').value = data.bank_name; // Set the input value
                            document.getElementById('ac_name').value = data.account_name; // Set the input value
                            document.getElementById('account_name_display').textContent = data.account_name;
                        })
                        .catch(error => {
                            console.error('Error fetching account details:', error);
                        });
                }
            });
        });
    </script>
</head>
<body>
    <h1>Generate Giro</h1>
    <form method="POST" action="">
        <label>Jenis Giro:</label>
        <label><input type="radio" name="jenis_giro" value="Giro" required checked>Giro</label>
        <label><input type="radio" name="jenis_giro" value="Cek">Cek</label>
        <br>
        <label for="nama_entitas">Entitas:</label>
        <select id="nama_entitas" name="nama_entitas" required>
            <option value="">-- Pilih Entitas --</option>
            <?php foreach ($entities as $entity): ?>
                <option value="<?php echo $entity['id_entitas']; ?>">
                    <?php echo htmlspecialchars($entity['nama_entitas']); ?>
                </option>
            <?php endforeach; ?>
        </select>



        <label for="ac_number">Account Number:</label>
        <select id="ac_number" name="ac_number" required>
            <option value="">Select Account Number</option>
        </select>

        <div>
            <strong>Bank Name:</strong> <span id="bank_name_display" hidden></span>
            <input type="text" id="namabank" name="namabank" value="" required readonly>
        </div>
        <br>
        <div>
            <strong>Account Name:</strong> <span id="account_name_display" hidden></span>
            <input type="text" id="ac_name" name="ac_name" value="" required readonly>
        </div>

        <br>

        <table>
        <label for="Start_number">Mulai dari no. :</label>
        <tr>
        <td><input type="text" id="prefix" name="prefix" required style="width:70px;"></td>
        <td><input type="number" id="Start_number" name="Start_number" required style="width:220px;"></td>
        <tr>
            </table>


        <label for="Jumlah_giro">Jumlah Giro:</label>
        <input type="number" id="Jumlah_giro" name="Jumlah_giro" required>

        <input type="submit" value="Submit">
        <a href="dashboard.php">Kembali</a>
    </form>
</body>
</html>
