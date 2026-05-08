<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Per permettere le chiamate dal tuo PC locale

// Connessione al database
$servername = "localhost";
$username = "tuo_username";
$password = "tuo_password";
$dbname = "my_treuominiemezzo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connessione fallita"]));
}

// Recupero parametri GET
$tipo = $_GET['tipo'] ?? 'Base';
$inizio = $_GET['inizio'] ?? date('Y-m-d');
$fine = $_GET['fine'] ?? date('Y-m-d');

// 1. Calcolo numero di giorni
$date1 = new DateTime($inizio);
$date2 = new DateTime($fine);
$diff = $date1->diff($date2);
$giorni = $diff->days + 1;

// 2. Determiniamo quale scaglione di numMinGiorni cercare nel DB
// Basato sul tuo script: D=1, MID=8, LONG=21
$scaglione = 1;
if ($giorni >= 21) {
    $scaglione = 21;
} elseif ($giorni >= 8) {
    $scaglione = 8;
}

// 3. Query per trovare la tariffa unitaria corretta
// Cerchiamo la tariffa che copre la data di inizio e ha il giusto scaglione di giorni
$sql = "SELECT t.prezzo 
        FROM tariffa t
        JOIN tipologia_tariffa tt ON t.codice = tt.codTariffa
        WHERE tt.codTipologia = ? 
        AND ? BETWEEN t.dataInizio AND t.dataFine
        AND t.numMinGiorni = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $tipo, $inizio, $scaglione);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $prezzoUnitarioScontato = $row['prezzo'];
    $totale = $prezzoUnitarioScontato * $giorni;
    
    echo json_encode([
        "giorni" => $giorni,
        "prezzo_unitario" => $prezzoUnitarioScontato,
        "totale" => number_format($totale, 2, '.', ''),
        "scaglione" => $scaglione
    ]);
} else {
    echo json_encode(["error" => "Tariffa non trovata"]);
}

$conn->close();
?>