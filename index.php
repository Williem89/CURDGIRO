<?php
// Start the session

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    // If logged in, redirect to dashboard or home page
    header("Location: dashboard.php");
    exit;
} else {
    // If not logged in, display a landing page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                text-align: center;
            }

            .container {
                background-color: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            h1 {
                margin-bottom: 20px;
                color: #333;
            }

            p {
                margin-bottom: 30px;
                color: #666;
            }

            a {
                display: inline-block;
                padding: 10px 20px;
                background-color: #5cb85c;
                color: white;
                border-radius: 5px;
                text-decoration: none;
            }

            a:hover {
                background-color: #4cae4c;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Welcome to Our Site</h1>
            <p>Please log in to continue.</p>
            <a href="login.html">Go to Login</a>
        </div>
    </body>
    </html>
    <?php
}
?>
<?php
// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    // If logged in, redirect to dashboard or home page
    header("Location: dashboard.php");
    exit;
} else {
    // If not logged in, display a landing page
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                text-align: center;
            }

            .container {
                background-color: white;
                padding: 40px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            h1 {
                margin-bottom: 20px;
                color: #333;
            }

            p {
                margin-bottom: 30px;
                color: #666;
            }

            a {
                display: inline-block;
                padding: 10px 20px;
                background-color: #5cb85c;
                color: white;
                border-radius: 5px;
                text-decoration: none;
            }

            a:hover {
                background-color: #4cae4c;
            }
        </style>
    </head>
    <body>

    </body>
    </html>
    <?php
}
?>
