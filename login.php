<?php
// koneksi.php: Database connection file
include 'koneksi.php';

// Redirect to login.html if accessed directly
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.html");
    exit;
}

// Start session
session_start();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    // Prepare statement to select user
    $stmt = $conn->prepare("SELECT password, UsrLevel FROM users WHERE username = ?");
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("s", $username);
        
        // Execute statement
        $stmt->execute();
        $stmt->store_result();
        
        // Check if the user exists
        if ($stmt->num_rows === 1) {
            // Bind result variables
            $stmt->bind_result($hashed_password, $UsrLevel);
            $stmt->fetch();
            
            // Verify the password
            if (password_verify($password, $hashed_password)) {
                // Set session variables
                $_SESSION['username'] = $username;
                $_SESSION['UsrLevel'] = $UsrLevel;
                
                // Redirect to a protected page (e.g., dashboard)
                header("Location: dashboard.php");
                exit;
            } else {
                echo "<script>alert('Invalid password!'); window.location.href='login.html';</script>";
            }
        } else {
            echo "<script>alert('Username not found!'); window.location.href='login.html';</script>";
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
