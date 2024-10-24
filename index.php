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
            background: url('https://media.tampang.com/tm_images/article/dkkp-37jpgvm0cpkwzq5xjc7s40l.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            }

            .container {
            background-color: rgba(29, 29, 29, 0.8);
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
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
            background: linear-gradient(to right, #87CEEB, #00BFFF);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            }

            a:hover {
            background: linear-gradient(to right, #00BFFF, #87CEEB);
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
        <link rel="icon" type="image/x-icon" href="img/icon.png">
        <title>Welcome</title>
        <style>
            body {
            font-family: Arial, sans-serif;
            background-color: #d1D1D1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
            }

            .container {
            background-color: white;
            background-opacity: 0.01;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(135, 206, 235, 0.7), 0 0 20px rgba(135, 206, 235, 0.7);
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
            background: linear-gradient(to right, #87CEEB, #00BFFF);
            color: white;
            border-radius: 5px;
            text-decoration: none;
            }

            a:hover {
            background: linear-gradient(to right, #00BFFF, #87CEEB);
            }
        </style>
    </head>
    <body>

    </body>
    </html>
    <?php
}
?>
