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
    $sql = "SELECT reservations.*, 
    properties.title, 
    properties.description, 
    properties.bedCounts, 
    properties.rent, 
    properties.longitude, 
    properties.latitude, 
    properties.Address AS locationLink, 
    properties.status AS propertyStatus, 
    images.image_data AS imageData 
FROM reservations 
INNER JOIN properties ON reservations.propertyId = properties.id 
LEFT JOIN (
 SELECT property_id, MAX(id) AS maxImageId 
 FROM images 
 GROUP BY property_id
) AS latest_images ON properties.id = latest_images.property_id 
LEFT JOIN images ON latest_images.maxImageId = images.id 
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
            echo '<p class="beds"><strong>Address</strong>' . $row["locationLink"] . '</p>';
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
            echo '<img src="' . $row["imageData"] . '" alt="' . $row["title"] . '" style="height: 100%; object-fit: cover;">';
            echo '<br><br>';
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
    <?php
    include "footer.php";
    ?>

</body>

</html>