<?php require_once '../php/config.php'; ?>

<div id="modal-reservation" class="modal">
  <div class="modal-ios">
    <div class="ios-header">
      <div class="ios-umbrella-info">
        <h2 id="display-umbrella-code" class="txt-oro-main" style="margin-bottom: 2px;">#00</h2>
        <span id="display-umbrella-type" class="txt-tipologia-small txt-grigio-medium" style="text-transform: uppercase; letter-spacing: 1px;">TIPOLOGIA</span>
      </div>
      <span class="close-modal" onclick="closeReservationModal()">&times;</span>
    </div>

    <form id="form-new-reservation" onsubmit="saveReservation(event)">
      <div class="ios-row-container" style="background: rgba(244, 208, 63, 0.1); border: 1px solid var(--primary-light);">
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
          <span class="txt-grigio-medium-label" style="font-size: 11px;">COSTO</span>
          <div class="ios-price-value txt-oro-main" style="font-size: 28px; line-height: 1;">
            €<span id="display-total-cost">0</span>
          </div>
        </div>
      </div>

      <hr class="ios-divider">

      <h3 class="txt-oro-sub" style="margin-bottom: 10px; padding-left: 5px;">Dati Cliente</h3>
      
      <div class="modal-profile-box" style="background: #fff; border: 1px solid var(--separator);">
        <div class="row-fields ios-input-group">
          <div class="field-half">
            <label class="txt-grigio-medium-label">Nome</label>
            <input type="text" name="nome" required>
          </div>
          <div class="field-half field-left-border">
            <label class="txt-grigio-medium-label">Cognome</label>
            <input type="text" name="cognome" required>
          </div>
        </div>

        <div class="ios-input-group">
          <label class="txt-grigio-medium-label">Data di Nascita</label>
          <input type="date" name="data_nascita">
        </div>

        <div class="ios-input-group">
          <label class="txt-grigio-medium-label">Indirizzo casa</label>
          <input type="text" name="indirizzo">
        </div>

        <div class="row-fields ios-input-group field-no-border">
          <div class="field-half">
            <label class="txt-grigio-medium-label">Email</label>
            <input type="email" name="email">
          </div>
          <div class="field-half field-left-border">
            <label class="txt-grigio-medium-label">Cellulare</label>
            <input type="tel" name="cellulare" >
          </div>
        </div>
      </div>

      <div class="ios-actions" style="margin-top: 20px;">
        <button type="submit" class="btn-ios-primary">Conferma Prenotazione</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openReservationModal(code, type, baseCost) {
    const modal = document.getElementById('modal-reservation');

    // Inserisce i dati dinamici tradotti in inglese
    document.getElementById('display-umbrella-code').innerText = 'Ombrellone #' + code;
    document.getElementById('display-umbrella-type').innerText = type;
    document.getElementById('display-total-cost').innerText = baseCost;

    // Mostra il modal
    modal.classList.add('show');
  }

  function closeReservationModal() {
    document.getElementById('modal-reservation').classList.remove('show');
    // Deseleziona l'ombrellone sulla mappa se applicabile
    document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
  }


  function formattaDataItaliana(dataStr) {
    if (!dataStr) return 'N/D';
    const parti = dataStr.split('-');
    if (parti.length !== 3) return dataStr;
    return `${parti[2]}/${parti[1]}/${parti[0]}`;
  }
</script>