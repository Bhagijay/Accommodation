<?php

session_start();

include("config.php");


if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Warden") {
    header("Location: login.php");
    exit();
}

$propertyStatus = isset($_GET['status']) ? $_GET['status'] : '';

if ($propertyStatus == "Accepted") {
    $acceptButtonDisplay = "";
    $rejectButtonDisplay = "none";
}
if ($propertyStatus == "Rejected") {
    $acceptButtonDisplay = "none";
    $rejectButtonDisplay = "";
}
if ($propertyStatus == "Pending") {
    $acceptButtonDisplay = "";
    $rejectButtonDisplay = "";
}




if (isset($_GET['action']) && isset($_GET['property_id'])) {

    $action = $_GET['action'];
    $propertyId = $_GET['property_id'];


    if ($action === 'accept') {
        $updateQuery = "UPDATE properties SET status = 'Accepted' WHERE id = $propertyId";
    } elseif ($action === 'reject') {
        $updateQuery = "UPDATE properties SET status = 'Rejected' WHERE id = $propertyId";
    } else {
        http_response_code(400);
        exit("Invalid action");
    }

    if ($connection->query($updateQuery) === TRUE) {
        http_response_code(200);
        exit("Property $action successfully");
    } else {
        http_response_code(500);
        exit("Error updating property: " . $connection->error);
    }
}
$connection->close();

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
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <?php

    include("config.php");
    $landlordId = null;

    $selectQuery = "SELECT image_data FROM images WHERE property_id={$_GET['property_id']}";
    $big_result = $connection->query($selectQuery);
    $small_result = $connection->query($selectQuery);

    $selectUserIdQuery = "SELECT userId FROM properties WHERE id={$_GET['property_id']}";
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
                <p><strong>Spaces Available: </strong><?php echo $_GET['bedCounts']; ?></p>
                <p><strong>Address </strong><?php echo $_GET['rent']; ?></p>
                <p><strong>Rent: </strong><?php echo $_GET['locationLink']; ?></p>
           
                <div class="user-details">
                    <p><?php echo $name ?></p>
                    <p><?php echo $email ?></p>
                    <p><?php echo $mobile ?></p>
                </div>
                <div class="webadmin_dashboard_buttons_container">
                    <a href="#" onclick="acceptProperty(<?php echo $_GET['property_id']; ?>)"><button id="acceptBtn" class="big-button accept-btn">Accept</button></a>
                    <a href="#" onclick="rejectProperty(<?php echo $_GET['property_id']; ?>)"><button id="rejectBtn" class="big-button reject-btn">Reject</button></a>

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
        document.getElementById('acceptBtn').innerText = '<?php echo $propertyStatus == "Pending" ? "Accept" : $propertyStatus; ?>';
        document.getElementById('rejectBtn').innerText = '<?php echo $propertyStatus == "Pending" ? "Reject" : $propertyStatus; ?>';


        document.getElementById('acceptBtn').style.display = '<?php echo $acceptButtonDisplay; ?>';
        document.getElementById('rejectBtn').style.display = '<?php echo $rejectButtonDisplay; ?>';

        function acceptProperty(propertyId) {

            fetch(`viewProperty.php?action=accept&property_id=${propertyId}`)
                .then(response => {
                    if (response.ok) {

                        document.getElementById('rejectBtn').style.display = 'none';
                        document.getElementById('acceptBtn').innerText = 'Accepted';
                        console.log('Property accepted successfully');
                    } else {
                        console.error('Failed to accept property');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }


        function rejectProperty(propertyId) {

            fetch(`viewProperty.php?action=reject&property_id=${propertyId}`)
                .then(response => {
                    if (response.ok) {

                        document.getElementById('acceptBtn').style.display = 'none';
                        document.getElementById('rejectBtn').innerText = 'Rejected';
                        console.log('Property rejected successfully');
                    } else {
                        console.error('Failed to reject property');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }



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