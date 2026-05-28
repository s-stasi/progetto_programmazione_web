<?php
header('Content-Type: application/json');
require_once('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connessione al database fallita']);
    exit;
}


$clientId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($clientId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Cliente non valido']);
    exit;
}

$sql = "SELECT numProgr, data, importo, stato FROM Contratto WHERE stipulatoDa = ?ORDER BY data DESC";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $clientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $contracts = [];
    while ($row = $result->fetch_assoc()) {
        $contracts[] = $row;
    }
    
    echo json_encode(['success' => true, 'contracts' => $contracts]);
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nella query']);
}

$conn->close();
?>