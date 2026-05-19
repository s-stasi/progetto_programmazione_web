<?php
// php/add_reservation.php
require_once('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aligned with the English 'name' attributes from index.php modal form
    $id_umbrella  = $_POST['id_umbrella'] ?? null;
    $first_name   = trim($_POST['first_name'] ?? '');
    $last_name    = trim($_POST['last_name'] ?? '');
    $birth_date   = $_POST['birth_date'] ?? null;
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $home_address = trim($_POST['home_address'] ?? '');
    $date_start   = $_POST['date_start'] ?? null;
    $date_end     = $_POST['date_end'] ?? null;

    if (!$id_umbrella || !$first_name || !$last_name || !$birth_date || !$email || !$phone || !$home_address || !$date_start || !$date_end) {
        die("Error: All customer details and booking dates are mandatory.");
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    try {
        $conn->begin_transaction();

        // 1. Check if client already exists by email (indirizzo column in DB)
        $sql_check = "SELECT codice FROM Cliente WHERE indirizzo = ? LIMIT 1"; 
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        $existing_client = $result_check->fetch_assoc();
        $stmt_check->close();

        if ($existing_client) {
            $id_client = $existing_client['codice'];
        } else {
            // Insert new client if it doesn't exist
            $sql_insert_cliente = "INSERT INTO Cliente (nome, cognome, dataNascita, indirizzo) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert_cliente);
            $stmt_insert->bind_param("ssss", $first_name, $last_name, $birth_date, $email);
            $stmt_insert->execute();
            $id_client = $conn->insert_id;
            $stmt_insert->close();
        }

        // 2. Create Contract record
        $today_date = date('Y-m-d');
        $fictional_price = 30.00; 
        
        $sql_contratto = "INSERT INTO Contratto (data, importo, stipulatoDa) VALUES (?, ?, ?)";
        $stmt_contratto = $conn->prepare($sql_contratto);
        $stmt_contratto->bind_param("sdi", $today_date, $fictional_price, $id_client);
        $stmt_contratto->execute();
        $num_contract = $conn->insert_id;
        $stmt_contratto->close();

        // 3. Populate OmbrelloneVenduto row day by day
        $current_timestamp = strtotime($date_start);
        $end_timestamp = strtotime($date_end);

        $sql_vendita = "INSERT INTO OmbrelloneVenduto (data, idOmbrellone, idContratto) VALUES (?, ?, ?)";
        $stmt_vendita = $conn->prepare($sql_vendita);

        while ($current_timestamp <= $end_timestamp) {
            $day_date = date('Y-m-d', $current_timestamp);
            $stmt_vendita->bind_param("sii", $day_date, $id_umbrella, $num_contract);
            $stmt_vendita->execute();
            $current_timestamp = strtotime("+1 day", $current_timestamp);
        }
        $stmt_vendita->close();

        $conn->commit();
        $conn->close();

        header("Location: ../interface/index.php?status=success");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $conn->close();
        die("Error during reservation storage: " . $e->getMessage());
    }
}
?>