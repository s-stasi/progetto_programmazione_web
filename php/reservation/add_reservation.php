<?php
header("Content-Type: application/json");
require_once('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve parameters from form
  $id_umbrella  = $_POST['id_ombrellone'] ?? null;
  $first_name   = trim($_POST['nome'] ?? '');
  $last_name    = trim($_POST['cognome'] ?? '');
  $birth_date   = !empty($_POST['data_nascita']) ? $_POST['data_nascita'] : null;
  $email        = trim($_POST['email'] ?? '');
  $phone        = trim($_POST['cellulare'] ?? '');
  $home_address = trim($_POST['indirizzo'] ?? '');
  $date_start   = $_POST['data_inizio'] ?? null;
  $date_end     = $_POST['data_fine'] ?? null;
  $prezzo_tot   = $_POST['prezzo_totale'] ?? null;

  if (!$id_umbrella || !$first_name || !$last_name || !$date_start || !$date_end || $prezzo_tot === null) {
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

    // 1. Check if client already exists by email (Your smart logic)
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

    // If client doesn't exist, create it
    if (!$id_client) {
      $stmt_insert = $conn->prepare("INSERT INTO Cliente (nome, cognome, dataNascita, email, telefono, indirizzo) VALUES (?, ?, ?, ?, ?, ?)");
      $stmt_insert->bind_param("ssssss", $first_name, $last_name, $birth_date, $email, $phone, $home_address);
      $stmt_insert->execute();
      $id_client = $conn->insert_id;
      $stmt_insert->close();
    }

    // 2. Create the Contract
    $today_date = date('Y-m-d');
    $stmt_contract = $conn->prepare("INSERT INTO Contratto (data, importo, stipulatoDa) VALUES (?, ?, ?)");
    $stmt_contract->bind_param("sdi", $today_date, $prezzo_tot, $id_client);
    $stmt_contract->execute();
    $num_contract = $conn->insert_id;
    $stmt_contract->close();

    // 3. Generate daily availability and bookings
    $start = new DateTime($date_start);
    $end   = new DateTime($date_end);
    $end->modify('+1 day'); // Include the last day
    
    $interval = new DateInterval('P1D');
    $period   = new DatePeriod($start, $interval, $end);

    // Statements for the two bridge tables
    $stmt_day = $conn->prepare("INSERT IGNORE INTO GiornoDisponibilita (idOmbrellone, data) VALUES (?, ?)");
    $stmt_sold = $conn->prepare("INSERT INTO OmbrelloneVenduto (idOmbrellone, data, contratto) VALUES (?, ?, ?)");

    foreach ($period as $dt) {
      $day_date = $dt->format('Y-m-d');
      
      // A. Ensure day exists to avoid Foreign Key errors
      $stmt_day->bind_param("is", $id_umbrella, $day_date);
      $stmt_day->execute();

      // B. Insert the booking (using correct column names)
      $stmt_sold->bind_param("isi", $id_umbrella, $day_date, $num_contract);
      $stmt_sold->execute();
    }

    $stmt_day->close();
    $stmt_sold->close();

    // Console logging
    error_log("=================================================");
    error_log("SUCCESS: Prenotazione effettuata con successo!");
    error_log("Ombrellone ID: " . $id_umbrella);
    error_log("Periodo: Dal " . $date_start . " Al " . $date_end);
    error_log("Cliente: " . $first_name . " " . $last_name);
    error_log("=================================================");

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Prenotazione salvata con successo!"]);

  } catch (mysqli_sql_exception $e) {
    $conn->rollback();
    if ($e->getCode() == 1062) {
      echo json_encode(["success" => false, "message" => "Errore: ombrellone già occupato nelle date selezionate."]);
    } else {
      echo json_encode(["success" => false, "message" => "Errore DB: " . $e->getMessage()]);
    }
  } catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Errore di sistema: " . $e->getMessage()]);
  }

  $conn->close();
}
?>