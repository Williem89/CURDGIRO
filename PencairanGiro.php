<?php
include 'koneksi.php';

$results = [];

// Fetch all issued records to display in the form
$sql = "SELECT * FROM detail_giro WHERE StatGiro = 'Issued'";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_nogiro'])) {
    $update_id = $_POST['update_nogiro'];
    $tanggal_giro_cair = $_POST['tanggal_giro_cair']; // New input field

    // Update query to set StatGiro to 'Settled' and include tanggal_giro_cair
    $sql_update = "UPDATE detail_giro SET StatGiro = 'Settled', tanggal_cair_giro = ? WHERE nogiro = ?";
    $stmt = $conn->prepare($sql_update);
    
    if ($stmt) {
        $stmt->bind_param('ss', $tanggal_cair_giro, $update_id); // Assuming nogiro is a string
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Data successfully updated!</p>";
        } else {
            echo "<p style='color: red;'>Error executing update: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p style='color: red;'>Error preparing statement: " . $conn->error . "</p>";
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
    <title>Update Detail Giro</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
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
            margin-bottom: 10px;
            color: #495057;
        }
        select, input[type="text"], input[type="date"], input[type="number"], input[type="submit"], input[type="button"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        input[type="button"] {
            background-color: #6c757d; /* Grey color for back button */
            color: white;
            cursor: pointer;
        }
        input[type="button"]:hover {
            background-color: #5a6268; /* Darker grey on hover */
        }
        #giro-preview {
            background: #f1f1f1;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
    <script>
        function showPreview() {
            const select = document.getElementById('update_nogiro');
            const previewDiv = document.getElementById('giro-preview');
            const selectedOption = select.options[select.selectedIndex];

            if (selectedOption.value) {
                const giroDetails = JSON.parse(selectedOption.getAttribute('data-details'));
                const formattedNominal = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(giroDetails.nominal); // Adjust the currency as needed

                previewDiv.innerHTML = `
                    <strong>No Giro:</strong> ${giroDetails.nogiro} <br>
                    <strong>Status Giro:</strong> ${giroDetails.StatGiro} <br>
                    <strong>Nominal Giro:</strong> ${formattedNominal} <br>
                    <strong>Tanggal Jatuh Tempo:</strong> ${giroDetails.tanggal_jatuh_tempo}
                `;
            } else {
                previewDiv.innerHTML = '';
            }
        }
    </script>
</head>
<body>
    <header>
        <h1>Pencairan Giro</h1>
    </header>

    <form method="POST">
        <label for="update_nogiro">Pilih Giro untuk dicairkan:</label>
        <select name="update_nogiro" id="update_nogiro" required onchange="showPreview()">
            <option value="">--Pilih Giro--</option>
            <?php foreach ($results as $row): ?>
                <option value="<?php echo htmlspecialchars($row['nogiro']); ?>" data-details='<?php echo json_encode($row); ?>'>
                    <?php echo htmlspecialchars($row['nogiro']) . " - " . htmlspecialchars($row['StatGiro']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="tanggal_cair_giro">Tanggal Giro Cair:</label>
        <input type="date" id="tanggal_cair_giro" name="tanggal_cair_giro" required>

        <input type="submit" value="Update ke Settled">
        <input type="button" value="Back" onclick="window.location.href='dashboard.php'"> <!-- Adjust the link as necessary -->
    </form>

    <div id="giro-preview">
        <!-- Preview will be displayed here -->
    </div>
</body>
</html>
