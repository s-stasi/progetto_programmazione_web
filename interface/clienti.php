<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="corpo-pagina">
  <div class="table-header">
    <h2>Client Management</h2>
    <button class="btn-primary" onclick="openAddModal()">+ Add New Client</button>
  </div>

  <div id="addClientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>New Client</h3>
        <span onclick="closeAddModal()" style="cursor:pointer; font-weight:bold;">&times;</span>
      </div>
      <form id="addClientForm" onsubmit="saveClient(event)">
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="firstName" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="lastName" required>
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="dob">
        </div>
        <button type="submit" class="btn-primary" style="width:100%">Save Client</button>
      </form>
    </div>
  </div>

  <div id="editClientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit Client</h3>
        <span onclick="closeEditModal()" style="cursor:pointer; font-weight:bold;">&times;</span>
      </div>
      <form id="editClientForm" onsubmit="updateClient(event)">
        <input type="hidden" id="editClientId" name="clientId">
        
        <div class="form-group">
          <label>First Name</label>
          <input type="text" id="editFirstName" name="firstName" required>
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" id="editLastName" name="lastName" required>
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" id="editDob" name="dob">
        </div>
        <button type="submit" class="btn-edit" style="width:100%">Update Client</button>
      </form>
    </div>
  </div>

  <div class="table-container">
    <table class="gestionale-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Birth Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php
        require_once('../php/config.php');
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if (!$conn->connect_error) {
          $sql = "SELECT codice, nome, cognome, dataNascita FROM Cliente ORDER BY codice DESC LIMIT 50";
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
                        <button class='btn-edit' onclick='openEditModal({$id}, \"{$nome}\", \"{$cognome}\", \"{$dataNascita}\")'>Edit</button>
                        <button class='btn-delete' onclick='deleteClient({$id})'>Delete</button>
                      </td>
                    </tr>";
            }
          }
          $conn->close();
        }
        ?>
      </tbody>
    </table>
  </div>
</main>

<script>
  // --- ADD MODAL LOGIC ---
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

  // --- EDIT MODAL LOGIC ---
  const editModal = document.getElementById('editClientModal');
  
  function openEditModal(id, nome, cognome, dataNascita) {
    // Populate the form fields
    document.getElementById('editClientId').value = id;
    document.getElementById('editFirstName').value = nome;
    document.getElementById('editLastName').value = cognome;
    document.getElementById('editDob').value = dataNascita;
    
    // Show the modal
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

  // --- DELETE LOGIC ---
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