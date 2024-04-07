<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommo-NSBM</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .success-container {
            text-align: center;
            margin-top: 100px;
        }
        .success-header {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .success-message {
            font-size: 18px;
            margin-bottom: 30px;
        }
        .btn {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav__logo">Accommo-NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="landlordDashboard.php">Dashboard</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <section class="section__container">
        <div class="success-container">
            <h2 class="success-header">Property and images uploaded successfully!</h2>
            <p class="success-message">You have successfully posted a new accommodation.</p>
            <a href="landlordDashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </section>
</body>
</html>