<?php

session_start();

include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Student") {
    header("Location: login.php");
    exit();
}


function fetchAllAccommodations($connection)
{
    $sql = "SELECT properties.*, latest_images.image_data 
                FROM properties 
                INNER JOIN ( 
                    SELECT property_id, MAX(id) AS maxImageId, image_data 
                    FROM images 
                    GROUP BY property_id 
                ) AS latest_images ON properties.id = latest_images.property_id
                WHERE properties.status != 'Pending'";

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {


            $longitude = $row['longitude'];
            $latitude = $row['latitude'];
            $propertyId = $row['id'];



            echo '<div class="card" data-latitude="' . $latitude . '" data-longitude="' . $longitude . '" data-propertyid="' . $propertyId . '">';
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
            
            echo '<span class="rent">' . $row["rent"] . '</span>';
            
            echo '</div>';
            echo '<a href="studentReservation.php?propertyId=' . $row['id'] . '&description=' . urlencode($row['description']) . '&bedCounts=' . $row['bedCounts'] . '&title=' . $row['title'] .'&rent=' . $row['rent'] .'&latitude=' . $row['latitude'] . '&longitude=' . $row['longitude'] .'&address=' . urlencode($row['Address']) . '"><button class="reserve__button">Reserve</button></a>';
            echo '</div>';
            
            echo '<div class="card__image">';
            echo '<img src="' . $row["image_data"] . '" alt="' . $row["title"] . '" style="height: 100%; object-fit: cover;">';
            echo '</div>';
            // Add button to redirect to studentReservation.php with data
         
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css\all.min.css">
</head>

<body>



    <nav>
        <div class="nav__logo">Accommo-NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>





    <section class="section__container landlord_dashboard_section__container" id="all_accommodations_section">
        <div class="webadmin_dashboard_buttons_container">
            <a href="reservedProperties.php"><button class="big-button" id="pendingBtn">My Reservations</button></a>

        </div>
    </section>

    <section class="section__container webadmin_dashboard_section__container" id="studentDashboard_section">
        <h2 class="section__header">Browse Accommodations</h2>

        <div class="webadmin_dashboard_accommodation_container" id="card-map-container">
            <div class="property-cards-container">

                <?php



                fetchAllAccommodations($connection);

                ?>
            </div>
         



        </div>
    </section>

<
    <div id="propertyModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <img id="modalImage" src="" alt="Property Image">
            <h2 id="modalTitle"></h2>
            <p id="modalDescription"></p>
            <div id="modalDetails"></div>
            <button id="reserveBtn">Reserve</button>
        </div>
    </div>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        let map = L.map('map').setView([6.8208936, 80.03972288538341], 15);


        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker([6.8208936, 80.03972288538341]).addTo(map);

        function updateMarkerPosition(latitude, longitude) {
            marker.setLatLng([latitude, longitude]);
        }

        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {

                const latitude = parseFloat(this.dataset.latitude);
                const longitude = parseFloat(this.dataset.longitude);

                updateMarkerPosition(latitude, longitude);

                map.setView([latitude, longitude], 15);
            });
        });



        window.addEventListener('load', function() {
            var mapContainer = document.getElementById('map-container');
            var propertyCardsContainer = document.querySelector('.property-cards-container');
            var windowHeight = window.innerHeight;
            var propertyCardsContainerHeight = propertyCardsContainer.offsetHeight;
            var mapHeight = windowHeight > propertyCardsContainerHeight ? windowHeight : propertyCardsContainerHeight;

            mapContainer.style.height = mapHeight + 'px';
        });


        window.addEventListener('resize', function() {
            var mapContainer = document.getElementById('map-container');
            var propertyCardsContainer = document.querySelector('.property-cards-container');
            var windowHeight = window.innerHeight;
            var propertyCardsContainerHeight = propertyCardsContainer.offsetHeight;
            var mapHeight = windowHeight > propertyCardsContainerHeight ? windowHeight : propertyCardsContainerHeight;

            mapContainer.style.height = mapHeight + 'px';
        });

        var modal = document.getElementById("propertyModal");


        var closeBtn = document.getElementsByClassName("close")[0];

        closeBtn.onclick = function() {
            modal.style.display = "none";
        }


        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }


        function openModal(title, description, details, imageSrc, latitude, longitude, propertyId, bedCounts, postedAt, rent) {
            document.getElementById("modalTitle").innerText = title;
            document.getElementById("modalDescription").innerText = description;
            document.getElementById("modalDetails").innerHTML = details;
            document.getElementById("modalImage").src = imageSrc;

            // Set dataset attributes for latitude, longitude, and propertyId
            document.getElementById("reserveBtn").setAttribute("data-latitude", latitude);
            document.getElementById("reserveBtn").setAttribute("data-longitude", longitude);
            document.getElementById("reserveBtn").setAttribute("data-propertyId", propertyId);
            document.getElementById("reserveBtn").setAttribute("data-bedCounts", bedCounts); // Add bedCounts to the button
            document.getElementById("reserveBtn").setAttribute("data-postedAt", postedAt); // Add postedAt to the button
            document.getElementById("reserveBtn").setAttribute("data-rent", rent); // Add rent to the button

            modal.style.display = "block";
        }

        document.getElementById("reserveBtn").addEventListener("click", function() {
            // Retrieve latitude, longitude, propertyId, bedCounts, postedAt, and rent from dataset
            const latitude = document.getElementById("reserveBtn").getAttribute("data-latitude");
            const longitude = document.getElementById("reserveBtn").getAttribute("data-longitude");
            const propertyId = document.getElementById("reserveBtn").getAttribute("data-propertyId");
            const bedCounts = document.getElementById("reserveBtn").getAttribute("data-bedCounts");
            const postedAt = document.getElementById("reserveBtn").getAttribute("data-postedAt");
            const rent = document.getElementById("reserveBtn").getAttribute("data-rent");

            // Construct reservation URL with all necessary parameters
            const reservationURL = "studentReservation.php?" +
                "title=" + encodeURIComponent(document.getElementById("modalTitle").innerText) +
                "&description=" + encodeURIComponent(document.getElementById("modalDescription").innerText) +
                "&details=" + encodeURIComponent(document.getElementById("modalDetails").innerHTML) +
                "&latitude=" + encodeURIComponent(latitude) +
                "&longitude=" + encodeURIComponent(longitude) +
                "&propertyId=" + encodeURIComponent(propertyId) +
                "&bedCounts=" + encodeURIComponent(bedCounts) + // Append bedCounts to the URL
                "&postedAt=" + encodeURIComponent(postedAt) + // Append postedAt to the URL
                "&rent=" + encodeURIComponent(rent); // Append rent to the URL

            window.location.href = reservationURL;
        });

        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', function() {
                const title = this.querySelector('.card__title').innerText;
                const description = this.querySelector('.card__description').innerText;
                const details = this.querySelector('.card__details').innerHTML;
                const imageSrc = this.querySelector('.card__image img').src;
                const latitude = this.dataset.latitude;
                const longitude = this.dataset.longitude;
                const propertyId = this.dataset.propertyid;
                const bedCounts = this.querySelector('.beds').innerText.match(/\d+/)[0]; // Extract bedCounts from the card
                const postedAt = this.querySelector(' .postedAt').innerText.match(/\d{4}-\d{2}-\d{2}/)[0]; // Extract postedAt from the card
                const rent = this.querySelector('.rent').innerText; // Extract rent from the card

                openModal(title, description, details, imageSrc, latitude, longitude, propertyId, bedCounts, postedAt, rent);
            });
        });
    </script>
<?php
    include "footer.php";
    ?>
</body>

</html>