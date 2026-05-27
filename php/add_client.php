<?php
header("Content-Type: application/json");
require_once('config.php');

// Parametri unificati in italiano provenienti dal form unico
$nome         = $_POST['nome'] ?? null;
$cognome      = $_POST['cognome'] ?? null;
$data_nascita = $_POST['data_nascita'] ?? null;
$email        = $_POST['email'] ?? null;
$cellulare    = $_POST['cellulare'] ?? null;

if (!$nome || !$cognome) {
  echo json_encode(["success" => false, "message" => "Nome e Cognome sono obbligatori."]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connessione fallita."]);
  exit;
}

try {
  $stmt = $conn->prepare("INSERT INTO Cliente (nome, cognome, dataNascita, email, telefono) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $nome, $cognome, $data_nascita, $email, $cellulare);
  
  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Cliente registrato con successo!"]);
  } else {
    echo json_encode(["success" => false, "message" => "Errore durante l'esecuzione della query."]);
  }
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>