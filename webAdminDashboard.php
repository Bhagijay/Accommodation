<?php

session_start();

include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "WebAdmin") {
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





    <section class="section__container webadmin_dashboard_section__container" id="popular_section">
        <div class="webadmin_dashboard_buttons_container">
            <a href="createAccount.php"><button class="big-button">Create New Account</button></a>
            <a href="postArticle.php"><button class="big-button">Post New Article</button></a>
        </div>

    </section>



    <section class="section__container webadmin_dashboard_section__container" id="popular_section">
        <h2 class="section__header">All Accommodations</h2>
        <div class="webadmin_dashboard_accommodation_container">
            <?php
            include("config.php");

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_button'])) {

                if (isset($_POST['property_id'])) {
                    $propertyId = $_POST['property_id'];


                    $deleteImagesQuery = "DELETE FROM images WHERE propertyId = $propertyId";
                    $connection->query($deleteImagesQuery);

                    $deletePropertyQuery = "DELETE FROM properties WHERE propertyId = $propertyId";
                    if ($connection->query($deletePropertyQuery) === TRUE) {
                    } else {
                        echo "Error deleting property: " . $connection->error;
                    }
                } else {
                    echo "Property ID is not set.";
                }
            }

            $sql = "SELECT properties.*, images.imageData 
        FROM properties 
        INNER JOIN (
            SELECT propertyId, MAX(imageId) AS maxImageId 
            FROM images 
            GROUP BY propertyId
        ) AS latest_images ON properties.propertyId = latest_images.propertyId
        INNER JOIN images ON latest_images.maxImageId = images.imageId
        ORDER BY properties.postedAt DESC";

            $result = $connection->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="card">';

                    echo '<div class="card__content">';

                    echo '<form method="post">';
                    echo '<div class="card__buttons">';
                    echo '<input type="hidden" name="property_id" value="' . $row["propertyId"] . '">';
                    echo '<button type="submit" class="delete-button" name="delete_button">Delete</button>';

                    echo '</div>';
                    echo '</form>';

                    echo '<h2 class="card__title">' . $row["title"] . '</h2>';
                    echo '<p class="card__description">' . substr($row['description'], 0, 60) . '...</p>';

                    echo '<div class="card__details">';
                    echo '<p class="beds"><strong>Beds Available</strong>' . $row["bedCounts"] . '</p>';
                    echo '<p class="beds"><strong>Posted at</strong> ' . date("Y-m-d", strtotime($row["postedAt"])) . '</p>';
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
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($row["imageData"]) . '" alt="' . $row["title"] . '">';
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