<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2>Gestione Contratti</h2>
    <button class="btn-primary" onclick="alert('Proceed to Operational Map to create a new contract')">Nuova Prenotazione</button>
  </div>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>Prog. #</th>
          <th>Data</th>
          <th>Cliente</th>
          <th>Totale</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once('../php/config.php');
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
          echo "<tr><td colspan='5' style='color:red;'>Database connection failed: " . $conn->connect_error . "</td></tr>";
        } else {
          $sql = "SELECT c.numProgr, c.data, c.importo, cl.nome, cl.cognome 
                  FROM Contratto c
                  JOIN Cliente cl ON c.stipulatoDa = cl.codice
                  WHERE 1=1";

          $da = $_GET['data_da'] ?? '';
          $a = $_GET['data_a'] ?? '';

          if (!empty($da)) {
            $safeDa = $conn->real_escape_string($da);
            $sql .= " AND c.data >= '{$safeDa}'";
          }
          if (!empty($a)) {
            $safeA = $conn->real_escape_string($a);
            $sql .= " AND c.data <= '{$safeA}'";
          }

          $sql .= " ORDER BY c.data, c.numProgr LIMIT 50";
          
          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              echo "<tr>";
              echo "<td>#" . htmlspecialchars($row['numProgr'] ?? '') . "</td>";
              
              // Format contract date
              $date = !empty($row['data']) ? date('d/m/Y', strtotime($row['data'])) : '-';
              echo "<td>" . htmlspecialchars($date) . "</td>";
              
              // Combine Name and Surname
              $clientName = ($row['nome'] ?? '') . " " . ($row['cognome'] ?? '');
              echo "<td>" . htmlspecialchars($clientName) . "</td>";
              
              // Format currency
              $amountValue = $row['importo'] ?? 0;
              $amount = number_format((float)$amountValue, 2, ',', '.');
              echo "<td><strong>€ " . htmlspecialchars($amount) . "</strong></td>";
              
              // Action buttons
              echo "<td>
                      <button class='btn-edit'>
                        <span class='material-symbols-outlined' style='font-size: 18px;'>edit</span>
                      </button>
                      <button class='btn-delete' onclick='deleteContract(" . ($row['numProgr'] ?? 0) . ")'>
                        <span class='material-symbols-outlined' style='font-size: 18px;'>delete</span>
                      </button>
                    </td>";
              echo "</tr>";
            }
          } else {
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>Nessun contratto trovato nel database.</td></tr>";
          }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<script src="javascript/contratti.js"></script>

<?php include 'components/footer.php'; ?>