<?php
// koneksi.php: Database connection file
include 'koneksi.php';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input values
    session_start();
    $username = $_SESSION['username']; // Assuming the username is stored in session after login
    $current_password = filter_input(INPUT_POST, 'current_password', FILTER_SANITIZE_STRING);
    $new_password = password_hash(filter_input(INPUT_POST, 'new_password', FILTER_SANITIZE_STRING), PASSWORD_BCRYPT);

    // Fetch the current password hash from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
    
    if ($stmt) {
        // Bind parameters
        $stmt->bind_param("s", $username);
        // Execute statement
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            
            // Verify the current password
            if (password_verify($current_password, $hashed_password)) {
                // Update the password
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
                if ($update_stmt) {
                    $update_stmt->bind_param("ss", $new_password, $username);
                    if ($update_stmt->execute()) {
                        echo "<script>alert('Password changed successfully!'); window.location.href='login.html';</script>";
                    } else {
                        echo "<script>alert('Error updating password: " . $update_stmt->error . "');</script>";
                    }
                    $update_stmt->close();
                } else {
                    echo "<script>alert('Error preparing update statement: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Current password is incorrect.');</script>";
            }
        } else {
            echo "<script>alert('User  not found.');</script>";
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