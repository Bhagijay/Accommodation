<?php
session_start();

include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Landlord") {
    header("Location: new.php");
    exit();
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
            <li class="link"><a href="#footer_section">Contact</a></li>
            <li class="link"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <section class="section__container landlord_dashboard_section__container" id="my_accommodations_section">
        <div class="webadmin_dashboard_buttons_container">
            <a href="reservationsRequests.php"><button class="big-button">View Reservation Requests</button></a>
            <a href="postProperty.php"><button class="big-button">Add New Property</button></a>
        </div>
    </section>

    <section class="section__container webadmin_dashboard_section__container" id="popular_section">
        <h2 class="section__header">My Properties</h2>
        <div class="webadmin_dashboard_accommodation_container">
        <?php
    include("config.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_button'])) {
        if (isset($_POST['property_id'])) {
            $propertyId = $_POST['property_id'];
            $deleteImagesQuery = "DELETE FROM images WHERE property_id = $propertyId";

            if ($connection->query($deleteImagesQuery) === TRUE) {
                $deleteReservationsQuery = "DELETE FROM reservations WHERE propertyId = $propertyId";

                if ($connection->query($deleteReservationsQuery) === TRUE) {
                    $deletePropertyQuery = "DELETE FROM properties WHERE id = $propertyId";

                    if ($connection->query($deletePropertyQuery) === TRUE) {
                        // Property deleted successfully
                    } else {
                        echo "Error deleting property: " . $connection->error;
                    }
                } else {
                    echo "Error deleting reservations: " . $connection->error;
                }
            } else {
                echo "Error deleting images: " . $connection->error;
            }
        } else {
            echo "Property ID is not set.";
        }
    }

    $sql = "SELECT properties.*, latest_images.image_data FROM properties INNER JOIN ( SELECT property_id, MAX(id) AS maxImageId, image_data FROM images GROUP BY property_id ) AS latest_images ON properties.id = latest_images.property_id";

    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card">';
            echo '<div class="card__content">';
            echo '<form method="post">';
            echo '<div class="card__buttons">';
            echo '<input type="hidden" name="property_id" value="' . $row["id"] . '">';
            echo '<button type="submit" class="delete-button" name="delete_button">Delete</button>';
            echo '<a href="updateProperty.php?property_id=' . $row["id"] . '&title=' . $row["title"] . '&description=' . $row["description"] . '&bedCounts=' . $row["bedCounts"]. '&rent=' . $row["rent"] . '&longitude=' . $row["longitude"] . '&latitude=' . $row["latitude"] . '&locationLink=' . $row["Address"] . '" class="update-button">Update</a>';
            echo '</div>';
            echo '</form>';
            echo '<h2 class="card__title">' . $row["title"] . '</h2>';
            echo '<p class="card__description">' . substr($row['description'], 0, 60) . '...</p>';
            echo '<div class="card__details">';
            echo '<p class="beds"><strong>Beds Available</strong>' . $row["bedCounts"] . '</p>';
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
            echo '<img src="' . $row["image_data"] . '" alt="' . $row["title"] . '">';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "No accommodations found.";
    }
    $connection->close();
    ?>
        </div>
    </section>
    <?php
    include "footer.php";
    ?>

</body>

</html>
