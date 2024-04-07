<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include("config.php");
include("getProperty.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Warden") {
    header("Location: login.php");
    exit();
}
function outputPropertyCard($row)
{
    echo '<a href="viewProperty.php?property_id=' . $row["id"] . '&title=' . $row["title"] . '&description=' . $row["description"] . '&bedCounts=' . $row["bedCounts"] . '&rent=' . $row["rent"] . '&longitude=' . $row["longitude"] . '&latitude=' . $row["latitude"] . '&locationLink=' . $row["Address"] . '&status=' . $row["status"] . '">';
    echo '<div class="card" data-property-id="' . $row["id"] . '">';
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
    echo '</a>';
}
$headerText = "All Accommodations";

if (isset($_GET['status']) && $_GET['status'] == 'pending') {
    $headerText = "Pending for Approval";
}

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
            <div class="warden_dashboard_buttons_container">
                <a href="wardenDashboard.php?status=pending"><button class="big-button" id="pendingBtn">Pending for Approval</button></a>
                <a href="wardenDashboard.php"><button class="big-button" id="allAccommodationsBtn">All Accommodations</button></a>
            </div>
        </section>
        <div class="webadmin_dashboard_accommodation_container">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['status']) && $_GET['status'] == 'pending') {
                $properties = fetchPendingAccommodations($connection);
            } else {
                $properties = fetchAllAccommodations($connection);
            }

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
        var map = L.map('map').setView([6.8208936, 80.03972288538341], 15); // Default view

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        <?php
    // Output JavaScript code to add markers and attach click events
    foreach ($properties as $property) {
        echo "var marker = L.marker([" . $property['latitude'] . ", " . $property['longitude'] . "]).addTo(map);\n";
        echo "marker.propertyId = " . $property['id'] . ";\n";
        // Adding click event to each marker
        echo "marker.on('click', function(e) {
            highlightPropertyCard(e.target.propertyId);
            map.setView(e.latlng, 17); // Zoom to the clicked marker
        });\n";
    }
    ?>

    // Function to highlight corresponding property card
    function highlightPropertyCard(propertyId) {
        // Remove previous highlighted cards
        var cards = document.querySelectorAll('.card');
        cards.forEach(function(card) {
            card.classList.remove('highlighted');
        });
        // Highlight the card corresponding to the clicked marker
        var card = document.querySelector('[data-property-id="' + propertyId + '"]');
        if (card) {
            card.classList.add('highlighted');
        }
    }
    </script>
</body>

</html>