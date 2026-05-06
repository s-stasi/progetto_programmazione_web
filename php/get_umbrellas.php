<?php
include('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connessione fallita"]));
}

$sql = "SELECT * FROM ombrellone";
$result = $conn->query($sql);
$ombrelloni = [];

while($row = $result->fetch_assoc()) {
    $ombrelloni[] = $row;
}

header('Content-Type: application/json');
echo json_encode($ombrelloni);

$conn->close();
?>