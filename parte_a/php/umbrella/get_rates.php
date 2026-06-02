<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
require_once('../config.php');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["success" => false, "message" => "Connessione fallita"]));
}

$tipo = $_GET['tipo'] ?? 'Base';
$inizio = $_GET['inizio'] ?? date('Y-m-d');
$fine = $_GET['fine'] ?? date('Y-m-d');

$date1 = new DateTime($inizio);
$date2 = new DateTime($fine);

if ($date1 > $date2) {
    echo json_encode(["success" => false, "message" => "Date non valide"]);
    $conn->close();
    exit;
}

$diff = $date1->diff($date2);
$giorni = $diff->days + 1;

// Identificazione dello scaglione commerciale e sconto associato
$sconto_label = "0%";
if ($giorni > 20) {
    $tipo_tariffa_db = 'Abbonamento';
    $scaglione = 21;
    $sconto_label = "15%"; // Sconto lungo del generator
} elseif ($giorni >= 8) {
    $tipo_tariffa_db = 'Abbonamento';
    $scaglione = 8;
    $sconto_label = "10%"; // Sconto medio
} elseif ($giorni > 1) {
    $tipo_tariffa_db = 'Abbonamento';
    $scaglione = 1;
    $sconto_label = "7%";  // Sconto breve
} else {
    $tipo_tariffa_db = 'Giornaliera';
    $scaglione = null; 
    $sconto_label = "Prezzo Pieno";
}

if ($scaglione === null) {
    $sql = "SELECT t.prezzo FROM Tariffa t
            JOIN TipologiaTariffa tt ON t.id = tt.codTariffa
            WHERE LOWER(tt.codTipologia) = LOWER(?) AND ? BETWEEN t.dataInizio AND t.dataFine
            AND t.tipo = ? AND t.numMinGiorni IS NULL LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $tipo, $inizio, $tipo_tariffa_db);
} else {
    $sql = "SELECT t.prezzo FROM Tariffa t
            JOIN TipologiaTariffa tt ON t.id = tt.codTariffa
            WHERE LOWER(tt.codTipologia) = LOWER(?) AND ? BETWEEN t.dataInizio AND t.dataFine
            AND t.tipo = ? AND t.numMinGiorni = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $tipo, $inizio, $tipo_tariffa_db, $scaglione);
}

$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $prezzoUnitarioScontato = $row['prezzo'];
    $totale = $prezzoUnitarioScontato * $giorni;
    
    // Per lo scontrino recuperiamo il prezzo intero stimato prima dello sconto
    // Dividendo il prezzo scontato per il rispettivo moltiplicatore di abbonamento
    $prezzoLordoUnitario = $prezzoUnitarioScontato;
    if ($scaglione === 21) $prezzoLordoUnitario = $prezzoUnitarioScontato / 0.85;
    if ($scaglione === 8)  $prezzoLordoUnitario = $prezzoUnitarioScontato / 0.90;
    if ($scaglione === 1)  $prezzoLordoUnitario = $prezzoUnitarioScontato / 0.93;

    echo json_encode([
        "success" => true,
        "giorni" => $giorni,
        "prezzo_unitario" => number_format($prezzoUnitarioScontato, 2, '.', ''),
        "prezzo_lordo_unitario" => number_format($prezzoLordoUnitario, 2, '.', ''),
        "totale" => number_format($totale, 2, '.', ''),
        "sconto" => $sconto_label,
        "modalita" => $tipo_tariffa_db
    ]);
} else {
    echo json_encode(["success" => false, "totale" => "0.00"]);
}

$stmt->close();
$conn->close();
?>