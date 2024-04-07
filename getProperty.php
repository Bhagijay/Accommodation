<?php

function fetchPendingAccommodations($connection)
{
    $sql = "SELECT properties.*, latest_images.image_data 
            FROM properties  
            INNER JOIN (
                SELECT property_id, MAX(id) AS maxImageId, image_data
                FROM images 
                GROUP BY property_id
            ) AS latest_images ON properties.id = latest_images.property_id 
            WHERE properties.status = 'Pending';";

    $result = $connection->query($sql);

    $properties = array();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    }

    return $properties;
}
function fetchapproved($connection)
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
        $properties = [];
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
        return $properties;
    } else {
        return [];
    }
}

function fetchAllAccommodations($connection)
{
    $sql = "SELECT properties.*, latest_images.image_data 
    FROM properties  
    INNER JOIN (
        SELECT property_id, MAX(id) AS maxImageId, image_data
        FROM images 
        GROUP BY property_id
    ) AS latest_images ON properties.id = latest_images.property_id ";



    $result = $connection->query($sql);

    $properties = array();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $properties[] = $row;
        }
    }

    return $properties;
}
?>