const addModal = document.getElementById('addClientModal');
function openAddModal() { addModal.classList.add('show'); }
function closeAddModal() { addModal.classList.remove('show'); }

async function saveEditClient(event) {
  event.preventDefault();

  const form = document.getElementById('editClientForm');
  const formData = new FormData(form);

  try {
    const response = await fetch('../php/client/update_client.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      closeEditModal();
      location.reload();
    } else {
      alert("Errore durante la modifica: " + result.message);
    }
  } catch (error) {
    console.error("Errore di rete:", error);
    alert("Impossibile connettersi al server per salvare le modifiche.");
  }
}

const editModal = document.getElementById('edit-modal-client');

function openEditModal(id, nome, cognome, dataNascita, email, telefono) {
  document.getElementById('editClientId').value = id;

  document.getElementById('edit-nome').value = nome;
  document.getElementById('edit-cognome').value = cognome;
  document.getElementById('edit-data-nascita').value = dataNascita;
  document.getElementById('edit-email').value = email === '-' ? '' : email;
  document.getElementById('edit-cellulare').value = telefono === '-' ? '' : telefono;

  if (document.getElementById('edit-indirizzo')) {
    document.getElementById('edit-indirizzo').value = '';
  }

  editModal.classList.add('show');
}

function closeEditModal() { editModal.classList.remove('show'); }

async function updateClient(event) {
  event.preventDefault();
  const formData = new FormData(event.target);
  try {
    const response = await fetch('../php/client/update_client.php', { method: 'POST', body: formData });
    const result = await response.json();
    if (result.success) { location.reload(); } else { alert("Errore: " + result.message); }
  } catch (e) { alert("Errore tecnico"); }
}

async function deleteClient(clientId) {
  if (!confirm("Sei sicuro di voler eliminare definitivamente questo cliente?")) {
    return;
  }

  try {
    const response = await fetch(`../php/client/delete_client.php?id=${clientId}`, {
      method: 'GET'
    });

    const result = await response.json();

    if (result.success) {
      alert(result.message);
      location.reload();
    } else {
      alert("Errore durante l'eliminazione: " + result.message);
    }
  } catch (error) {
    console.error("Errore di rete:", error);
    alert("Impossibile connettersi al server per eliminare il cliente.");
  }
}

const contractsModal = document.getElementById('contractsModal');
function closeContractsModal() { contractsModal.classList.remove('show'); }

async function viewContracts(clientId) {
  const tbody = document.getElementById('contractsTableBody');
  tbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 15px;">Caricamento in corso...</td></tr>';
  contractsModal.classList.add('show');

  try {
    const response = await fetch(`../php/get_customer_contracts.php?id=${clientId}`);
    const result = await response.json();
    tbody.innerHTML = '';

    if (result.success && result.contracts.length > 0) {
      result.contracts.forEach(c => {
        const dateObj = new Date(c.data);
        const dataFormattata = isNaN(dateObj) ? c.data : dateObj.toLocaleDateString('it-IT');

        const row = `<tr>
          <td><strong>#${c.numProgr}</strong></td>
          <td>${dataFormattata}</td>
          <td class="text-right importo-valore">${parseFloat(c.importo).toFixed(2)} €</td>
        </tr>`;
        tbody.innerHTML += row;
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 15px; color: #666;">Nessun contratto stipulato da questo cliente.</td></tr>';
    }
  } catch (e) {
    tbody.innerHTML = '<tr><td colspan="3" class="text-center" style="padding: 15px; color: red;">Errore nel caricamento dei dati.</td></tr>';
  }
}