<?php
header("Content-Type: application/json");
require_once('config.php');

// Retrieve POST data
$id = $_POST['clientId'] ?? null;
$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$dob = $_POST['dob'] ?? null;

if (!$id || !$firstName || !$lastName) {
  echo json_encode(["success" => false, "message" => "ID, First Name and Last Name are mandatory."]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connection failed."]);
  exit;
}

try {
  // Prepared statement for UPDATE
  $stmt = $conn->prepare("UPDATE Cliente SET nome = ?, cognome = ?, dataNascita = ? WHERE codice = ?");
  $stmt->bind_param("sssi", $firstName, $lastName, $dob, $id);
  
  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Client updated successfully!"]);
  } else {
    echo json_encode(["success" => false, "message" => "Error executing query."]);
  }
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>