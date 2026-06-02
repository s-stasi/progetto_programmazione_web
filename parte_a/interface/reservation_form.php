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

    
    <form id="form-new-reservation" onsubmit="submitNewReservation(event)">
      <input type="hidden" id="booking-id" name="id_prenotazione">
      <input type="hidden" id="booking-umbrella-id" name="id_ombrellone">
      <input type="hidden" id="booking-total-cost-hidden" name="prezzo_totale">
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

      <div id="wrapper-view-actions">
        <button type="button" class="btn-ios-danger" onclick="deleteReservation()">Elimina Prenotazione</button>
      </div>
    </form>
  </div>
</div>

<script src="javascript/reservation_form.js"></script>