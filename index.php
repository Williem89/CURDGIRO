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
                overflow: hidden;
                margin: 0;
                padding: 0;
                width: 100%;
                height: 100%;
            }

            .wrapper {
                background: #ED2324;
                width: 100%;
                height: 100%;
                margin: 0;
                position: absolute;
                top: 0;
                left: 0;
            }

            #myCanvas {
                background: url('https://37.media.tumblr.com/tumblr_mbha9qWF401qcixnko4_500.gif') no-repeat center center fixed;
                background-size: cover;
                z-index: 1000;
                position: relative;
            }

            .Marvel {
                color: #fff;
                font-size: 70px;
                border: 2px solid #fff;
                position: relative;
                z-index: 99;
                top: -490px;
                width: 443px;
                text-align: center;
                left: 35%;
                font-family: 'BentonSansExtraCompBlack';
                src: url('http://sudocoda.com/fonts/bentonsansextracomp-black-webfont.eot?') format('eot');
                src: url('http://sudocoda.com/fonts/bentonsansextracomp-black-webfont.woff') format('woff'), url('http://sudocoda.com/fonts/bentonsansextracomp-black-webfont.ttf') format('truetype');
                padding: 20px;
            }

            /*#playAgain{
  position:relative;
  z-index:99;
  top:-355px;
  left:42%;
  color:#000;
  padding:25px;
  background:transparent;
  font-size:35px;
}*/
        </style>
    </head>

    <body>
        <a href="login.php">
        <div class="wrapper">
            <canvas id="myCanvas">
                You're Browser Doesn't Support Canvas :(
            </canvas>
            <audio id="pageFlip" loop>
                <source src="https://www.freesfx.co.uk/rx2/mp3s/2/1440_1258277866.mp3" type="audio/mpeg">
                </source>
            </audio>
            <div class="Marvel">IT AVENGERS</div>
        </div>
        </a>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </body>

    </html>
    <script>
        var canvas = document.getElementById('myCanvas');
        var context = canvas.getContext("2d");

        var width = window.innerWidth;
        var height = window.innerHeight;

        context.canvas.width = width;
        context.canvas.height = height;
        var page = document.getElementById("pageFlip");

        //page.play();
        /*page.loop = true;*/
        $('#myCanvas').fadeTo(8000, 0.0, function() {
            window.location.href = 'login.php';
            //page.loop = false;
            /*page.stop();*/
        });

        
    </script>



<?php
}
?>