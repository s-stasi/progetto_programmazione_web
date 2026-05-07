<?php
// Permette al Live Server locale di leggere i dati
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Connessione fallita: " . $conn->connect_error]));
}

$sql = "SELECT 
            o.id AS id_ombrellone, 
            o.settore, 
            o.numFila AS numero_fila, 
            o.numPostoFila AS numero_ordine, 
            t.nome AS tipologia_nome 
        FROM Ombrellone o 
        JOIN Tipologia t ON o.tipologia = t.codice";

$result = $conn->query($sql);

if ($result) {
    $ombrelloni = [];
    while($row = $result->fetch_assoc()) {
        $ombrelloni[] = $row;
    }
    echo json_encode($ombrelloni);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Errore Database: " . $conn->error]);
}

$conn->close();
?>