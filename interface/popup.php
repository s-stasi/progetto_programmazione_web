<?php require_once '../php/config.php'; ?>

<div id="modal-reservation" class="modal">
  <div class="modal-ios">
    <div class="ios-header">
      <div class="ios-umbrella-info">
        <h2 id="display-umbrella-code" class="txt-oro-main">#00</h2>
        <span id="display-umbrella-type" class="txt-tipologia-small txt-grigio-medium">TIPOLOGIA</span>
      </div>
      <span class="close-modal" onclick="closeReservationModal()">&times;</span>
    </div>

    <input type="hidden" id="booking-id" name="id_prenotazione">

    <form id="form-new-reservation" onsubmit="saveReservation(event)">
      <div class="ios-row-container">
        <div class="ios-date-inputs">
          <div class="ios-date-field">
            <label class="txt-oro-sub-inline">DA:</label>
            <input type="date" name="data_inizio" id="booking-start" value="<?= date('Y-m-d'); ?>" required>
          </div>
          <div class="ios-date-field">
            <label class="txt-oro-sub-inline">A: &nbsp;</label>
            <input type="date" name="data_fine" id="booking-end" value="<?= date('Y-m-d'); ?>" required>
          </div>
        </div>
        <div class="ios-price-box">
          <span class="txt-grigio-medium-label">COSTO</span>
          <div class="ios-price-value txt-oro-main">
            €<span id="display-total-cost">0</span>
          </div>
        </div>
      </div>

      <hr class="ios-divider">

      <h3 class="txt-oro-sub">Dati Cliente</h3>
      <?php
      $prefix = 'client';
      include 'components/client_form.php';
      ?>

      <div id="wrapper-creation-actions">
        <button type="submit" class="btn-ios-primary">Conferma Prenotazione</button>
      </div>

      <div id="wrapper-view-actions" style="display: none; gap: 12px;">
        <button type="button" class="btn-ios-danger" onclick="deleteReservation()">Elimina Prenotazione</button>
        <button type="button" class="btn-ios-secondary" onclick="enableModificationMode()">Modifica Dati</button>
      </div>
    </form>
  </div>
</div>

<script>
  let popupCurrentUmbrella = null;
  let popupCurrentType = null;
  function openReservationModal(code, type, baseCost, isReserved = false, reservationData = null) {
    popupCurrentUmbrella = reservationData;
    popupCurrentType = type;

    const modal = document.getElementById('modal-reservation');
    const form = document.getElementById('form-new-reservation');
    form.reset();

    document.getElementById('display-umbrella-code').innerText = 'Ombrellone ' + code;
    document.getElementById('display-umbrella-type').innerText = type;
    document.getElementById('display-total-cost').innerText = baseCost;

    const creationActions = document.getElementById('wrapper-creation-actions');
    const viewActions = document.getElementById('wrapper-view-actions');
    if (isReserved && reservationData) {
      creationActions.style.display = 'none';
      viewActions.style.display = 'flex';

      document.getElementById('booking-id').value = reservationData.id || '';
      document.getElementById('booking-start').value = reservationData.data_inizio || '';
      document.getElementById('booking-end').value = reservationData.data_fine || '';
      document.getElementById('client-nome').value = reservationData.nome || '';
      document.getElementById('client-cognome').value = reservationData.cognome || '';
      document.getElementById('client-data-nascita').value = reservationData.data_nascita || '';
      document.getElementById('client-indirizzo').value = reservationData.indirizzo || '';
      document.getElementById('client-email').value = reservationData.email || '';
      document.getElementById('client-cellulare').value = reservationData.cellulare || '';

      toggleFormInputs(true);
    } else {
      creationActions.style.display = 'block';
      viewActions.style.display = 'none';
      document.getElementById('booking-id').value = '';

      if (document.getElementById('start-date')) {
        document.getElementById('booking-start').value = document.getElementById('start-date').value;
        document.getElementById('booking-end').value = document.getElementById('end-date').value;
      }

      toggleFormInputs(false);
    }

    modal.classList.add('show');const popupStartDate = document.getElementById('popup-start-date');

    const popupEndDate = document.getElementById('popup-end-date');
    
    if (popupStartDate && popupEndDate) {
      popupStartDate.value = document.getElementById('start-date').value;
      popupEndDate.value = document.getElementById('end-date').value;
    }
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
    document.getElementById('wrapper-creation-actions').querySelector('button').innerText = 'Salva Modifiche';
    document.getElementById('wrapper-view-actions').style.display = 'none';
  }

  async function deleteReservation() {
    const bookingId = document.getElementById('booking-id').value;
    if (!bookingId) return;

    if (confirm("Sei sicuro di voler eliminare definitivamente questa prenotazione?")) {
      try {
        const response = await fetch(`../php/reservation/delete_reservation.php?id=${bookingId}`, {
          method: 'DELETE'
        });
        const result = await response.json();

        if (result.success) {
          closeReservationModal();
          if (typeof fetchUmbrellas === "function") fetchUmbrellas();
        } else {
          alert("Errore durante l'eliminazione: " + result.message);
        }
      } catch (error) {
        console.error("Errore di rete:", error);
        alert("Impossibile connettersi al server per eliminare.");
      }
    }
  }

  function closeReservationModal() {
    document.getElementById('modal-reservation').classList.remove('show');
    document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
  }

  async function handlePopupDateChange() {
    if (!popupCurrentUmbrella) return;

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
    
    priceDisplay.innerText = "Calcolo...";

    try {
      const availUrl = `../php/umbrella/get_umbrellas.php?inizio=${newStart}&fine=${newEnd}`;
      const availResponse = await fetch(availUrl);
      const allUmbrellas = await availResponse.json();
      
      const currentUmbData = allUmbrellas.find(item => item.id_ombrellone === popupCurrentUmbrella.id_ombrellone);
      
      if (currentUmbData && currentUmbData.occupato == 1) {
        if (btnPrenota) btnPrenota.disabled = true;
        priceDisplay.innerText = "Non Disponibile";
        priceDisplay.style.color = "#e74c3c";
        return;
      } else {
        if (btnPrenota) btnPrenota.disabled = false;
        priceDisplay.style.color = "inherit";
      }

      const priceUrl = `../php/umbrella/get_price.php?tipo=${encodeURIComponent(popupCurrentType)}&inizio=${newStart}&fine=${newEnd}`;
      const priceResponse = await fetch(priceUrl);
      const priceData = await priceResponse.json();

      if (!priceData.error) {
        priceDisplay.innerText = priceData.totale;
      } else {
        priceDisplay.innerText = "Errore tariffa";
      }

    } catch (err) {
      console.error('Errore aggiornamento popup:', err);
      priceDisplay.innerText = "Errore di rete";
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const popupStartDate = document.getElementById('booking-start');
    const popupEndDate = document.getElementById('booking-end');

    if (popupStartDate && popupEndDate) {
      popupStartDate.addEventListener('change', handlePopupDateChange);
      popupEndDate.addEventListener('change', handlePopupDateChange);
    }
  });
</script>