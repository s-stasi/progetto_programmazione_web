<?php
header("Content-Type: application/json");
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id_umbrella = $_GET['id_ombrellone'] ?? null;
  $date_start = $_GET['data_inizio'] ?? null;

  if(!$id_umbrella || !$date_start) {
    echo json_encode(["success" => false, "message" => "Dati obbligatori mancanti per la prenotazione."]);
    exit;
  }
  
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
  if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connessione fallita."]);
    exit;
  }

  try {
    $conn->begin_transaction();

    // Get the contract
    $sql = "SELECT 
              c.numProgr AS id,
              cl.nome,
              cl.cognome,
              cl.dataNascita AS data_nascita,
              cl.indirizzo,
              cl.email,
              cl.telefono AS cellulare,
              c.importo AS prezzo_totale,
              (SELECT MIN(data) FROM OmbrelloneVenduto WHERE contratto = c.numProgr) AS data_inizio,
              (SELECT MAX(data) FROM OmbrelloneVenduto WHERE contratto = c.numProgr) AS data_fine
            FROM OmbrelloneVenduto ov
            JOIN Contratto c ON ov.contratto = c.numProgr
            JOIN Cliente cl ON c.stipulatoDa = cl.codice
            WHERE ov.idOmbrellone = ? AND ov.data = ?
            LIMIT 1";
    $stmt_get = $conn->prepare($sql);
    $stmt_get->bind_param("is", $id_umbrella, $date_start);
    $stmt_get->execute();
    
    $result = $stmt_get->get_result();
    
    if ($row = $result->fetch_assoc()) {
      echo json_encode(["success" => true, "data" => $row]);
    } else {
      echo json_encode(["success" => false, "message" => "Nessun contratto attivo trovato per questo ombrellone in questa data."]);
    }

    $stmt_get->close();
  } catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
  }
  
  $conn->close();
}
?>