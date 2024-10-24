<?php
// koneksi.php: Database connection file
include 'koneksi.php';

/// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = password_hash(filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $UsrLevel = filter_input(INPUT_POST, 'UsrLevel', FILTER_SANITIZE_STRING) ?? 1; // Default to 1 if not provided
    $status='inactive';

    // Prepare statement to insert into the users table
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, UsrLevel, status) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("sssis", $username, $password, $full_name, $UsrLevel, $status);
        // Execute statement
        if ($stmt->execute()) {
            echo "<script>alert('User registered successfully!'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
        
        // Close the statement
        $stmt->close();
    } else {
        echo "<script>alert('Error preparing statement: " . $conn->error . "');</script>";
    }

    // Close the connection
    $conn->close();
}
?>
