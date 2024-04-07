<?php

session_start();

include("config.php");

if (!isset($_SESSION['userEmail']) || $_SESSION['userType'] != "Landlord") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['reservationId'])) {
    $action = $_POST['action'];
    $reservationId = $_POST['reservationId'];

    if ($action === 'accept') {
        $sql = "UPDATE reservations SET status = 'Accepted' WHERE reservationId = $reservationId";
        if ($connection->query($sql) === TRUE) {
            echo "Reservation accepted successfully";
        } else {
            echo "Error accepting reservation";
        }
    } elseif ($action === 'reject') {
        $sql = "UPDATE reservations SET status = 'Rejected' WHERE reservationId = $reservationId";
        if ($connection->query($sql) === TRUE) {
            echo "Reservation rejected successfully";
        } else {
            echo "Error rejecting reservation";
        }
    } else {
        echo "Invalid action";
    }
    exit();
}

$headerText = "Pending Properties";

function fetchAccommodationsByStatus($connection, $status)
{
    $userId = $_SESSION['userId'];
 $userId = $_SESSION['userId'];

$sql = "SELECT r.*, 
               p.title, 
               p.description, 
               p.bedCounts, 
               p.Address, 
               p.rent, 
               p.longitude, 
               p.latitude, 
               p.Address AS locationLink, 
               p.status AS propertyStatus, 
               i.image_data AS imageData, 
               u.userId AS studentId 
        FROM reservations r
        INNER JOIN properties p ON r.propertyId = p.id
        INNER JOIN users u ON r.userId = u.userId
        LEFT JOIN (
            SELECT property_id, MAX(id) AS latest_image_id
            FROM images
            GROUP BY property_id
        ) AS latest_images ON p.id = latest_images.property_id
        LEFT JOIN images i ON latest_images.latest_image_id = i.id
        WHERE p.userId = $userId AND r.status = '$status'";


    $result = $connection->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $studentId = $row['studentId'];

            $fetchStudentSql = "SELECT * FROM users WHERE userId = $studentId";

            $studentResult = $connection->query($fetchStudentSql);

            if ($studentResult) {
                while ($student_row = $studentResult->fetch_assoc()) {
                    $name = $student_row['name'];
                    $email = $student_row['email'];
                    $mobile = $student_row['mobile'];
                    $gender = $student_row['gender'];
                }
            } else {
                echo "Error: " . $connection->error;
            }

            echo '<div class="card" id="card_' . $row['reservationId'] . '">';
            echo '<div class="card__content">';
            if ($row["status"] == "Pending") {
                echo '<form id="reservationForm_' . $row['reservationId'] . '" method="post">';
                echo '<div class="card__buttons">';
                echo '<input type="hidden" name="reservationId" value="' . $row["reservationId"] . '">';
                echo '<button type="button" class="delete-button" onclick="rejectReservation(' . $row['reservationId'] . ')">Reject</button>';
                echo '<button type="button" class="accept-button" onclick="acceptReservation(' . $row['reservationId'] . ')">Accept</button>';
                echo '</div>';
                echo '</form>';
            }
            echo '<h2 class="card__title">' . $row["title"] . '</h2>';
            echo '<p class="card__description">' . substr($row['description'], 0, 60) . '...</p>';
            echo '<div class="card__details">';
            echo '<p class="beds"><strong>Beds Available</strong>' . $row["bedCounts"] . '</p>';

            echo '</div>';
            echo '<div class="user-details">';
            echo  '<p>Reserved By</p>';
            echo  '<p>' . $name . '</p>';
            echo  '<p>' . $email . '</p>';
            echo  '<p>' . $mobile . '</p>';
            echo  '<p>' . $gender . '</p>';
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
            echo '<img src="' . $row["imageData"] . '" alt="' . $row["title"] . '" style="height: 100%; object-fit: cover;">';
            echo '</div>';
            echo '</div>';
        }
    } else {
        echo "No accommodations found.";
    }
}

if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status === 'Pending' || $status === 'Accepted' || $status === 'Rejected') {
        $headerText = ucfirst($status) . " Properties";
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

    <script>
        function acceptReservation(reservationId) {
            performAction(reservationId, 'accept');
        }

        function rejectReservation(reservationId) {
            performAction(reservationId, 'reject');
        }

        function performAction(reservationId, action) {
            var formData = new FormData();
            formData.append('action', action);
            formData.append('reservationId', reservationId);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', 'reservationsRequests.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {

                    console.log(xhr.responseText);

                    var card = document.getElementById('card_' + reservationId);
                    if (card) {
                        card.style.display = 'none';
                    }

                } else {

                    console.error('Error performing action: ' + xhr.statusText);
                }
            };
            xhr.onerror = function() {

                console.error('Network error occurred');
            };
            xhr.send(formData);
        }
    </script>
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






    <section class="section__container landlord_dashboard_section__container" id="all_accommodations_section">
        <div class="webadmin_dashboard_buttons_container">
            <a href="?status=Pending"><button class="big-button" id="pendingBtn">Pending</button></a>
            <a href="?status=Accepted"><button class="big-button" id="acceptedBtn">Accepted</button></a>
            <a href="?status=Rejected"><button class="big-button" id="rejectedBtn">Rejected</button></a>
        </div>
    </section>





    <section class="section__container webadmin_dashboard_section__container" id="wardenDashboard_section">
        <h2 class="section__header"><?php echo $headerText; ?></h2>
        <div class="webadmin_dashboard_accommodation_container reservationsRequestsContainer">


            <?php
            if (isset($_GET['status'])) {
                $status = $_GET['status'];
                if ($status === 'Pending' || $status === 'Accepted' || $status === 'Rejected') {
                    $headerText = ucfirst($status) . " Properties";

                    fetchAccommodationsByStatus($connection, $status);
                    exit();
                }
            } else {

                fetchAccommodationsByStatus($connection, 'Pending');
                exit();
            }


            ?>



        </div>
    </section>

    <?php
    include "footer.php";
    ?>
</body>

</html>