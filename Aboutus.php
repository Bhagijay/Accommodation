<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Accommo NSBM</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/all.min.css">
    <style>
      
        .column {
            text-align: center;
        }
        .column img {
            display: block;
            margin: 0 auto; 
            max-width: 100%; 
        }
        .container {
            margin-top: 20px; 
        }
      
    </style>
</head>
<body>
    <nav>
    <div class="nav__logo">Accommo NSBM</div>
            <ul class="nav__links">
                <li class="link"><a href="#home_section">Home</a></li>
                <li class="link"><a href="#new_accommodations_section">Accommodations</a></li>
                <li class="link"><a href="#blog_section">Blog</a></li>
                <li class="link"><a href="#footer_section">Contact</a></li>
                <li class="link"><a href="login.php">Login</a></li>
            </ul>
        </nav>

        <center>
    <h2>Our Team</h2>


    
        </center>

    <div class="column">
        <img src="assets/image5.JPEG" alt="Jane" style="width: 400px;">
        <div class="container">
            <h2>Daham Ranasinghe</h2>
            <p class="title">Founder</p>
            <p>Some text that describes me lorem ipsum ipsum lorem.</p>
            <p>jane@example.com</p>
            <p><button class="button">Contact</button></p>
        </div>
    </div>

    <div class="column">
        <img src="assets/image4.JPEG" alt="Mike" style="width: 400px;">
        <div class="container">
            <h2>Zamrah Fathima</h2>
            <p class="title">Director</p>
            <p>Some text that describes me lorem ipsum ipsum lorem.</p>
            <p>mike@example.com</p>
            <p><button class="button">Contact</button></p>
        </div>
    </div>

    <div class="column">
        <img src="assets/image6.JPEG" alt="John" style="width: 400px;">
        <div class="container">
            <h2>Bhagya Jayasinghe</h2>
            <p class="title">Designer</p>
            <p>Some text that describes me lorem ipsum ipsum lorem.</p>
            <p>john@example.com</p>
            <p><button class="button">Contact</button></p>
        </div>
    </div>

    <script>
        // JavaScript code here
    </script>
      <?php
    include "footer.php";
    ?>
</body>
</html>
