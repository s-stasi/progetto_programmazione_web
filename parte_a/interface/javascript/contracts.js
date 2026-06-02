async function deleteContract(id) {
  if (!confirm(`Sei sicuro di voler cancellare il contratto #${id}? Questo libererà gli ombrelloni associati.`)) {
    return;
  }

  try {
    const response = await fetch(`../php/delete_contract.php?id=${id}`);
    const result = await response.json();

    if (result.success) {
      alert(result.message);
      location.reload();
    } else {
      alert("ERROR: " + result.message);
    }
  } catch (e) {
    alert("Errore tecnico durante la cancellazione del contratto");
    console.error(e);
  }
}

// Variabili globali per tenere traccia di cosa stiamo modificando
let editCurrentUmbrellaId = null;
let editCurrentTipologia = null;
let editCurrentContractId = null;

// Modifica la funzione in modo che riceva i dati da gestione_contratti.php
function editContract(numProgr, idOmbrellone, tipologia, inizio, fine, costo, nome, cognome) {
  editCurrentContractId = numProgr;
  editCurrentUmbrellaId = idOmbrellone;
  editCurrentTipologia = tipologia;

  document.getElementById('display-contract-code').innerText = "#" + numProgr;
  document.getElementById('contract-id').value = idOmbrellone;
  
  document.getElementById('booking-start').value = inizio;
  document.getElementById('booking-end').value = fine;
  document.getElementById('display-total-cost').innerText = costo;

  // Inietta nome e cognome (che ora sono readonly)
  document.getElementById('contract-nome').value = nome;
  document.getElementById('contract-cognome').value = cognome;

  const modal = document.getElementById('modal-contract');
  modal.classList.add('show');
}

// Funzione gemella a quella della mappa, ma con la clausola di esclusione!
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
    // 1. Controllo disponibilità passando il contratto da escludere
    const availUrl = `/php/umbrella/get_umbrellas.php?inizio=${newStart}&fine=${newEnd}&escludi_contratto=${editCurrentContractId}`;
    const availResponse = await fetch(availUrl);
    const allUmbrellas = await availResponse.json();

    const currentUmbData = allUmbrellas.find(item => item.id_ombrellone == editCurrentUmbrellaId);

    if (currentUmbData && currentUmbData.occupato == 1) {
      if (btnConferma) btnConferma.disabled = true; // Blocca il salvataggio
      if (btnConferma) btnConferma.hidden = true; // Blocca il salvataggio
      priceDisplay.innerText = "Non Disponibile";
      priceDisplay.style.color = "#e74c3c";
      return; 
    } else {
      if (btnConferma) btnConferma.disabled = false; // Sblocca
      if (btnConferma) btnConferma.hidden = false; // Blocca il salvataggio
      priceDisplay.style.color = "inherit";
    }

    // 2. Ricalcolo del prezzo aggiornato
    const priceUrl = `/php/umbrella/get_price.php?tipo=${encodeURIComponent(editCurrentTipologia)}&inizio=${newStart}&fine=${newEnd}`;
    const priceResponse = await fetch(priceUrl);
    const priceData = await priceResponse.json();

    if (!priceData.error) {
      priceDisplay.innerText = priceData.totale;
      // Aggiorna anche l'input nascosto per l'invio del form
      document.getElementById('contract-total-cost-hidden').value = priceData.totale;
    } else {
      priceDisplay.innerText = "Errore tariffa";
    }
  } catch (err) {
    console.error('Errore aggiornamento popup:', err);
    priceDisplay.innerText = "Errore rete";
  }
}

// Aggancia gli eventi al caricamento
document.addEventListener('DOMContentLoaded', () => {
  const startInput = document.getElementById('booking-start');
  const endInput = document.getElementById('booking-end');
  if (startInput && endInput) {
    startInput.addEventListener('change', handleContractDateChange);
    endInput.addEventListener('change', handleContractDateChange);
  }
});

function closeContractModal() {
  document.getElementById('modal-contract').classList.remove('show');
}

async function saveReservation(event) {
  event.preventDefault();

  const start = document.getElementById('booking-start').value;
  const end = document.getElementById('booking-end').value;
  const umbrellaId = document.getElementById('contract-id').value;
  const totalCost = document.getElementById('contract-total-cost-hidden').value;
  const btnSubmit = document.querySelector('#wrapper-creation-actions button[type="submit"]');

  // Prevent submitting if no contract ID is loaded in the global variable
  if (!editCurrentContractId) {
    alert("Error: Missing contract ID.");
    return;
  }

  // Prepare the payload
  const formData = new FormData();
  formData.append('id_contratto', editCurrentContractId);
  formData.append('id_ombrellone', umbrellaId);
  formData.append('data_inizio', start);
  formData.append('data_fine', end);
  formData.append('prezzo_totale', totalCost);

  // Disable button to prevent double submissions
  const originalText = btnSubmit.innerText;
  btnSubmit.innerText = "Saving...";
  btnSubmit.disabled = true;

  try {
    const response = await fetch('/php/reservation/update_reservation.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert("Contract updated successfully!");
      // Reload the page to reflect the updated dates and prices in the table
      location.reload(); 
    } else {
      alert("Error: " + (result.message || "Unknown error occurred"));
      btnSubmit.innerText = originalText;
      btnSubmit.disabled = false;
    }
  } catch (err) {
    console.error("Network error:", err);
    alert("Failed to connect to the server.");
    btnSubmit.innerText = originalText;
    btnSubmit.disabled = false;
  }
}
