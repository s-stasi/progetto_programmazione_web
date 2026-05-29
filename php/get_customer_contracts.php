<?php
header('Content-Type: application/json');
require_once('config.php'); 

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connessione al database fallita']);
    exit;
}

$id_ombrellone = $_GET['id_ombrellone'] ?? null;
$data_selezionata = $_GET['data'] ?? null;

if ($id_ombrellone && $data_selezionata) {
    $sql = "SELECT 
                con.numProgr AS id_contratto,
                con.importo AS costo_totale,
                cli.nome, 
                cli.cognome, 
                cli.dataNascita AS data_nascita, 
                cli.email, 
                cli.telefono AS cellulare, 
                cli.indirizzo,
                (SELECT MIN(data) FROM OmbrelloneVenduto WHERE idContratto = con.numProgr) AS data_inizio,
                (SELECT MAX(data) FROM OmbrelloneVenduto WHERE idContratto = con.numProgr) AS data_fine
            FROM OmbrelloneVenduto ov
            JOIN Contratto con ON ov.idContratto = con.numProgr
            JOIN Cliente cli ON con.stipulatoDa = cli.codice
            WHERE ov.idOmbrellone = ? AND ov.data = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("is", $id_ombrellone, $data_selezionata);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result) {
            echo json_encode(['success' => true, 'data' => $result]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nessuna prenotazione trovata per questa data.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore query dettagli']);
    }
    $conn->close();
    exit;
}

$clientId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($clientId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID Cliente non valido']);
    exit;
}

$sql = "SELECT numProgr, data, importo FROM Contratto WHERE stipulatoDa = ? ORDER BY data DESC";
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