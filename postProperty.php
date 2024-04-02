<?php 
session_start(); 
if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Landlord") { header("Location: login.php"); exit(); } ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accommo NSBM</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css\all.min.css">
</head>
<body>
    <nav>
        <div class="nav__logo">Accommo NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="landlordDashboard.php">Dashboard</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <section class="section__container post_property_section__container" id="popular_section">
        <h2 class="section__header">Post New Accommodation</h2>
        
        <?php

include("config.php");


if (isset($_POST['submit'])) {

    $userId = $_SESSION['userId'];
    $userName = $_SESSION['userName'];
    $status = "Pending";

    $title = $_POST['title'];
    $description = $_POST['description'];
    $rent = $_POST['rent'];
    $bedCounts = $_POST['bedCounts'];
    $locationLink = $_POST['locationLink'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    $insertQuery = "INSERT INTO properties (userId, postedBy, title, description, locationLink, latitude, longitude, rent, status, bedCounts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connection, $insertQuery);

    mysqli_stmt_bind_param($stmt, "ssssssssss", $userId, $userName, $title, $description, $locationLink, $latitude, $longitude, $rent, $status, $bedCounts);

    $resultProperty = mysqli_stmt_execute($stmt);

    if (!$resultProperty) {
        echo " <div class='errorMessageBox'>
                          <p>Error occurred in server!</p>
                      </div><br>
                      ";
        echo " <a href='postProperty.php'>
                         <button class='btn back-btn'>Go Back </button>
                      </a>  
                     ";
    } else {

        $property_id = mysqli_insert_id($connection);

        if (isset($_FILES['images'])) {

            $file = $_FILES['images'];

            $insertImageQuery = "INSERT INTO images (propertyId, imageData) VALUES (?, ?)";
            $stmt = mysqli_prepare($connection, $insertImageQuery);

            if ($stmt) {

                mysqli_stmt_bind_param($stmt, "is", $property_id, $imageData);

                foreach ($file['tmp_name'] as $key => $tmp_name) {
                    $fileError = $file['error'][$key];
                    $fileTmpName = $tmp_name;

                    if ($fileError == 0) {
                        $imageData = file_get_contents($fileTmpName);
                        mysqli_stmt_execute($stmt);
                    }
                }
                echo " <div class='successMessageBox'>
                                          <p>Your property successfully posted!</p>
                                             </div><br>
                                           ";
                echo " <a href='landlordDashboard.php'>
                                           <button class='btn back-btn'>Go Back</button>
                                           </a>";
            }

            mysqli_stmt_close($stmt);
        } else {

            echo " <div class='errorMessageBox'>
                                          <p>Error occurred in server!</p>
                                             </div><br>
                                           ";
            echo " <a href='postProperty.php'>
                                           <button class='btn back-btn'>Go Back </button>
                                           </a>";
        }
    }
} else {

?>
    <div class="form_map_container">
        <div class="box form-box left-box">

            <form id="propertyForm" method="post" action="" enctype="multipart/form-data">

                <div class="field input">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title">
                </div>

                <div class="field textarea">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" required></textarea>
                </div>

                <div class="field input">
                    <label for="locationLink">Location Link</label>
                    <input type="text" id="locationLink" name="locationLink" readonly>
                </div>

                <div class="field input" style="display: none;">
                    <label for="latitude">Latitude</label>
                    <input type="text" id="latitude" name="latitude" readonly>
                </div>

                <div class="field input" style="display: none;">
                    <label for="longitude">Longitude</label>
                    <input type="text" id="longitude" name="longitude" readonly>
                </div>

                <div class="field input">
                    <label for="rent">Rent</label>
                    <input type="text" id="rent" name="rent">
                </div>

                <div class="field input">
                    <label for="bedCounts">Bed Counts</label>
                    <input type="number" id="bedCounts" name="bedCounts">
                </div>

                <div class="field">
                    <label for="images">Select up to 5 Images</label>
                    <label for="images" class="custom-file-input">Choose Images</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                    <div class="selected-files"></div>
                </div>


                <div class="field">
                    <input type="submit" class="btn" name="submit" value="POST">
                </div>


            </form>
        </div>


        <div class="box form-box right-box">

            <div id="searchBox">
                <input type="text" id="searchInput" placeholder="Enter a location">
                <button onclick="searchLocation()">Search</button>
            </div>

            <div id="map"></div>
        </div>

    <?php } ?>

    </div>


</section>


















<footer class="footer" id="footer_section">
<div class="section__container footer__container">
    <div class="footer__col">
        <h3>Accommo-NSBM</h3>
        <p>Experience unparalleled convenience with Accommo NSBM, the ultimate solution for hassle-free student housing near NSBM Green University Town. Explore a plethora of accommodation choices, simplifying the process of finding your dream living space.</p>
                <p>Say goodbye to accommodation worries and welcome a seamless booking experience with Accommo NSBM.</p>
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









<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
document.getElementById('images').addEventListener('change', function(event) {
    var files = event.target.files;
    var numSelectedImages = files.length;
    var selectedFilesContainer = document.querySelector('.selected-files');
    selectedFilesContainer.textContent = numSelectedImages + " image(s) selected";
});



function validateForm() {
    const title = document.getElementById('title').value.trim();
    const description = document.getElementById('description').value.trim();
    const rent = document.getElementById('rent').value.trim();
    const bedCounts = document.getElementById('bedCounts').value.trim();
    const latitude = document.getElementById('latitude').value.trim();
    const longitude = document.getElementById('longitude').value.trim();
    const files = document.getElementById('images').files;

    let isValid = true;


    if (files.length === 0) {
        isValid = false;
        alert("Please select at least one image.");

    }

    if (title === '') {
        isValid = false;
        alert('Please enter a title.');
    }

    if (description === '') {
        isValid = false;
        alert('Please enter a description.');
    }

    if (rent === '') {
        isValid = false;
        alert('Please enter rent amount.');
    } else if (isNaN(rent)) {
        isValid = false;
        alert('Rent amount must be a number.');
    }

    if (latitude === '' || longitude === '') {
        isValid = false;
        alert('Please search and select a location.');
    }

    if (bedCounts === '') {
        isValid = false;
        alert('Please enter bed counts.');
    } else if (isNaN(bedCounts) || bedCounts <= 0) {
        isValid = false;
        alert('Bed counts must be a positive number.');
    }

    return isValid;
}

document.getElementById('propertyForm').addEventListener('submit', function(event) {
    if (!validateForm()) {
        event.preventDefault(); // Prevent form submission if validation fails
    }
});





let map = L.map('map').setView([6.8208936, 80.03972288538341], 15); // Default view

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

let marker = L.marker([6.8208936, 80.03972288538341]).addTo(map);;

function searchLocation() {
    const searchInput = document.getElementById('searchInput').value;

    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${searchInput}`)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const {
                    lat,
                    lon
                } = data[0];
                const newLatLng = new L.LatLng(lat, lon);
                map.setView(newLatLng, 13);
                if (marker) {
                    map.removeLayer(marker);
                }

                marker = L.marker(newLatLng).addTo(map);
                document.getElementById('locationLink').value = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}`;
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lon;


            } else {
                console.log('Location not found');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
</script>

</body>

</html>