<?php
header("Content-Type: application/json");
require_once('../config.php');

$id_contratto = $_GET['id'] ?? null;

if (!$id_contratto) {
    echo json_encode(["success" => false, "message" => "ID contratto mancante."]);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connessione al database fallita."]);
    exit;
}

try {
    $conn->begin_transaction();

    // Rimuove le giornate occupate associate a questo contratto
    $stmt_libera = $conn->prepare("DELETE FROM OmbrelloneVenduto WHERE contratto = ?");
    $stmt_libera->bind_param("i", $id_contratto);
    $stmt_libera->execute();
    $stmt_libera->close();

    // Rimuove il contratto (o puoi fare un UPDATE sullo stato se preferisci mantenerlo nello storico)
    $stmt_contratto = $conn->prepare("DELETE FROM Contratto WHERE numProgr = ?");
    $stmt_contratto->bind_param("i", $id_contratto);
    $stmt_contratto->execute();
    $stmt_contratto->close();

    $conn->commit();
    $conn->close();
    echo json_encode(["success" => true, "message" => "Prenotazione cancellata e ombrellone liberato."]);
    exit;

} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
    exit;
}
?>