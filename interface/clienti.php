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

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Cognome</th>
          <th>Data di Nascita</th>
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

          $data_nascita = $_GET['data_nascita'] ?? '';
          if (!empty($data_nascita)) {
            $safe_data_nascita = $conn->real_escape_string($data_nascita);
            $whereClause .= " AND dataNascita = '{$safe_data_nascita}'";
          }

          $countSql = "SELECT COUNT(*) as total FROM Cliente $whereClause";
          $countResult = $conn->query($countSql);
          $totalRecords = $countResult->fetch_assoc()['total'];
          $totalPages = ceil($totalRecords / $recordsPerPage);

          $sql = "SELECT codice, nome, cognome, dataNascita 
                  FROM Cliente 
                  $whereClause 
                  ORDER BY codice 
                  LIMIT $recordsPerPage OFFSET $offset";
                  
          $result = $conn->query($sql);
          
          if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
              $id = $row['codice'];
              $nome = htmlspecialchars($row['nome'] ?? '');
              $cognome = htmlspecialchars($row['cognome'] ?? '');
              $dataNascita = $row['dataNascita'] ?? '';
              $dataFormattata = $dataNascita ? date('d/m/Y', strtotime($dataNascita)) : '-';
              
              echo "<tr>
                      <td>{$id}</td>
                      <td>{$nome}</td>
                      <td>{$cognome}</td>
                      <td>{$dataFormattata}</td>
                      <td>
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
            echo "<tr><td colspan='5' style='text-align: center; padding: 20px;'>No clients found.</td></tr>";
          }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
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
</script>

<?php include 'components/footer.php'; ?>