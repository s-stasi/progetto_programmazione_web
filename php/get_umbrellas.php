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

// 1. Recupero parametri GET passati dal frontend (con fallback a oggi)
$inizio = $_GET['inizio'] ?? date('Y-m-d');
$fine = $_GET['fine'] ?? date('Y-m-d');

$sql = "SELECT 
            o.id AS id_ombrellone, 
            o.settore, 
            o.numFila AS numero_fila, 
            o.numPostoFila AS numero_ordine, 
            t.nome AS tipologia_nome,
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM OmbrelloneVenduto ov 
                    WHERE ov.idOmbrellone = o.id 
                    AND ov.data BETWEEN ? AND ?
                ) THEN 1 
                ELSE 0 
            END AS occupato
        FROM Ombrellone o 
        JOIN Tipologia t ON o.tipologia = t.codice";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $inizio, $fine);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $ombrelloni = [];
    while($row = $result->fetch_assoc()) {
        $ombrelloni[] = $row;
    }
    // Sputa fuori il JSON per l'interfaccia
    echo json_encode($ombrelloni);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Errore Database: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>