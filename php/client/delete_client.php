<?php
header("Content-Type: application/json");
require_once('../config.php');

// Recupera l'ID dalla richiesta
$id = $_GET['id'] ?? null;

if (!$id) {
  echo json_encode(["success" => false, "message" => "ID cliente mancante."]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connessione al database fallita."]);
  exit;
}

try {
  $stmt = $conn->prepare("DELETE FROM Cliente WHERE codice = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Cliente eliminato con successo."]);
  } else {
    echo json_encode(["success" => false, "message" => "Cliente non trovato."]);
  }
} catch (mysqli_sql_exception $e) {
  // Errore 1451: Chiave esterna (Foreign Key constraint) se ha contratti attivi
  if ($e->getCode() == 1451) {
    echo json_encode([
      "success" => false, 
      "message" => "Errore di integrità: Questo cliente ha dei contratti attivi e non può essere eliminato."
    ]);
  } else {
    echo json_encode(["success" => false, "message" => "Errore del database: " . $e->getMessage()]);
  }
}

$stmt->close();
$conn->close();
?>