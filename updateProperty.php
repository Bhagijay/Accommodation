<?php
session_start();
include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Landlord") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        $propertyId = $_POST['property_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $rent = $_POST['rent'];
        $bedCounts = $_POST['bedCounts'];
        $Address = $_POST['Address'];
        $latitude = $_POST['latitude'];
        $longitude = $_POST['longitude'];

        $updateQuery = "UPDATE properties SET title=?, description=?, Address=?, latitude=?, longitude=?, rent=?, bedCounts=? WHERE id=?";
        $stmt = mysqli_prepare($connection, $updateQuery);
        if ($stmt === FALSE) {
            die("Error in preparing statement: " . mysqli_error($connection));
        }
        
        mysqli_stmt_bind_param($stmt, "sssssssi", $title, $description, $Address, $latitude, $longitude, $rent, $bedCounts, $propertyId);

        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            // Property details updated successfully
            echo "<div class='successMessageBox'><p>Property details updated successfully!</p></div>";
            // Redirect to landlord dashboard or any other appropriate page
            header("Location: landlordDashboard.php");
            exit();
        } else {
            echo "<div class='errorMessageBox'><p>Error occurred while updating property details: " . mysqli_error($connection) . "</p></div>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<div class='errorMessageBox'><p>Form submission error: Required fields are missing.</p></div>";
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
        <div class="nav__logo">Accommo NSBM</div>
        <ul class="nav__links">
            <li class="link"><a href="index.php">Home</a></li>
            <li class="link"><a href="landlordDashboard.php">Dashboard</a></li>
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="section__container post_property_section__container" id="popular_section">
        <h2 class="section__header">Update Property</h2>

        <div class="form_map_container">
            <div class="box form-box left-box">
                <form id="propertyForm" method="post" action="" enctype="multipart/form-data">
                    <input type="hidden" name="property_id" value="<?php echo isset($_GET['property_id']) ? htmlspecialchars($_GET['property_id']) : ''; ?>">

                    <div class="field input">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" value="<?php echo isset($_GET['title']) ? htmlspecialchars($_GET['title']) : ''; ?>">
                    </div>

                    <div class="field textarea">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" required><?php echo isset($_GET['description']) ? htmlspecialchars($_GET['description']) : ''; ?></textarea>
                    </div>

                    <div class="field input">
                        <label for="Address">Address</label>
                        <input type="text" id="Address" name="Address"  value="<?php echo isset($_GET['Address']) ? htmlspecialchars($_GET['Address']) : ''; ?>">
                    </div>

                    <div class="field input" style="display: none;">
                        <label for="latitude" >Latitude</label>
                        <input type="text" id="latitude" name="latitude"  value="<?php echo isset($_GET['latitude']) ? htmlspecialchars($_GET['latitude']) : ''; ?>">
                    </div>

                    <div class="field input" style="display: none;">
                        <label for="longitude">Longitude</label>
                        <input type="text" id="longitude" name="longitude"  value="<?php echo isset($_GET['longitude']) ? htmlspecialchars($_GET['longitude']) : ''; ?>">
                    </div>

                    <div class="field input">
                        <label for="rent">Rent</label>
                        <input type="text" id="rent" name="rent" value="<?php echo isset($_GET['rent']) ? htmlspecialchars($_GET['rent']) : ''; ?>">
                    </div>

                    <div class="field input">
                        <label for="bedCounts">Bed Counts</label>
                        <input type="number" id="bedCounts" name="bedCounts" value="<?php echo isset($_GET['bedCounts']) ? htmlspecialchars($_GET['bedCounts']) : ''; ?>">
                    </div>

                    <div class="field">
                        <label for="images">Select up to 4 Images</label>
                        <label for="images" class="custom-file-input">Choose Images</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        <div class="selected-files"></div>
                    </div>

                    <div class="field">
                        <input type="submit" class="btn" name="submit" value="UPDATE">
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
        const defaultLatitude = <?php echo isset($_GET['latitude']) ? htmlspecialchars($_GET['latitude']) : '6.8208936'; ?>;
    const defaultLongitude = <?php echo isset($_GET['longitude']) ? htmlspecialchars($_GET['longitude']) : '80.03972288538341'; ?>;

    let map = L.map('map').setView([defaultLatitude, defaultLongitude], 15); // Default view    

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

let marker = L.marker([defaultLatitude, defaultLongitude]).addTo(map);;
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
