<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("config.php");
include("getProperty.php");

function outputPropertyCard($row)
{
   
    echo '<div class="card" style="margin: 0; padding: 0;" data-property-id="' . $row["id"] . '" onclick="moveToProperty(' . $row["latitude"] . ', ' . $row["longitude"] . ')">';
echo '<div class="card">';
echo '<div class="card__image">';
echo '<img src="' . $row["image_data"] . '" alt="' . $row["title"] . '">';
echo '</div>';
echo '<div class="card__content">';
echo '<h2 class="card__title">' . $row["title"] . '</h2>';
echo '<p class="card__description">' . substr($row['description'], 0, 60) . '...</p>';
echo '<div class="card__details">';
echo '<p class="beds"><strong>Beds Available</strong>' . $row["bedCounts"] . '</p>';
echo '<p class="beds"><strong>Address</strong>' . $row["Address"] . '</p>';
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
echo '</div>';
echo '</div>';
}
$headerText = "All Accommodations";


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommo-NSBM</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <style>
        #map {
            height: 600px;
        }
    </style>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

    <nav>
        <div class="nav__logo"><span id="uni">Accommo-</span><span id="nest">NSBM</span></span></div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <section>
        <div id="map"></div>
    </section>

    <section class="section__container webadmin_dashboard_section__container" id="wardenDashboard_section">
        <h2 class="section__header"><?php echo $headerText; ?></h2>
        <section class="section__container landlord_dashboard_section__container" id="my_accommodations_section">
                 </section>
        <div class="webadmin_dashboard_accommodation_container">
            <?php
            $properties = fetchapproved($connection);
            foreach ($properties as $property) {
                outputPropertyCard($property);
            }
            ?>
        </div>
    </section>

    <?php
    include "footer.php";
    ?>

<script>
        var map = L.map('map').setView([6.8208936, 80.03972288538341], 15); 

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        function moveToProperty(latitude, longitude) {
        
        map.setView([latitude, longitude], 17); 
        
       
        var mapSection = document.getElementById('map');
        mapSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
        <?php
  
    foreach ($properties as $property) {
        echo "var marker = L.marker([" . $property['latitude'] . ", " . $property['longitude'] . "]).addTo(map);\n";
        echo "marker.propertyId = " . $property['id'] . ";\n";

        echo "marker.on('click', function(e) {
            highlightPropertyCard(e.target.propertyId);
            map.setView(e.latlng, 17); // Zoom to the clicked marker
        });\n";
    }
    
    ?>

 
    function highlightPropertyCard(propertyId) {

        var cards = document.querySelectorAll('.card');
        cards.forEach(function(card) {
            card.classList.remove('highlighted');
        });
     
        var card = document.querySelector('[data-property-id="' + propertyId + '"]');
        if (card) {
            card.classList.add('highlighted');
        }
    }
    </script>
</body>

</html>