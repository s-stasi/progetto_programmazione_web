// Modal management
const addModal = document.getElementById('addClientModal');
const editModal = document.getElementById('edit-modal-client');
const contractsModal = document.getElementById('contractsModal');

function openAddModal() { addModal.classList.add('show'); }
function closeAddModal() { addModal.classList.remove('show'); }
function closeEditModal() { editModal.classList.remove('show'); }
function closeContractsModal() { contractsModal.classList.remove('show'); }

function openEditModal(id, nome, cognome, dataNascita, email, telefono) {
  document.getElementById('editClientId').value = id;
  document.getElementById('edit-nome').value = nome;
  document.getElementById('edit-cognome').value = cognome;
  document.getElementById('edit-data-nascita').value = dataNascita && dataNascita !== 'null' ? dataNascita : '';
  document.getElementById('edit-email').value = email === '-' ? '' : email;
  document.getElementById('edit-cellulare').value = telefono === '-' ? '' : telefono;
  
  if (document.getElementById('edit-indirizzo')) {
    document.getElementById('edit-indirizzo').value = '';
  }
  editModal.classList.add('show');
}

// INSERT a new client
async function saveClient(event) {
  event.preventDefault();
  const form = document.getElementById('addClientForm');
  const formData = new FormData(form);

  try {
    const response = await fetch('/lido/api/clienti?action=add', {
      method: 'POST',
      body: formData
    });
    const result = await response.json();
    
    if (result.success) {
      alert(result.message);
      closeAddModal();
      location.reload();
    } else {
      alert("Errore: " + result.message);
    }
  } catch (error) {
    alert("Impossibile connettersi al server.");
  }
}

// UPDATE an existing client
async function updateClient(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  
  try {
    const response = await fetch('/lido/api/clienti?action=update', { 
      method: 'POST', 
      body: formData 
    });
    const result = await response.json();
    
    if (result.success) { 
      location.reload(); 
    } else { 
      alert("Errore: " + result.message); 
    }
  } catch (e) { 
    alert("Errore tecnico"); 
  }
}

// DELETE a client
async function deleteClient(clientId) {
  if (!confirm("Sei sicuro di voler eliminare definitivamente questo cliente?")) return;

  try {
    // Moved to POST for security instead of the old GET
    const response = await fetch(`/lido/api/clienti?action=delete&id=${clientId}`, {
      method: 'POST'
    });
    const result = await response.json();

    if (result.success) {
      alert(result.message);
      location.reload();
    } else {
      alert("Errore durante l'eliminazione: " + result.message);
    }
  } catch (error) {
    alert("Impossibile connettersi al server.");
  }
}

// VIEW Contracts (To be implemented later)
async function viewContracts(clientId) {
  alert("La Servlet dei contratti è in arrivo!");
}