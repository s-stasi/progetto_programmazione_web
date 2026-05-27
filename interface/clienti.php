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
        <div id="wrapper-creation-actions" style="margin-top: 20px;">
          <button type="submit" class="btn-ios-primary" style="width:100%">Salva Cliente</button>
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
        <div id="wrapper-creation-actions" style="margin-top: 20px;">
          <button type="submit" class="btn-ios-primary" style="width:100%">Conferma Modifica</button>
        </div>
      </form>
    </div>
  </div>

  <div id="contractsModal" class="modal">
    <div class="modal-ios" style="max-width: 550px;">
      <div class="ios-header">
        <div class="ios-umbrella-info">
          <h2 class="txt-oro-main">Contratti</h2>
          <span class="txt-tipologia-small txt-grigio-medium">Storico del cliente</span>
        </div>
        <span class="close-modal" onclick="closeContractsModal()">&times;</span>
      </div>

      <hr class="ios-divider" style="margin-bottom: 15px;">

      <div>
        <table class="gestionale-table" style="width: 100%; margin-top: 0;">
          <thead>
            <tr>
              <th style="text-align: left;">Numero</th>
              <th style="text-align: left;">Data Stipula</th>
              <th style="text-align: right;">Importo</th>
            </tr>
          </thead>
          <tbody id="contractsTableBody">
          </tbody>
        </table>
      </div>

      <button class="btn-ios-primary" onclick="closeContractsModal()" style="margin-top: 20px; width: 100%;">
        Chiudi
      </button>
    </div>
  </div>

  <?php
  $sortColumn = $_GET['sort'] ?? 'codice';
  $sortDirection = isset($_GET['dir']) && strtolower($_GET['dir']) === 'desc' ? 'DESC' : 'ASC';

  function getSortLink($column, $currentSort, $currentDir) {
    $params = $_GET;
    $params['sort'] = $column;
    $params['dir'] = ($currentSort === $column && $currentDir === 'ASC') ? 'desc' : 'asc';
    return '?' . http_build_query($params);
  }

  function getSortIcon($column, $currentSort, $currentDir) {
    if ($currentSort !== $column)
      return ' <span style="color:#ccc; font-size:0.7em;">&#9660;</span>';
    return $currentDir === 'ASC' ? ' <span style="font-size:0.7em;">&#9650;</span>' : ' <span style="font-size:0.7em;">&#9660;</span>';
  }
  ?>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>
            <a href="<?php echo getSortLink('codice', $sortColumn, $sortDirection); ?>" style="color: inherit; text-decoration: none;">
              ID<?php echo getSortIcon('codice', $sortColumn, $sortDirection); ?>
            </a>
          </th>
          <th>
            <a href="<?php echo getSortLink('nome', $sortColumn, $sortDirection); ?>" style="color: inherit; text-decoration: none;">
              Nome<?php echo getSortIcon('nome', $sortColumn, $sortDirection); ?>
            </a>
          </th>
          <th>
            <a href="<?php echo getSortLink('cognome', $sortColumn, $sortDirection); ?>" style="color: inherit; text-decoration: none;">
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
          if ($page < 1) $page = 1;

          $offset = ($page - 1) * $recordsPerPage;

          $whereClause = "WHERE 1=1";
          $searchCognome = $_GET['search_cognome'] ?? '';
          if (!empty($searchCognome)) {
            $safeSearch = $conn->real_escape_string($searchCognome);
            $whereClause .= " AND cognome LIKE '%{$safeSearch}%'";
          }

          $searchNome = $_GET['search_nome'] ?? '';
          if (!empty($searchNome)) {
            $safeSearch = $conn->real_escape_string($searchNome);
            $whereClause .= " AND nome LIKE '%{$safeSearch}%'";
          }

          $annoNascita = $_GET['anno_nascita'] ?? '';
          if (!empty($annoNascita) && is_numeric($annoNascita)) {
            $safeAnno = $conn->real_escape_string($annoNascita);
            $whereClause .= " AND YEAR(dataNascita) = '{$safeAnno}'";
          }

          $searchEmail = $_GET['search_email'] ?? '';
          if (!empty($searchEmail)) {
            $safeEmail = $conn->real_escape_string($searchEmail);
            $whereClause .= " AND email LIKE '%{$safeEmail}%'";
          }

          $searchTelefono = $_GET['search_telefono'] ?? '';
          if (!empty($searchTelefono)) {
            $safeTelefono = $conn->real_escape_string($searchTelefono);
            $whereClause .= " AND telefono LIKE '%{$safeTelefono}%'";
          }

          $countSql = "SELECT COUNT(*) as total FROM Cliente $whereClause";
          $countResult = $conn->query($countSql);
          $totalRecords = $countResult->fetch_assoc()['total'];
          $totalPages = ceil($totalRecords / $recordsPerPage);

          $allowedSortColumns = ['codice', 'nome', 'cognome', 'dataNascita'];
          $safeSortColumn = in_array($sortColumn, $allowedSortColumns) ? $sortColumn : 'codice';

          $sql = "SELECT codice, nome, cognome, dataNascita, email, telefono 
                  FROM Cliente 
                  $whereClause 
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

              echo "<tr>
                      <td>{$id}</td>
                      <td>{$nome}</td>
                      <td>{$cognome}</td>
                      <td>{$dataFormattata}</td>
                      <td>
                        <span style='display: block; font-weight: 500;'>{$email}</span>
                        <span style='display: block; color: #666; font-size: 0.85rem; margin-top: 2px;'>Tel: {$telefono}</span>
                      </td>
                      <td>
                        <button type='button' style='background-color: #0288d1; color: white; border: none; padding: 5px; border-radius: 4px; cursor: pointer; margin-right: 4px;' onclick='viewContracts({$id})' title='Vedi Contratti'>
                          <span class='material-symbols-outlined' style='font-size: 18px;'>description</span>
                        </button>
                      
                        <button class='btn-edit' onclick='openEditModal({$id}, \"{$nome}\", \"{$cognome}\", \"{$dataNascita}\", \"{$email}\", \"{$telefono}\")'>
                          <span class='material-symbols-outlined' style='font-size: 18px;'>edit</span>
                        </button>
                        <button class='btn-delete' onclick='deleteClient({$id})'>
                          <span class='material-symbols-outlined' style='font-size: 18px;'>delete</span>
                        </button>
                      </td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>Nessun cliente trovato.</td></tr>";
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

<script>
  const addModal = document.getElementById('addClientModal');
  function openAddModal() { addModal.classList.add('show'); }
  function closeAddModal() { addModal.classList.remove('show'); }

  async function saveClient(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
      const response = await fetch('../php/add_client.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert("Errore: " + result.message); }
    } catch (e) { alert("Errore tecnico"); }
  }

  const editModal = document.getElementById('edit-modal-client');

  function openEditModal(id, nome, cognome, dataNascita, email, telefono) {
    document.getElementById('editClientId').value = id;
    
    // Assegnazione pulita tramite prefisso dei componenti riutilizzabili
    document.getElementById('edit-nome').value = nome;
    document.getElementById('edit-cognome').value = cognome;
    document.getElementById('edit-data-nascita').value = dataNascita;
    document.getElementById('edit-email').value = email === '-' ? '' : email;
    document.getElementById('edit-cellulare').value = telefono === '-' ? '' : telefono;
    
    if(document.getElementById('edit-indirizzo')) {
        document.getElementById('edit-indirizzo').value = ''; 
    }

    editModal.classList.add('show');
  }

  function closeEditModal() { editModal.classList.remove('show'); }

  async function updateClient(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
      const response = await fetch('../php/update_client.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert("Errore: " + result.message); }
    } catch (e) { alert("Errore tecnico"); }
  }

  async function deleteClient(id) {
    if (!confirm(`Vuoi eliminare il cliente #${id}?`)) return;
    try {
      const response = await fetch(`../php/delete_client.php?id=${id}`);
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert(result.message); }
    } catch (e) { alert("Errore tecnico"); }
  }
  
  const contractsModal = document.getElementById('contractsModal');
  function closeContractsModal() { contractsModal.classList.remove('show'); }

  async function viewContracts(clientId) {
    const tbody = document.getElementById('contractsTableBody');
    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 15px;">Caricamento in corso...</td></tr>';
    contractsModal.classList.add('show');

    try {
      const response = await fetch(`../php/get_contratti_cliente.php?id=${clientId}`);
      const result = await response.json();
      tbody.innerHTML = '';

      if (result.success && result.contracts.length > 0) {
        result.contracts.forEach(c => {
          const dateObj = new Date(c.data);
          const dataFormattata = isNaN(dateObj) ? c.data : dateObj.toLocaleDateString('it-IT');

          const row = `<tr>
            <td><strong>#${c.numProgr}</strong></td>
            <td>${dataFormattata}</td>
            <td style="font-weight: 600; color: #2e7d32;">${parseFloat(c.importo).toFixed(2)} €</td>
          </tr>`;
          tbody.innerHTML += row;
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 15px; color: #666;">Nessun contratto stipulato da questo cliente.</td></tr>';
      }
    } catch (e) {
      tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 15px; color: red;">Errore nel caricamento dei dati.</td></tr>';
    }
  }
</script>

<?php include 'components/footer.php'; ?>