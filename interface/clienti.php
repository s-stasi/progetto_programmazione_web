<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2>Gestione clienti</h2>
    <button class="btn-primary" onclick="openAddModal()">+ Aggiungi clienti</button>
  </div>

  <div id="addClientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Nuovo Cliente</h3>
        <span onclick="closeAddModal()" style="cursor:pointer; font-weight:bold;">&times;</span>
      </div>
      <form id="addClientForm" onsubmit="saveClient(event)">
        <div class="form-group">
          <label>Nome</label>
          <input type="text" name="firstName" required>
        </div>
        <div class="form-group">
          <label>Cognome</label>
          <input type="text" name="lastName" required>
        </div>
        <div class="form-group">
          <label>Data di Nascita</label>
          <input type="date" name="dob">
        </div>
        <button type="submit" class="btn-primary" style="width:100%">Salva Cliente</button>
      </form>
    </div>
  </div>

  <div id="editClientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Modifica Cliente</h3>
        <span onclick="closeEditModal()" style="cursor:pointer; font-weight:bold;">&times;</span>
      </div>
      <form id="editClientForm" onsubmit="updateClient(event)">
        <input type="hidden" id="editClientId" name="clientId">
        
        <div class="form-group">
          <label>Nome</label>
          <input type="text" id="editFirstName" name="firstName" required>
        </div>
        <div class="form-group">
          <label>Cognome</label>
          <input type="text" id="editLastName" name="lastName" required>
        </div>
        <div class="form-group">
          <label>Data di Nascita</label>
          <input type="date" id="editDob" name="dob">
        </div>
        <button type="submit" class="btn-edit" style="width:100%">Aggiorna Cliente</button>
      </form>
    </div>
        </div>

    <div id="contractsModal" class="modal">
    <div class="modal-ios" style="max-width: 550px;">
      
      <div class="ios-header">
        <div class="ios-umbrella-info">
          <span class="txt-oro-main">Contratti</span>
          <span class="txt-oro-sub" style="margin-bottom: 0;">Storico del cliente</span>
        </div>
        <span class="close-modal" onclick="closeContractsModal()">&times;</span>
      </div>

      <hr class="ios-divider">

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
      
      <button class="btn-ios-primary" onclick="closeContractsModal()" style="margin-top: 20px;">
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
      // Se stiamo già ordinando per questa colonna in ASC, il prossimo click la metterà in DESC
      $params['dir'] = ($currentSort === $column && $currentDir === 'ASC') ? 'desc' : 'asc';
      return '?' . http_build_query($params);
  }

  function getSortIcon($column, $currentSort, $currentDir) {
      if ($currentSort !== $column) return ' <span style="color:#ccc; font-size:0.7em;">&#9660;</span>'; 
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
          $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
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
            while($row = $result->fetch_assoc()) {
              $id = $row['codice'];
              $nome = htmlspecialchars($row['nome'] ?? '');
              $cognome = htmlspecialchars($row['cognome'] ?? '');
              $dataNascita = $row['dataNascita'] ?? '';
              $dataFormattata = $dataNascita ? date('d/m/Y', strtotime($dataNascita)) : '-';
              $email = htmlspecialchars($row['email'] ?? '-');
              $telefono = htmlspecialchars($row['telefono'] ??'-');
              
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
                      
                        <button class='btn-edit' onclick='openEditModal({$id}, \"{$nome}\", \"{$cognome}\", \"{$dataNascita}\")'>
                          <span class='material-symbols-outlined' style='font-size: 18px;'>edit</span>
                        </button>
                        <button class='btn-delete' onclick='deleteClient({$id})'>
                          <span class='material-symbols-outlined' style='font-size: 18px;'>delete</span>
                        </button>
                      </td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='6' style='text-align: center; padding: 20px;'>No clients found.</td></tr>";
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
        echo "<a href='{$prevUrl}' class='{$prevClass}'>&laquo; Previous</a>";
        
        echo "<span class='current-page'>Page {$page} of {$totalPages}</span>";
        
        $queryParams['page'] = $page + 1;
        $nextUrl = '?' . http_build_query($queryParams);
        $nextClass = ($page >= $totalPages) ? 'disabled' : '';
        echo "<a href='{$nextUrl}' class='{$nextClass}'>Next &raquo;</a>";
        ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<script>
  const addModal = document.getElementById('addClientModal');
  function openAddModal() { addModal.style.display = 'block'; }
  function closeAddModal() { addModal.style.display = 'none'; }

  async function saveClient(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
      const response = await fetch('../php/add_client.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert("Error: " + result.message); }
    } catch (e) { alert("Technical error"); }
  }

  const editModal = document.getElementById('editClientModal');
  
  function openEditModal(id, nome, cognome, dataNascita) {
    document.getElementById('editClientId').value = id;
    document.getElementById('editFirstName').value = nome;
    document.getElementById('editLastName').value = cognome;
    document.getElementById('editDob').value = dataNascita;
    
    editModal.style.display = 'block';
  }
  
  function closeEditModal() { editModal.style.display = 'none'; }

  async function updateClient(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    try {
      const response = await fetch('../php/update_client.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert("Error: " + result.message); }
    } catch (e) { alert("Technical error"); }
  }

  async function deleteClient(id) {
    if (!confirm(`Delete client #${id}?`)) return;
    try {
      const response = await fetch(`../php/delete_client.php?id=${id}`);
      const result = await response.json();
      if (result.success) { location.reload(); } else { alert(result.message); }
    } catch (e) { alert("Technical error"); }
  }
  const contractsModal = document.getElementById('contractsModal');
  
  function closeContractsModal() { 
    contractsModal.style.display = 'none'; 
  }

  async function viewContracts(clientId) {
    const tbody = document.getElementById('contractsTableBody');
    // Mostriamo un testo di caricamento temporaneo
    tbody.innerHTML = '<tr><td colspan="3" style="text-align: center; padding: 15px;">Caricamento in corso...</td></tr>';
    contractsModal.style.display = 'block';

    try {
      const response = await fetch(`../php/get_contratti_cliente.php?id=${clientId}`);
      const result = await response.json();
      
      tbody.innerHTML = ''; // Svuotiamo il testo di caricamento

      if (result.success && result.contracts.length > 0) {
        result.contracts.forEach(c => {
          // Formattiamo la data nel formato leggibile italiano gg/mm/aaaa
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