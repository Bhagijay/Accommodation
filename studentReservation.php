<?php

session_start();

include("config.php");


if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Student") {
    header("Location: login.php");
    exit();
}


$propertyId = $_GET['propertyId'];
$userId = $_SESSION['userId'];


$reservationQuery = "SELECT * FROM reservations WHERE propertyId = $propertyId AND userId = '$userId'";
$reservationResult = $connection->query($reservationQuery);

$reserved = ($reservationResult && $reservationResult->num_rows > 0);

if (isset($_POST['reserve'])) {

    $selectPropertyQuery = "SELECT bedCounts FROM properties WHERE id = $propertyId";
    $propertyResult = $connection->query($selectPropertyQuery);
    if ($propertyResult && $propertyResult->num_rows > 0) {
        $propertyData = $propertyResult->fetch_assoc();
        $bedCounts = $propertyData['bedCounts'];
        if ($bedCounts > 0) {

            $updateBedCountsQuery = "UPDATE properties SET bedCounts = bedCounts - 1 WHERE id = $propertyId";
            if ($connection->query($updateBedCountsQuery) === TRUE) {
      
                $insertReservationQuery = "INSERT INTO reservations (userId, propertyId, status) VALUES ('$userId', '$propertyId', 'Pending')";
                if ($connection->query($insertReservationQuery) === TRUE) {
                    $reserved = true;
  
                    echo "<script>document.getElementById('bedCounts').innerText = '$bedCounts';</script>";
                } else {
                    echo "Error: " . $connection->error;
                }
            } else {
                echo "Error: " . $connection->error;
            }
        } else {
            echo "No available spaces.";
        }
    } else {
        echo "Error: " . $connection->error;
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
</head>

<body>



    <nav>
        <div class="nav__logo">Accommo NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="studentdashboard.php">Dashboard</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <?php

    include("config.php");


    $selectQuery = "SELECT image_data FROM images WHERE property_id={$_GET['propertyId']}";
    $big_result = $connection->query($selectQuery);
    $small_result = $connection->query($selectQuery);

    $selectUserIdQuery = "SELECT userId FROM properties WHERE id={$_GET['propertyId']}";
    $result = $connection->query($selectUserIdQuery);
    if ($result) {

        $row = $result->fetch_assoc();
        if ($row) {

            $landlordId = $row['userId'];
        }
    }
    $selectUserDeatils = "SELECT name,email,mobile,gender FROM users WHERE userId=$landlordId";
    $sql = "SELECT name,email,mobile,gender FROM users WHERE userId=$landlordId";


    $userResult = $connection->query($sql);

    if ($userResult) {

        if ($userResult->num_rows > 0) {

            $row = $userResult->fetch_assoc();

            $name = $row["name"];
            $email = $row["email"];
            $mobile = $row["mobile"];
            $gender = $row["gender"];
        } else {
            echo "No records found";
        }
    } else {
        echo "Error: " . $conn->error;
    }

    $connection->close();

    ?>


    <section class="section__container viewProperty_section__container" id="my_accommodations_section">

        <div class="main-box">



            <div class="property-card">
                <h2><?php echo $_GET['title']; ?></h2>
                <p><?php echo $_GET['description']; ?></p>
                <p ><strong>Spaces Available: </strong><span id="bedCounts"><?php echo $_GET['bedCounts']; ?></span></p>
                <p><strong>Address: </strong><?php echo $_GET['address']; ?></p>
                <p><strong>Rent: </strong><?php echo $_GET['rent']; ?></p>
                <div class="user-details">
                    <p><?php echo $name ?></p>
                    <p><?php echo $email ?></p>
                    <p><?php echo $mobile ?></p>
                </div>

                <div class="webadmin_dashboard_buttons_container">
                    <?php if ($reserved) : ?>
                        <button class="big-button reserved-btn" disabled>Reserved</button>
                    <?php else : ?>
                        <form method="post">
                            <button type="submit" name="reserve" id="reserve-Btn" class="big-button reserve-btn">Reserve</button>
                        </form>
                    <?php endif; ?>
                </div>


            </div>


            <div class="image-viewer">
    <?php
    // Display the main image if $big_result is a valid result set
    if ($big_result && $big_result->num_rows > 0) {
        $row = $big_result->fetch_assoc();
        $bigImageName = $row['image_data']; // Assuming you store the image file name in the database
        echo '<img src="' . $bigImageName . '" alt="Main Image" id="big-image">';
    } else {
        echo '<p>No main image found</p>';
    }
    ?>

    <div id="small-image-container">
        <?php
        // Display thumbnail images if $small_result is a valid result set
        if ($small_result && $small_result->num_rows > 0) {
            $count = 1;
            while ($row = $small_result->fetch_assoc()) {
                $imageFileName = $row['image_data']; // Assuming you store the image file name in the database
                $src = '' . $imageFileName;
                $borderStyle = ($count == 1) ? 'border: 3px solid #4285f4;' : ''; // Highlight the first image
                $imageId = "image" . $count;

                echo "<img src='$src' alt='Thumbnail' class='small-images' id='$imageId' onclick='showImage(\"$src\",\"$imageId\")' style='$borderStyle'>";

                $count++;
            }
        } else {
            echo '<p>No thumbnail images found</p>';
        }
        ?>
    </div>
</div>




        </div>

        <div class="box form-box map-box">
            <div id="map"></div>
        </div>






                </section>



    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        function showImage(image, imageId) {
            document.getElementById('big-image').src = image;
            var allImages = document.getElementsByClassName('small-images');
            for (image of allImages) {
                image.style.border = 'none';
            }
            document.getElementById(imageId).style.border = '4px solid #4285f4';


        }

        let latitude = <?php echo json_encode(isset($_GET['latitude']) ? $_GET['latitude'] : ''); ?>;
        let longitude = <?php echo json_encode(isset($_GET['longitude']) ? $_GET['longitude'] : ''); ?>;
        let map = L.map('map').setView([latitude, longitude], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        let marker = L.marker([latitude, longitude]).addTo(map);;

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


        <?php if ($reserved) : ?>
            var bedCounts = document.getElementById('bedCounts');
            bedCounts.innerText = parseInt(bedCounts.innerText) - 1;
        <?php endif; ?>

    </script>
<?php
    include "footer.php";
    ?>

</body>

</html>