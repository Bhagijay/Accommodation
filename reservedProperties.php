<?php

session_start();

include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Student") {
    header("Location: login.php");
    exit();
}

$headerText = "Reserved Properties";



// Function to fetch all accommodations
function fetchAllAccommodations($connection)
{
    $userId = $_SESSION['userId'];
    $sql = "SELECT reservations.*, properties.title, properties.description, properties.bedCounts, properties.postedAt, properties.rent, properties.longitude, properties.latitude, properties.locationLink, properties.status AS propertyStatus, images.imageData 
            FROM reservations
            INNER JOIN properties ON reservations.propertyId = properties.propertyId
            LEFT JOIN (
                SELECT propertyId, MIN(imageId) AS minImageId
                FROM images
                GROUP BY propertyId
            ) AS min_images ON properties.propertyId = min_images.propertyId
            LEFT JOIN images ON min_images.minImageId = images.imageId
            WHERE reservations.userId = $userId";
            
            

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
           
            echo '<div class="card">';

            echo '<div class="card__content">';



            echo '<h2 class="card__title">' . $row["title"] . '</h2>';
            echo '<p class="card__description">' . substr($row['description'], 0, 60) . '...</p>';

            echo '<div class="card__details">';
            echo '<p class="beds"><strong>Beds Available</strong>' . $row["bedCounts"] . '</p>';
            echo '<p class="beds"><strong>Posted at</strong> ' . date("Y-m-d", strtotime($row["postedAt"])) . '</p>';
            echo '</div>';

            echo '<div class="card_footer">';
            if ($row["bedCounts"] > 0) {
                echo '<span class="available_status">Available</span>';
            } else {
                echo '<span class="not_available_status">Not Available</span>';
            }
            if ($row["status"] == "Pending") {
                echo '<span class="pending_status">Pending</span>';
            }
            if ($row["status"] == "Accepted") {
                echo '<span class="accepted_status">Accepted</span>';
            }
            if ($row["status"] == "Rejected") {
                echo '<span class="rejected_status">Rejected</span>';
            }

            echo '<span class="rent">' . $row["rent"] . '</span>';
            echo '</div>';
            echo '</div>';
            echo '<div class="card__image">';
            echo '<img src="data:image/jpeg;base64,' . base64_encode($row["imageData"]) . '" alt="' . $row["title"] . '">';
            echo '</div>';
            echo '</div>';
            
        }
    } else {
        echo "No accommodations found.";
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommo NSBM</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css\all.min.css">
</head>

<body>



    <nav>
        <div class="nav__logo">Accommo NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="studentDashboard.php">Dashboard</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>


    <section class="section__container webadmin_dashboard_section__container" id="wardenDashboard_section">
        <h2 class="section__header"><?php echo $headerText; ?></h2>
        <div class="webadmin_dashboard_accommodation_container">


            <?php
            
                fetchAllAccommodations($connection);
            
            ?>



        </div>
    </section>
    <footer class="footer" id="footer_section">
        <div class="section__container footer__container">
            <div class="footer__col">
                <h3>Accommo NSBM</h3>
                <p>
                Experience unparalleled convenience with Accommo NSBM, the ultimate solution for hassle-free student housing near NSBM Green University Town. Explore a plethora of accommodation choices, simplifying the process of finding your dream living space.

                </p>
                <p>
                Say goodbye to accommodation worries and welcome a seamless booking experience with Accommo NSBM.
                </p>
            </div>
            <div class="footer__col">
                <h4>Company</h4>
                <p>About Us</p>
                <p>Our Team</p>
                <p>Contact Us</p>
            </div>
            <div class="footer__col">
                <h4>Legal</h4>
                <p>FAQs</p>
                <p>Terms & Conditions</p>
                <p>Privacy Policy</p>
            </div>
            <div class="footer__col">
                <h4>Resources</h4>
                <ul class="social-icons">
                    <li><a href="#" class="fab fa-facebook-f"></a></li>
                    <li><a href="#" class="fab fa-twitter"></a></li>
                    <li><a href="#" class="fab fa-instagram"></a></li>
                    <li><a href="#" class="fab fa-linkedin-in"></a></li>
                </ul>
            </div>
        </div>

        <div class="footer__bar">
            Copyright © nsbm. All rights reserved.
        </div>

    </footer>

</body>

</html>