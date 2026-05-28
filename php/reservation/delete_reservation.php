<?php
// php/delete_reservation.php
require_once('../config.php');

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
    $conn->begin_transaction();

    $sql_libera = "DELETE FROM OmbrelloneVenduto WHERE idOmbrellone = ? AND data BETWEEN ? AND ?";
    $stmt_libera = $conn->prepare($sql_libera);
    $stmt_libera->bind_param("iss", $id_umbrella, $date_start, $date_end);
    $stmt_libera->execute();

    $sql_stato = "UPDATE Contratto SET stato = 'Cancellato' WHERE numProgr = ?";
    $stmt_stato = $conn->prepare($sql_stato);
    $stmt_stato->bind_param("i", $id_contratto);
    $stmt_stato->execute();

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Prenotazione cancellata loggata nello storico."]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>