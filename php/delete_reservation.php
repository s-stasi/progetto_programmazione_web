<?php
// php/delete_reservation.php
require_once('config.php');

// Aligned with the English GET parameters passed by index.php window.location
$id_umbrella = $_GET['id_umbrella'] ?? null;
$date_start  = $_GET['start'] ?? null;
$date_end    = $_GET['end'] ?? null;

if (!$id_umbrella || !$date_start || !$date_end) {
    die("Error: Missing parameters required to delete the reservation.");
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

try {
    // Delete reserved days for the umbrella within the specific time boundaries
    $sql = "DELETE FROM OmbrelloneVenduto 
            WHERE idOmbrellone = ? 
              AND data BETWEEN ? AND ?";
              
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id_umbrella, $date_start, $date_end);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: ../interface/index.php?status=deleted");
    } else {
        header("Location: ../interface/index.php?status=not_found");
    }
    
    $stmt->close();
    $conn->close();
    exit();

} catch (Exception $e) {
    $conn->close();
    die("Error during rows deletion from OmbrelloneVenduto: " . $e->getMessage());
}
?>