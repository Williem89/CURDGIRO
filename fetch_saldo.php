<?php
include 'koneksi.php';

if (isset($_POST['ac_number'])) {
    $ac_number = $_POST['ac_number'];

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT saldoawal FROM list_rekening WHERE no_akun = ?");
    $stmt->bind_param('s', $ac_number);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo $row['saldoawal']; // Use the correct column name
        } else {
            echo "No saldo available.";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
$conn->close();
?>
