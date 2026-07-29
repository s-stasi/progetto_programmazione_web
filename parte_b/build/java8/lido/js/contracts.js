// Delete contract using existing Reservation API
async function deleteContract(id) {
  if (!confirm(`Sei sicuro di voler cancellare il contratto #${id}?`)) return;

  try {
    const response = await fetch(`/lido/api/prenotazioni?id=${id}`, { method: 'DELETE' });
    const result = await response.json();

    if (result.success) {
      location.reload();
    } else {
      alert("ERROR: " + result.message);
    }
  } catch (e) {
    alert("Network error.");
  }
}

// Global tracking variables
let editCurrentUmbrellaId = null;
let editCurrentTipologia = null;
let editCurrentContractId = null;

// Open modal reading data from dataset attributes
function openEditContractModal(button) {
  const dataset = button.dataset;
  editCurrentContractId = dataset.id;
  editCurrentUmbrellaId = dataset.ombrellone;
  editCurrentTipologia = dataset.tipo;

  document.getElementById('display-contract-code').innerText = "#" + dataset.id;
  document.getElementById('contract-id').value = dataset.ombrellone;
  
  document.getElementById('booking-start').value = dataset.inizio;
  document.getElementById('booking-end').value = dataset.fine;
  document.getElementById('display-total-cost').innerText = parseFloat(dataset.costo).toFixed(2);
  document.getElementById('contract-total-cost-hidden').value = dataset.costo;

  document.getElementById('contract-nome').value = dataset.nome;
  document.getElementById('contract-cognome').value = dataset.cognome;

  document.getElementById('modal-contract').classList.add('show');
}

function closeContractModal() {
  document.getElementById('modal-contract').classList.remove('show');
}

// Check availability and price via Java APIs
async function handleContractDateChange() {
  if (!editCurrentUmbrellaId) return;

  const startInput = document.getElementById('booking-start');
  const endInput = document.getElementById('booking-end');
  const priceDisplay = document.getElementById('display-total-cost');
  const btnConferma = document.querySelector('#wrapper-creation-actions button[type="submit"]');

  const newStart = startInput.value;
  let newEnd = endInput.value;

  if (newStart > newEnd) {
    endInput.value = newStart;
    newEnd = newStart;
  }

  priceDisplay.innerText = "Calcolo...";

  try {
    const availUrl = `/lido/api/ombrelloni?inizio=${newStart}&fine=${newEnd}&escludi_contratto=${editCurrentContractId}`;
    const availResponse = await fetch(availUrl);
    const allUmbrellas = await availResponse.json();
    
    const currentUmbData = allUmbrellas.find(item => item.id_ombrellone == editCurrentUmbrellaId);

    if (currentUmbData && currentUmbData.occupato === 1) {
      if (btnConferma) { btnConferma.disabled = true; btnConferma.hidden = true; }
      priceDisplay.innerText = "Non Disp.";
      priceDisplay.style.color = "#e74c3c";
      return; 
    }

    if (btnConferma) { btnConferma.disabled = false; btnConferma.hidden = false; }
    priceDisplay.style.color = "inherit";

    const priceUrl = `/lido/api/tariffe?tipo=${encodeURIComponent(editCurrentTipologia)}&inizio=${newStart}&fine=${newEnd}`;
    const priceResponse = await fetch(priceUrl);
    const priceData = await priceResponse.json();

    if (priceData.success) {
      priceDisplay.innerText = parseFloat(priceData.totale).toFixed(2);
      document.getElementById('contract-total-cost-hidden').value = priceData.totale;
    } else {
      priceDisplay.innerText = "Err.";
    }
  } catch (err) {
    priceDisplay.innerText = "Err.";
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const startInput = document.getElementById('booking-start');
  const endInput = document.getElementById('booking-end');
  if (startInput && endInput) {
    startInput.addEventListener('change', handleContractDateChange);
    endInput.addEventListener('change', handleContractDateChange);
  }
});

// Save updated contract via new Contratto API
async function saveReservation(event) {
  event.preventDefault();

  if (!editCurrentContractId) return;

  const form = document.getElementById('form-new-reservation');
  const formData = new FormData(form);
  formData.append('id_contratto', editCurrentContractId);

  const btnSubmit = document.querySelector('#wrapper-creation-actions button[type="submit"]');
  const originalText = btnSubmit.innerText;
  btnSubmit.innerText = "Saving...";
  btnSubmit.disabled = true;

  try {
    const response = await fetch('/lido/api/contratti?action=update', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();
    if (result.success) {
      location.reload(); 
    } else {
      alert("Error: " + result.message);
      btnSubmit.innerText = originalText;
      btnSubmit.disabled = false;
    }
  } catch (err) {
    alert("Network error.");
    btnSubmit.innerText = originalText;
    btnSubmit.disabled = false;
  }
}