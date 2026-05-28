<?php
// php/umbrella/get_price.php
require_once('../config.php');

$tipo   = $_GET['tipo'] ?? '';
$inizio = $_GET['inizio'] ?? '';
$fine   = $_GET['fine'] ?? '';

if (!$tipo || !$inizio || !$fine) {
    echo json_encode(["error" => "Parametri insufficienti per il calcolo del prezzo."]);
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["error" => "Connessione database fallita."]);
    exit();
}

// Calcolo giorni totali della prenotazione
$giorni = (strtotime($fine) - strtotime($inizio)) / 86400 + 1;
if ($giorni <= 0) $giorni = 1;

// Estrae la tariffa base della tipologia
$sql = "SELECT prezzoChiaro FROM Tipologia WHERE nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $tipo);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$prezzo_base = $res['prezzoChiaro'] ?? 20.00;
$stmt->close();

// Calcolo preliminare basato sul costo fisso giornaliero
$totale = $prezzo_base * $giorni;

$conn->close();

header('Content-Type: application/json');
echo json_encode([
    "giorni" => $giorni,
    "prezzo_giornaliero" => $prezzo_base,
    "totale" => number_format($totale, 2, '.', '')
]);