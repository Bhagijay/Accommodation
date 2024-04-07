<?php 
session_start(); 
if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Landlord") { 
    header("Location: login.php"); 
    exit(); 
} 
?>

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
            // Your existing code for posting property

            // After successful property post
            if ($resultProperty) {
                // Display uploaded images
                echo "<h3>Uploaded Images:</h3>";
                echo "<div class='uploaded-images'>";
                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    $fileError = $_FILES['images']['error'][$key];
                    if ($fileError == 0) {
                        $imageData = file_get_contents($tmp_name);
                        $imageType = $_FILES['images']['type'][$key];
                        $base64 = 'data:' . $imageType . ';base64,' . base64_encode($imageData);
                        echo "<img src='" . $base64 . "' alt='Property Image'>";
                    }
                }
                echo "</div>";
            }
        }
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
                <?php 
             ?>

            </div>


    </section>

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
    <?php
    include "footer.php";
    ?>
</body>
</html>
