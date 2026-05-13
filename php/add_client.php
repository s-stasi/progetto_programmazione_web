<?php
header("Content-Type: application/json");
require_once('config.php');

// Retrieve POST data from the form
$firstName = $_POST['firstName'] ?? null;
$lastName = $_POST['lastName'] ?? null;
$dob = $_POST['dob'] ?? null;

// Basic validation
if (!$firstName || !$lastName) {
  echo json_encode(["success" => false, "message" => "First and Last name are mandatory."]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Connection failed."]);
  exit;
}

try {
  // Prepared statement to prevent SQL Injection
  $stmt = $conn->prepare("INSERT INTO Cliente (nome, cognome, dataNascita) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $firstName, $lastName, $dob);
  
  if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Client registered successfully!"]);
  } else {
    echo json_encode(["success" => false, "message" => "Error executing query."]);
  }
} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "DB Error: " . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>