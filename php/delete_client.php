<?php
header("Content-Type: application/json");
require_once('config.php');

// Get the ID from the request
$id = $_GET['id'] ?? null;

if (!$id) {
  echo json_encode(["success" => false, "message" => "Missing client ID"]);
  exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
  echo json_encode(["success" => false, "message" => "Database connection failed"]);
  exit;
}

try {
  $stmt = $conn->prepare("DELETE FROM Cliente WHERE codice = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "Client deleted successfully"]);
  } else {
    echo json_encode(["success" => false, "message" => "Client not found"]);
  }
} catch (mysqli_sql_exception $e) {
  // Catch error 1451: Cannot delete or update a parent row (Foreign Key constraint)
  if ($e->getCode() == 1451) {
    echo json_encode([
      "success" => false, 
      "message" => "Integrity Error: This client has active contracts and cannot be deleted."
    ]);
  } else {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
  }
}

$stmt->close();
$conn->close();
?>