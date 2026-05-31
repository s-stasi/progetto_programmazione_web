<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2>Gestione clienti</h2>
    <button class="btn-primary" onclick="openAddModal()">+ Aggiungi clienti</button>
  </div>

  <div id="addClientModal" class="modal">
    <div class="modal-ios">
      <div class="ios-header">
        <div class="ios-umbrella-info">
          <h2 class="txt-oro-main">Nuovo Cliente</h2>
          <span class="txt-tipologia-small txt-grigio-medium">Inserisci i dati anagrafici</span>
        </div>
        <span class="close-modal" onclick="closeAddModal()">&times;</span>
      </div>

      <hr class="ios-divider">

      <form id="addClientForm" onsubmit="saveClient(event)">
        <?php
        $prefix = 'add';
        include 'components/client_form.php';
        ?>
        <div id="wrapper-creation-actions">
          <button type="submit" class="btn-ios-primary">Aggiungi cliente</button>
        </div>
      </form>
    </div>
  </div>

  <div id="edit-modal-client" class="modal">
    <div class="modal-ios">
      <div class="ios-header">
        <div class="ios-umbrella-info">
          <h2 class="txt-oro-main">Modifica Cliente</h2>
          <span class="txt-tipologia-small txt-grigio-medium">Aggiorna i dati del profilo</span>
        </div>
        <span class="close-modal" onclick="closeEditModal()">&times;</span>
      </div>

      <hr class="ios-divider">

      <form id="editClientForm" onsubmit="updateClient(event)">
        <input type="hidden" id="editClientId" name="clientId">
        <?php
        $prefix = 'edit';
        include 'components/client_form.php';
        ?>
        <div id="wrapper-creation-actions">
          <button type="submit" class="btn-ios-primary">Conferma Modifica</button>
        </div>
      </form>
    </div>
  </div>

  <div id="contractsModal" class="modal">
    <div class="modal-ios">
      <div class="ios-header">
        <div class="ios-umbrella-info">
          <h2 class="txt-oro-main">Contratti</h2>
          <span class="txt-tipologia-small txt-grigio-medium">Storico del cliente</span>
        </div>
        <span class="close-modal" onclick="closeContractsModal()">&times;</span>
      </div>

      <hr class="ios-divider">

      <div>
        <table class="gestionale-table">
          <thead>
            <tr>
              <th class="text-left">Numero</th>
              <th class="text-left">Data Stipula</th>
              <th class="text-right">Importo</th>
            </tr>
          </thead>
          <tbody id="contractsTableBody">
          </tbody>
        </table>
      </div>

      <button type="button" class="btn-ios-primary" onclick="closeContractsModal()">Chiudi</button>
    </div>
  </div>

  <?php
  $sortColumn = $_GET['sort'] ?? 'codice';
  $sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'DESC' : 'ASC';

  function getSortLink($column, $currentSort, $currentDir)
  {
    $params = $_GET;
    $params['sort'] = $column;
    $params['dir'] = ($currentSort === $column && $currentDir === 'ASC') ? 'desc' : 'asc';
    return '?' . http_build_query($params);
  }

  function getSortIcon($column, $currentSort, $currentDir)
  {
    if ($currentSort !== $column)
      return ' <span class="sort-icon-muted">&#9660;</span>';
    return $currentDir === 'ASC' ? ' <span>&#9650;</span>' : ' <span>&#9660;</span>';
  }
  ?>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>
            <a href="<?php echo getSortLink('codice', $sortColumn, $sortDirection); ?>">
              ID<?php echo getSortIcon('codice', $sortColumn, $sortDirection); ?>
            </a>
          </th>
          <th>
            <a href="<?php echo getSortLink('nome', $sortColumn, $sortDirection); ?>">
              Nome<?php echo getSortIcon('nome', $sortColumn, $sortDirection); ?>
            </a>
          </th>
          <th>
            <a href="<?php echo getSortLink('cognome', $sortColumn, $sortDirection); ?>">
              Cognome<?php echo getSortIcon('cognome', $sortColumn, $sortDirection); ?>
            </a>
          </th>
          <th>Data di Nascita</th>
          <th>Contatti</th>
          <th>Azioni</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once('../php/config.php');
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if (!$conn->connect_error) {
          $recordsPerPage = 50;
          $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
          if ($page < 1)
            $page = 1;

          $offset = ($page - 1) * $recordsPerPage;

          $whereClause = "WHERE 1=1";
          $searchCognome = $_GET['search_cognome'] ?? '';
          if (!empty($searchCognome)) {
            $safeSearch = $conn->real_escape_string($searchCognome);
            $whereClause .= " AND c.cognome LIKE '%{$safeSearch}%'";
          }

          $searchNome = $_GET['search_nome'] ?? '';
          if (!empty($searchNome)) {
            $safeSearch = $conn->real_escape_string($searchNome);
            $whereClause .= " AND c.nome LIKE '%{$safeSearch}%'";
          }

          $annoNascita = $_GET['anno_nascita'] ?? '';
          if (!empty($annoNascita) && is_numeric($annoNascita)) {
            $safeAnno = $conn->real_escape_string($annoNascita);
            $whereClause .= " AND YEAR(c.dataNascita) = '{$safeAnno}'";
          }

          $searchEmail = $_GET['search_email'] ?? '';
          if (!empty($searchEmail)) {
            $safeEmail = $conn->real_escape_string($searchEmail);
            $whereClause .= " AND c.email LIKE '%{$safeEmail}%'";
          }

          $searchTelefono = $_GET['search_telefono'] ?? '';
          if (!empty($searchTelefono)) {
            $safeTelefono = $conn->real_escape_string($searchTelefono);
            $whereClause .= " AND c.telefono LIKE '%{$safeTelefono}%'";
          }

          $countSql = "SELECT COUNT(DISTINCT c.codice) as total FROM Cliente c $whereClause";
          $countResult = $conn->query($countSql);
          $totalRecords = $countResult->fetch_assoc()['total'];
          $totalPages = ceil($totalRecords / $recordsPerPage);

          $allowedSortColumns = ['codice', 'nome', 'cognome', 'dataNascita'];
          $safeSortColumn = in_array($sortColumn, $allowedSortColumns) ? "c." . $sortColumn : 'c.codice';

          $sql = "SELECT c.codice, c.nome, c.cognome, c.dataNascita, c.email, c.telefono,
                         COUNT(con.numProgr) as num_contratti
                  FROM Cliente c
                  LEFT JOIN Contratto con ON c.codice = con.stipulatoDa
                  $whereClause 
                  GROUP BY c.codice
                  ORDER BY $safeSortColumn $sortDirection 
                  LIMIT $recordsPerPage OFFSET $offset";

          $result = $conn->query($sql);

          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $id = $row['codice'];
              $nome = htmlspecialchars($row['nome'] ?? '');
              $cognome = htmlspecialchars($row['cognome'] ?? '');
              $dataNascita = $row['dataNascita'] ?? '';
              $dataFormattata = $dataNascita ? date('d/m/Y', strtotime($dataNascita)) : '-';
              $email = htmlspecialchars($row['email'] ?? '-');
              $telefono = htmlspecialchars($row['telefono'] ?? '-');

              // Verifica se nascondere il pulsante blu dei contratti
              $numContratti = (int)($row['num_contratti'] ?? 0);
              $stileBottoneContratti = ($numContratti === 0) ? "style='visibility: hidden;'" : "";

              echo "<tr>
                      <td>{$id}</td>
                      <td>{$nome}</td>
                      <td>{$cognome}</td>
                      <td>{$dataFormattata}</td>
                      <td>
                        <span class='client-contact-email'>{$email}</span>
                        <span class='client-contact-phone'>Tel: {$telefono}</span>
                      </td>
                      <td>
                        <button type='button' class='btn-info-contracts' onclick='viewContracts({$id})' title='Vedi Contratti' {$stileBottoneContratti}>
                          <span class='material-symbols-outlined'>description</span>
                        </button>
                      
                        <button type='button' class='btn-edit' onclick='openEditModal({$id}, \"{$nome}\", \"{$cognome}\", \"{$dataNascita}\", \"{$email}\", \"{$telefono}\")' title='Modifica'>
                          <span class='material-symbols-outlined'>edit</span>
                        </button>
                        <button type='button' class='btn-delete' onclick='deleteClient({$id})' title='Elimina'>
                          <span class='material-symbols-outlined'>delete</span>
                        </button>
                      </td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='6' class='text-center' style='padding: 20px;'>Nessun cliente trovato.</td></tr>";
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

<!-- include script -->
<script src="javascript/clienti.js"></script>

<?php include 'components/footer.php'; ?>
