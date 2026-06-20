let popupCurrentUmbrella = null;
let popupCurrentType = null;
let popupBasePrice = null;

function openReservationModal(code, type, baseCost, isReserved, reservationData) {
  popupCurrentUmbrella = reservationData;
  popupCurrentType = type;
  popupBasePrice = baseCost;

  const modal = document.getElementById('modal-reservation');
  const form = document.getElementById('form-new-reservation');
  form.reset();

  document.getElementById('display-umbrella-code').textContent = 'Ombrellone ' + code;
  document.getElementById('display-umbrella-type').textContent = type;
  document.getElementById('display-total-cost').textContent = baseCost;
  document.getElementById('booking-total-cost-hidden').value = baseCost;

  const creationActions = document.getElementById('wrapper-creation-actions');
  const viewActions = document.getElementById('wrapper-view-actions');

  if (isReserved && reservationData && reservationData.data) {
    creationActions.style.display = 'none';
    viewActions.style.display = 'flex';

    document.getElementById('display-total-cost').textContent = reservationData.data.prezzo_totale;
    document.getElementById('booking-id').value = reservationData.data.id || '';
    document.getElementById('booking-start').value = reservationData.data.data_inizio || '';
    document.getElementById('booking-end').value = reservationData.data.data_fine || '';
    document.getElementById('client-nome').value = reservationData.data.nome || '';
    document.getElementById('client-cognome').value = reservationData.data.cognome || '';
    document.getElementById('client-data-nascita').value = reservationData.data.data_nascita || '';
    document.getElementById('client-indirizzo').value = reservationData.data.indirizzo || '';
    document.getElementById('client-email').value = reservationData.data.email || '';
    document.getElementById('client-cellulare').value = reservationData.data.cellulare || '';

    toggleFormInputs(true);
  } else {
    creationActions.style.display = 'block';
    viewActions.style.display = 'none';
    document.getElementById('booking-id').value = '';

    const umbrellaId = reservationData
      ? (reservationData.id_ombrellone || reservationData.id || '')
      : '';
    document.getElementById('booking-umbrella-id').value = umbrellaId;

    if (document.getElementById('start-date')) {
      document.getElementById('booking-start').value = document.getElementById('start-date').value;
      document.getElementById('booking-end').value = document.getElementById('end-date').value;
    }

    toggleFormInputs(false);
  }

  modal.classList.add('show');
}

function toggleFormInputs(isDisabled) {
  const form = document.getElementById('form-new-reservation');
  const inputs = form.querySelectorAll('input');
  inputs.forEach(input => {
    if (input.id !== 'booking-id') {
      input.disabled = isDisabled;
    }
  });
}

function enableModificationMode() {
  toggleFormInputs(false);
  document.getElementById('wrapper-creation-actions').style.display = 'block';
  document.getElementById('wrapper-creation-actions').querySelector('button').textContent = 'Salva Modifiche';
  document.getElementById('wrapper-view-actions').style.display = 'none';
}

async function deleteReservation() {
  const bookingId = document.getElementById('booking-id').value;
  if (!bookingId) return;

  if (confirm('Sei sicuro di voler eliminare definitivamente questa prenotazione?')) {
    try {
      const response = await fetch('/lido/api/prenotazioni?id=' + encodeURIComponent(bookingId), {
        method: 'DELETE'
      });
      const result = await response.json();

      if (result.success) {
        closeReservationModal();
        if (typeof fetchUmbrellas === 'function') fetchUmbrellas();
      } else {
        alert('Errore durante l\'eliminazione: ' + result.message);
      }
    } catch (error) {
      console.error('Network error:', error);
      alert('Impossibile connettersi al server per eliminare.');
    }
  }
}

function closeReservationModal() {
  document.getElementById('modal-reservation').classList.remove('show');
  document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
}

async function handlePopupDateChange() {
  const umbrellaIdInput = document.getElementById('booking-umbrella-id');
  if (!umbrellaIdInput || !umbrellaIdInput.value) return;

  const popupStartDate = document.getElementById('booking-start');
  const popupEndDate = document.getElementById('booking-end');
  const priceDisplay = document.getElementById('display-total-cost');
  const btnPrenota = document.querySelector('#wrapper-creation-actions button[type="submit"]');

  const newStart = popupStartDate.value;
  let newEnd = popupEndDate.value;

  if (newStart > newEnd) {
    popupEndDate.value = newStart;
    newEnd = newStart;
  }

  priceDisplay.textContent = 'Calcolo...';

  try {
    const availUrl = '/lido/api/ombrelloni?inizio=' + encodeURIComponent(newStart)
      + '&fine=' + encodeURIComponent(newEnd);
    const availResponse = await fetch(availUrl);
    const allUmbrellas = await availResponse.json();

    const umbrellaId = parseInt(umbrellaIdInput.value, 10);
    const currentUmbData = allUmbrellas.find(item => item.id_ombrellone === umbrellaId);

    if (currentUmbData && currentUmbData.occupato == 1) {
      if (btnPrenota) btnPrenota.disabled = true;
      priceDisplay.textContent = 'Non Disponibile';
      priceDisplay.style.color = '#e74c3c';
      return;
    }

    if (btnPrenota) btnPrenota.disabled = false;
    priceDisplay.style.color = 'inherit';

    const priceUrl = '/lido/api/tariffe?tipo=' + encodeURIComponent(popupCurrentType)
      + '&inizio=' + encodeURIComponent(newStart)
      + '&fine=' + encodeURIComponent(newEnd);
    const priceResponse = await fetch(priceUrl);
    const priceData = await priceResponse.json();

    if (priceData.success && priceData.totale !== undefined) {
      priceDisplay.textContent = priceData.totale;
      document.getElementById('booking-total-cost-hidden').value = priceData.totale;
    } else {
      priceDisplay.textContent = 'Errore tariffa';
    }
  } catch (err) {
    console.error('Popup update error:', err);
    priceDisplay.textContent = 'Errore di rete';
  }
}

async function submitNewReservation(event) {
  event.preventDefault();

  const form = document.getElementById('form-new-reservation');
  const formData = new FormData(form);
  const btnSubmit = form.querySelector('button[type="submit"]');

  const originalText = btnSubmit.textContent;
  btnSubmit.textContent = 'Salvataggio in corso...';
  btnSubmit.disabled = true;

  try {
    const response = await fetch('/lido/api/prenotazioni', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.success) {
      alert('Prenotazione salvata con successo!');
      closeReservationModal();
      if (typeof fetchUmbrellas === 'function') {
        fetchUmbrellas();
      }
      form.reset();
    } else {
      alert('Errore: ' + result.message);
    }
  } catch (err) {
    console.error('Network error during reservation:', err);
    alert('Errore di connessione al server.');
  } finally {
    btnSubmit.textContent = originalText;
    btnSubmit.disabled = false;
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('modal-reservation');
  if (!modal) return;

  const closeButton = modal.querySelector('.close-modal');
  if (closeButton) {
    closeButton.addEventListener('click', closeReservationModal);
    closeButton.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        closeReservationModal();
      }
    });
  }

  const form = document.getElementById('form-new-reservation');
  if (form) {
    form.addEventListener('submit', submitNewReservation);
  }

  const deleteButton = document.getElementById('btn-delete-reservation');
  if (deleteButton) {
    deleteButton.addEventListener('click', deleteReservation);
  }

  const popupStartDate = document.getElementById('booking-start');
  const popupEndDate = document.getElementById('booking-end');

  if (popupStartDate && popupEndDate) {
    popupStartDate.addEventListener('change', handlePopupDateChange);
    popupEndDate.addEventListener('change', handlePopupDateChange);
  }

  modal.addEventListener('click', (e) => {
    if (e.target === modal) {
      closeReservationModal();
    }
  });
});
