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
    <title>Accommo-NSBM</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css\all.min.css">
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
    <section class="section__container post_property_section__container" id="popular_section">
        <h2 class="section__header">Post New Accommodation</h2>
        
        <?php
        include("config.php");

        if (isset($_POST['submit'])) {
            $userId = $_SESSION['userId'];
            $userName = $_SESSION['userName'];

            $title = $_POST['title'];
            $description = $_POST['description'];
            $rent = $_POST['rent'];
            $bedCounts = $_POST['bedCounts'];
            $Address = $_POST['Address'];
            $latitude = $_POST['latitude'];
            $longitude = $_POST['longitude'];

            $num_images = isset($_FILES['image']['name']) ? count($_FILES['image']['name']) : 0; // Check if files were uploaded before counting

            $insertQuery = "INSERT INTO properties (userId, postedBy, title, description, Address, latitude, longitude, rent, bedCounts) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $insertQuery);

            if ($stmt === false) {
                echo "<div class='errorMessageBox'>
                          <p>Error occurred in server while preparing the statement: " . mysqli_error($connection) . "</p>
                      </div><br>";
                echo "<a href='postProperty.php'>
                          <button class='btn back-btn'>Go Back </button>
                      </a>";
                exit(); // Stop execution
            }

            mysqli_stmt_bind_param($stmt, "sssssssss", $userId, $userName, $title, $description, $Address, $latitude, $longitude, $rent, $bedCounts);

            $resultProperty = mysqli_stmt_execute($stmt);

            if ($resultProperty) {
                $property_id = mysqli_insert_id($connection);

                if ($num_images > 0) {
                    // Directory where uploaded images will be stored
                    $uploadDirectory = "uploads/";

                    // Loop through each uploaded image
                    for ($i = 0; $i < $num_images; $i++) {
                        if ($_FILES['image']['error'][$i] === UPLOAD_ERR_OK) {
                            $imageName = $_FILES['image']['name'][$i];
                            $imageTmpName = $_FILES['image']['tmp_name'][$i];
                            $destination = $uploadDirectory . basename($imageName);
                            $uploadDirectory = "uploads/";
if (!file_exists($uploadDirectory) && !is_dir($uploadDirectory)) {
    mkdir($uploadDirectory, 0777, true);
}
                            if (move_uploaded_file($imageTmpName, $destination)) {
                                $insertImageQuery = "INSERT INTO images (property_id, image_data) VALUES (?, ?)";
                                $stmtImage = mysqli_prepare($connection, $insertImageQuery);

                                if ($stmtImage) {
                                    mysqli_stmt_bind_param($stmtImage, "is", $property_id, $destination);
                                    mysqli_stmt_execute($stmtImage);
                                    mysqli_stmt_close($stmtImage);
                                } else {
                                    echo "Failed to prepare statement for image insertion.";
                                }
                            } else {
                                echo "Failed to move uploaded image to destination directory.";
                            }
                        } else {
                            echo "Error occurred while uploading image.";
                        }
                    }
                }
                echo "Property and images uploaded successfully.";
                header("Location: success_page.php");
                exit();
            } else {
                echo "Failed to insert property into the database.";
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
                            <label for="Address">Address</label>
                            <input type="text" id="Address" name="Address" readonly>
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
    <input type="file" id="images" name="image[]" accept="image/*" multiple>
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
        map.on('click', function(e) {
        const clickedLatLng = e.latlng; 
        const { lat, lng } = clickedLatLng;

        console.log('Latitude:', lat);
        console.log('Longitude:', lng);

        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker(clickedLatLng).addTo(map);


        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;

        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(response => response.json())
            .then(data => {
                if (data && data.display_name) {
                    const address = data.display_name;
                    document.getElementById('Address').value = address;
                } else {
                    console.log('Address not found');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
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
                        // document.getElementById('locationLink').value = `https://www.openstreetmap.org/?mlat=${lat}&mlon=${lon}`;
                    

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
