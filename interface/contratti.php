<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2>Registro Contratti / Prenotazioni</h2>
  </div>

  <?php
  require_once('../php/config.php');
  $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

  if ($conn->connect_error) {
    echo "<div style='color:red; padding: 20px;'>Connessione fallita: " . $conn->connect_error . "</div>";
  } else {
    
    // 1. Query di controllo automatica per scadere i vecchi contratti attivi
    $sql_scadenza = "UPDATE Contratto c
                     JOIN OmbrelloneVenduto ov ON c.numProgr = ov.idContratto
                     SET c.stato = 'Scaduto'
                     WHERE c.stato = 'Attivo' AND ov.data < CURDATE()";
    $conn->query($sql_scadenza);

    // 2. Query principale: prendiamo lo storico recente dei contratti
    $sql = "SELECT c.numProgr, c.data, c.importo, c.stato, cl.nome, cl.cognome 
            FROM Contratto c
            JOIN Cliente cl ON c.stipulatoDa = cl.codice
            ORDER BY c.numProgr DESC 
            LIMIT 100";
            
    $result = $conn->query($sql);

    // Array per separare i record in base allo stato
    $contratti_attivi = [];
    $contratti_storico = [];

    if ($result && $result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
        if (($row['stato'] ?? 'Attivo') === 'Attivo') {
          $contratti_attivi[] = $row;
        } else {
          $contratti_storico[] = $row;
        }
      }
    }

    // Funzione interna helper per stampare le singole righe di tabella
    function printContrattoRow($row) {
      $num = $row['numProgr'];
      $data = !empty($row['data']) ? date('d/m/Y', strtotime($row['data'])) : '-';
      $cliente = htmlspecialchars(($row['nome'] ?? '') . ' ' . ($row['cognome'] ?? ''));
      $importo = number_format((float)($row['importo'] ?? 0), 2, ',', '.');
      
      $stato = $row['stato'] ?? 'Attivo';
      $badgeClass = 'badge-attivo';
      if ($stato === 'Cancellato') $badgeClass = 'badge-cancellato';
      if ($stato === 'Scaduto') $badgeClass = 'badge-scaduto';

      echo "<tr>";
      echo "<td><strong>#{$num}</strong></td>";
      echo "<td>{$data}</td>";
      echo "<td>{$cliente}</td>";
      echo "<td><span class='badge-stato {$badgeClass}'>{$stato}</span></td>";
      echo "<td class='text-right'><strong>€ {$importo}</strong></td>";
      echo "<td class='text-center'>
              <button class='btn-edit' title='Dettagli / Modifica'>
                <span class='material-symbols-outlined' style='font-size: 18px;'>edit</span>
              </button>
              <button class='btn-delete' onclick='deleteContract({$num})' title='Annulla Contratto' " . ($stato !== 'Attivo' ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : '') . ">
                <span class='material-symbols-outlined' style='font-size: 18px;'>delete</span>
              </button>
            </td>";
      echo "</tr>";
    }
    ?>

    <div class="sezione-titolo">🔥 Prenotazioni Attive</div>
    <div class="table-container">
      <table class="gestionale-table">
        <thead>
          <tr>
            <th>Num. Contratto</th>
            <th>Data Stipula</th>
            <th>Cliente</th>
            <th>Stato</th>
            <th class="text-right">Importo</th>
            <th class="text-center">Azioni</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          if (!empty($contratti_attivi)) {
            foreach ($contratti_attivi as $c) {
              printContrattoRow($c);
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Nessuna prenotazione attiva al momento.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($contratti_storico)): ?>
      <details class="storico-details">
        <summary class="storico-summary">📁 Visualizza Storico Contratti (Scaduti / Cancellati)</summary>
        <div class="storico-content">
          <div class="table-container" style="box-shadow: none; margin-top: 0;">
            <table class="gestionale-table">
              <thead>
                <tr>
                  <th>Num. Contratto</th>
                  <th>Data Stipula</th>
                  <th>Cliente</th>
                  <th>Stato</th>
                  <th class="text-right">Importo</th>
                  <th class="text-center">Azioni</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                foreach ($contratti_storico as $c) {
                  printContrattoRow($c);
                }
                ?>
              </tbody>
            </table>
          </div>
        </div>
      </details>
    <?php endif; ?>

    <?php
    $conn->close();
  }
  ?>
</main>

<script>
  async function deleteContract(id) {
    if (!confirm(`Sei sicuro di voler annullare e contrassegnare come CANCELLATO il contratto #${id}? l'ombrellone verrà liberato.`)) {
      return;
    }
    
    try {
      const response = await fetch(`../php/reservation/delete_reservation.php?id_contratto=${id}`);
      location.reload();
    } catch (e) {
      alert("Errore tecnico durante la cancellazione.");
      console.error(e);
    }
  }
</script>

<?php include 'components/footer.php'; ?>