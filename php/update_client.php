<?php
header("Content-Type: application/json");
require_once('config.php');

$id           = $_POST['clientId'] ?? null;
$nome         = $_POST['nome'] ?? null;
$cognome      = $_POST['cognome'] ?? null;
$data_nascita = $_POST['data_nascita'] ?? null;
$email        = $_POST['email'] ?? null;
$cellulare    = $_POST['cellulare'] ?? null;

if (!$id || !$nome || !$cognome) {
  echo json_encode(["success" => false, "message" => "ID, Nome e Cognome sono obbligatori."]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connessione fallita."]);
  exit;
}

try {
  $stmt = $conn->prepare("UPDATE Cliente SET nome = ?, cognome = ?, dataNascita = ?, email = ?, telefono = ? WHERE codice = ?");
  $stmt->bind_param("sssssi", $nome, $cognome, $data_nascita, $email, $cellulare, $id);
  
  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Cliente aggiornato con successo!"]);
  } else {
    echo json_encode(["success" => false, "message" => "Errore durante l'esecuzione della query."]);
  }
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>