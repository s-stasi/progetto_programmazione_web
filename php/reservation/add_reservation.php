<?php
header("Content-Type: application/json");
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parametri estratti dal form italiano di popup.php
    $id_umbrella  = $_POST['id_ombrellone'] ?? null;
    $first_name   = trim($_POST['nome'] ?? '');
    $last_name    = trim($_POST['cognome'] ?? '');
    $birth_date   = $_POST['data_nascita'] ?? null;
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['cellulare'] ?? '');
    $home_address = trim($_POST['indirizzo'] ?? '');
    $date_start   = $_POST['data_inizio'] ?? null;
    $date_end     = $_POST['data_fine'] ?? null;
    $prezzo_tot   = $_POST['prezzo_totale'] ?? 30.00;

    if (!$id_umbrella || !$first_name || !$last_name || !$date_start || !$date_end) {
        echo json_encode(["success" => false, "message" => "Dati obbligatori mancanti per la prenotazione."]);
        exit;
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        echo json_encode(["success" => false, "message" => "Connessione fallita."]);
        exit;
    }

    try {
        $conn->begin_transaction();

        // 1. Controllo se il cliente esiste già per email
        $id_client = null;
        if (!empty($email)) {
            $stmt_check = $conn->prepare("SELECT codice FROM Cliente WHERE email = ? LIMIT 1");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $res_check = $stmt_check->get_result()->fetch_assoc();
            if ($res_check) {
                $id_client = $res_check['codice'];
            }
            $stmt_check->close();
        }

        // Se non esiste, lo creiamo al volo
        if (!$id_client) {
            $stmt_insert = $conn->prepare("INSERT INTO Cliente (nome, cognome, dataNascita, email, telefono, indirizzo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt_insert->bind_param("ssssss", $first_name, $last_name, $birth_date, $email, $phone, $home_address);
            $stmt_insert->execute();
            $id_client = $conn->insert_id;
            $stmt_insert->close();
        }

        // 2. Creazione del Contratto
        $today_date = date('Y-m-d');
        $stmt_contratto = $conn->prepare("INSERT INTO Contratto (data, importo, stipulatoDa) VALUES (?, ?, ?)");
        $stmt_contratto->bind_param("sdi", $today_date, $prezzo_tot, $id_client);
        $stmt_contratto->execute();
        $num_contract = $conn->insert_id;
        $stmt_contratto->close();

        // 3. Occupazione giornaliera dell'ombrellone (OmbrelloneVenduto)
        $current_timestamp = strtotime($date_start);
        $end_timestamp = strtotime($date_end);

        $stmt_vendita = $conn->prepare("INSERT INTO OmbrelloneVenduto (data, idOmbrellone, idContratto) VALUES (?, ?, ?)");

        while ($current_timestamp <= $end_timestamp) {
            $day_date = date('Y-m-d', $current_timestamp);
            $stmt_vendita->bind_param("sii", $day_date, $id_umbrella, $num_contract);
            $stmt_vendita->execute();
            $current_timestamp = strtotime("+1 day", $current_timestamp);
        }
        $stmt_vendita->close();

        // COMPROMESSO PER IL TERMINALE: Stampiamo il log della prenotazione avvenuta
        error_log("=================================================");
        error_log("SUCCESS: Prenotazione effettuata con successo!");
        error_log("Ombrellone ID: " . $id_umbrella);
        error_log("Periodo: Dal " . $date_start . " Al " . $date_end);
        error_log("Cliente: " . $first_name . " " . $last_name);
        error_log("=================================================");

        $conn->commit();
        $conn->close();

        echo json_encode(["success" => true, "message" => "Prenotazione salvata con successo!"]);
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
        exit;
    }
}
?>