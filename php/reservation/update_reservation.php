<?php
header("Content-Type: application/json");
require_once('../config.php');

// Recupero parametri dal FormData
$id_contratto  = $_POST['id_contratto'] ?? null;
$id_ombrellone = $_POST['id_ombrellone'] ?? null;
$data_inizio   = $_POST['data_inizio'] ?? null;
$data_fine     = $_POST['data_fine'] ?? null;
$prezzo_totale = $_POST['prezzo_totale'] ?? null;

// Validazione base
if (!$id_contratto || !$id_ombrellone || !$data_inizio || !$data_fine || $prezzo_totale === null) {
    echo json_encode(["success" => false, "message" => "Dati mancanti dal form."]);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connessione fallita."]);
    exit;
}

// Iniziamo una transazione sicura
$conn->begin_transaction();

try {
    // 1. Aggiorna il prezzo totale nella tabella Contratto
    $stmt_contratto = $conn->prepare("UPDATE Contratto SET importo = ? WHERE numProgr = ?");
    $stmt_contratto->bind_param("di", $prezzo_totale, $id_contratto);
    $stmt_contratto->execute();
    $stmt_contratto->close();

    // 2. Elimina i vecchi giorni associati a questo contratto in OmbrelloneVenduto
    $stmt_delete = $conn->prepare("DELETE FROM OmbrelloneVenduto WHERE contratto = ?");
    $stmt_delete->bind_param("i", $id_contratto);
    $stmt_delete->execute();
    $stmt_delete->close();

    // 3. Genera l'intervallo di date tra inizio e fine
    $start = new DateTime($data_inizio);
    $end = new DateTime($data_fine);
    $end->modify('+1 day'); // Necessario perché DatePeriod esclude l'ultimo giorno per default
    
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);

    // Prepariamo gli statement per l'inserimento
    // Usiamo IGNORE su GiornoDisponibilita per evitare errori se la data esiste già
    $stmt_giorno = $conn->prepare("INSERT IGNORE INTO GiornoDisponibilita (idOmbrellone, data) VALUES (?, ?)");
    $stmt_venduto = $conn->prepare("INSERT INTO OmbrelloneVenduto (idOmbrellone, data, contratto) VALUES (?, ?, ?)");

    foreach ($period as $dt) {
        $data_corrente = $dt->format('Y-m-d');
        
        // Assicuriamoci che l'accoppiata ombrellone-giorno esista nella tabella padre (GiornoDisponibilita)
        $stmt_giorno->bind_param("is", $id_ombrellone, $data_corrente);
        $stmt_giorno->execute();

        // Inseriamo fisicamente la riga di vendita
        $stmt_venduto->bind_param("isi", $id_ombrellone, $data_corrente, $id_contratto);
        $stmt_venduto->execute();
    }

    $stmt_giorno->close();
    $stmt_venduto->close();

    // Se arriviamo qui senza crash, confermiamo tutte le modifiche sul DB!
    $conn->commit();
    echo json_encode(["success" => true, "message" => "Contratto modificato e salvato con successo!"]);

} catch (mysqli_sql_exception $e) {
    // In caso di errore SQL, annulla tutte le query precedenti
    $conn->rollback();
    
    // Errore 1062 = Duplicate entry (Tentativo di prenotare un ombrellone già occupato in quel giorno)
    if ($e->getCode() == 1062) {
        echo json_encode(["success" => false, "message" => "Errore: l'ombrellone risulta già occupato in una delle date selezionate."]);
    } else {
        echo json_encode(["success" => false, "message" => "Errore database: " . $e->getMessage()]);
    }
} catch (Exception $e) {
    // Gestione di altri errori imprevisti
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Errore tecnico: " . $e->getMessage()]);
}

$conn->close();
?>