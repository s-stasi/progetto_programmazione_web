<?php
// components/form_cliente.php
// Parametro atteso opzionale: $prefix (es. 'add', 'edit', 'client')
$prefix = $prefix ?? 'client';
?>
<div class="modal-profile-box">
  <div class="row-fields ios-input-group">
    <div class="field-half">
      <label class="txt-grigio-medium-label">Nome</label>
      <input type="text" name="nome" id="<?= $prefix ?>-nome" required>
    </div>
    <div class="field-half field-left-border">
      <label class="txt-grigio-medium-label">Cognome</label>
      <input type="text" name="cognome" id="<?= $prefix ?>-cognome" required>
    </div>
  </div>

  <div class="ios-input-group">
    <label class="txt-grigio-medium-label">Data di Nascita</label>
    <input type="date" name="data_nascita" id="<?= $prefix ?>-data-nascita">
  </div>

  <div class="ios-input-group">
    <label class="txt-grigio-medium-label">Indirizzo casa</label>
    <input type="text" name="indirizzo" id="<?= $prefix ?>-indirizzo">
  </div>

  <div class="row-fields ios-input-group">
    <div class="field-half">
      <label class="txt-grigio-medium-label">Email</label>
      <input type="email" name="email" id="<?= $prefix ?>-email">
    </div>
    <div class="field-half field-left-border">
      <label class="txt-grigio-medium-label">Cellulare</label>
      <input type="tel" name="cellulare" id="<?= $prefix ?>-cellulare">
    </div>
  </div>
</div>