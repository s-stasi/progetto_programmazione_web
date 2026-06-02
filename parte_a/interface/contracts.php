<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2 class="ricerca-titolo">Gestione Contratti</h2>
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
          $recordsPerPage = 50;
          $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;

          if ($page < 1)
            $page = 1;

          $offset = ($page - 1) * $recordsPerPage;

          $sql = "SELECT 
                    c.numProgr, c.data, c.importo, cl.nome, cl.cognome,
                    o.id AS idOmbrellone,
                    t.nome AS tipologia,
                    MIN(ov.data) AS inizio,
                    MAX(ov.data) AS fine
                  FROM Contratto c
                  JOIN Cliente cl ON c.stipulatoDa = cl.codice
                  LEFT JOIN OmbrelloneVenduto ov ON c.numProgr = ov.contratto
                  LEFT JOIN Ombrellone o ON ov.idOmbrellone = o.id
                  LEFT JOIN Tipologia t ON o.tipologia = t.codice
                  WHERE 1=1 ";

          $da = $_GET['data_da'] ?? '';
          $a = $_GET['data_a'] ?? '';

          $whereClause = "";

          if (!empty($da)) {
            $safeDa = $conn->real_escape_string($da);
            $whereClause .= " AND c.data >= '{$safeDa}'";
          }
          if (!empty($a)) {
            $safeA = $conn->real_escape_string($a);
            $whereClause .= " AND c.data <= '{$safeA}'";
          }

          $countSql = "SELECT COUNT(DISTINCT c.numProgr) as total FROM Contratto c $whereClause";
          $countResult = $conn->query($countSql);
          $totalRecords = $countResult->fetch_assoc()['total'];
          $totalPages = ceil($totalRecords / $recordsPerPage);

          $sql .= $whereClause;

          $sql .= " GROUP BY c.numProgr, c.data, c.importo, cl.nome, cl.cognome, o.id, t.nome";
          $sql .= " ORDER BY c.data, c.numProgr LIMIT {$recordsPerPage} OFFSET {$offset}";
          
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

              // Prepariamo i parametri JavaScript in modo sicuro (evitando conflitti di apici)
              $js_numProgr = intval($row['numProgr']);
              $js_idOmbrellone = intval($row['idOmbrellone']);
              $js_tipologia = addslashes($row['tipologia'] ?? 'Base');
              $js_inizio = $row['inizio'] ?? '';
              $js_fine = $row['fine'] ?? '';
              $js_costo = $row['importo'];
              $js_nome = addslashes($row['nome']);
              $js_cognome = addslashes($row['cognome']);

              // Costruiamo la stringa della funzione JS con virgole e apici singoli corretti
              $onClickFunc = sprintf(
                "editContract(%d, %d, '%s', '%s', '%s', '%s', '%s', '%s')", 
                $js_numProgr, $js_idOmbrellone, $js_tipologia, $js_inizio, $js_fine, $js_costo, $js_nome, $js_cognome
              );
              
              // Action buttons
              echo "<td>
                      <button class='btn-edit' onclick=\"$onClickFunc\">
                        <span class='material-symbols-outlined' style='font-size: 18px;'>edit</span>
                      </button>
                      <button class='btn-delete' onclick='deleteContract(" . $js_numProgr . ")'>
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

    <?php if (isset($totalPages) && $totalPages > 1): ?>
      <div class="pagination">
        <?php
        $queryParams = $_GET;
        $queryParams['page'] = $page - 1;
        $prevUrl = '?' . http_build_query($queryParams);
        $prevClass = ($page <= 1) ? 'disabled' : '';
        echo "<a href='{$prevUrl}' class='{$prevClass}'>&laquo; Precedente</a>";

        echo "<span class='current-page'>Pagina {$page} di {$totalPages}</span>";

        $queryParams['page'] = $page + 1;
        $nextUrl = '?' . http_build_query($queryParams);
        $nextClass = ($page >= $totalPages) ? 'disabled' : '';
        echo "<a href='{$nextUrl}' class='{$nextClass}'>Successiva &raquo;</a>";
        ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<script src="javascript/contracts.js"></script>

<?php include 'contract_form.php'; ?>
<?php include 'components/footer.php'; ?>
