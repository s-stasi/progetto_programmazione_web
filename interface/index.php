<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="page-body">
  <div class="table-header">
    <h2 class="ricerca-titolo">Mappa Spiaggia</h2>
  </div>

  <div class="legend">
    <div class="item"><span class="badge free"></span> Disponibile</div>
    <div class="item"><span class="badge reserved"></span> Occupato</div>
    <div class="item"><span class="badge disable"></span> Disabili</div>
  </div>

  <div class="sea">MARE</div>
  <div id="grid"></div>

  <div id="modal-booking" class="modal">
    <div class="modal-content modal-ios">

      <div class="modal-header ios-header">
        <h2 id="booking-modal-title" class="txt-oro-main">
          PRENOTAZIONE
        </h2>

        <span class="close-modal" onclick="chiudiModal('modal-booking')">
          &times;
        </span>
      </div>

      <form id="form-booking" method="POST" action="../php/inserisci_prenotazione.php">

        <input type="hidden" id="p-id-ombrellone" name="id_ombrellone">
        <input type="hidden" id="p-id-prenotazione" name="id_prenotazione">

        <div id="info-codice-ombrellone" class="ios-subtitle"></div>

        <div class="ios-type-badge">
          <span id="p-tipologia-testo"></span>
        </div>

        <div class="ios-row-container">

          <div class="ios-date-inputs">

            <div class="ios-date-field">
              <span class="ios-date-label">Da:</span>

              <input type="date" id="p-inizio" name="data_inizio" required>
            </div>

            <div class="ios-date-field">
              <span class="ios-date-label">Al:</span>

              <input type="date" id="p-fine" name="data_fine" required>
            </div>

          </div>

          <div class="ios-price-box">

            <span class="ios-price-label">
              Costo
            </span>

            <div id="prezzo-calcolato" class="ios-price-value">
              0.00 €
            </div>

          </div>

        </div>

        <hr class="ios-divider">

        <div class="modal-profile-box">

          <div class="ios-input-group row-fields">

            <input type="text" id="p-nome" name="nome" placeholder="Nome" required class="field-half">

            <input type="text" id="p-cognome" name="cognome" placeholder="Cognome" required class="field-half">

          </div>

          <div class="ios-input-group">
            <input type="email" id="p-email" name="email" placeholder="Email" required>
          </div>

          <div class="ios-input-group ios-input-last">
            <input type="tel" id="p-telefono" name="telefono" placeholder="Telefono" required>
          </div>

        </div>

        <div class="ios-actions">

          <button type="submit" id="btn-submit-booking" class="btn-ios-action btn-ios-gold">
            Conferma
          </button>

          <button type="button" id="btn-trigger-delete" class="btn-ios-action btn-ios-danger" style="display: none;">
            Elimina Prenotazione
          </button>

        </div>
      </form>

    </div>
  </div>

  <div id="modal-delete-confirm" class="modal">
    <div class="modal-content modal-ios modal-delete">
      <div class="modal-header ios-header ios-header-center">
        <h2 class="txt-oro-main delete-title"> Annulla Prenotazione </h2>
      </div>

      <div class="delete-text"> Sei sicuro di voler eliminare la prenotazione dell'ombrellone 
        <strong id="del-codice"></strong> a nome di <strong id="del-cliente"></strong>?
      </div>

      <div class="ios-actions ios-actions-row">
        <button id="btn-conferma-eliminazione" class="btn-ios-action btn-ios-danger"> Sì, Elimina </button>
        <button type="button" class="btn-ios-action btn-ios-cancel" onclick="chiudiModal('modal-delete-confirm')"> No </button>
      </div>

    </div>
  </div>
</main>

<script>
  const startDateInput = document.getElementById('start-date');
  const endDateInput = document.getElementById('end-date');
  const mapForm = document.getElementById('form-filtri-mappa');

  window.addEventListener('load', () => {
    const today = new Date();
    startDateInput.value = today.toISOString().split('T')[0];

    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    endDateInput.value = tomorrow.toISOString().split('T')[0];

    fetchUmbrellas();
  });

  startDateInput.addEventListener('input', () => {
    if (endDateInput.value < startDateInput.value) {
      endDateInput.value = startDateInput.value;
    }
  });

  if (mapForm) {
    mapForm.addEventListener('submit', (e) => {
      e.preventDefault();
      if (startDateInput.value > endDateInput.value) {
        endDateInput.value = startDateInput.value;
      }
      fetchUmbrellas();
    });
  }

  async function fetchUmbrellas() {
    const grid = document.getElementById('grid');
    grid.innerHTML = "<p class='center-text' style='grid-column: 1/-1;'>Caricamento in corso...</p>";

    try {
      const url = `../php/get_umbrellas.php?inizio=${startDateInput.value}&fine=${endDateInput.value}`;
      const response = await fetch(url);
      if (!response.ok) throw new Error('Network response was not ok');
      const data = await response.json();
      drawMap(data);
    } catch (e) {
      grid.innerHTML = `<p class='center-text' style='grid-column: 1/-1; color:red;'>Errore tecnico: ${e.message}</p>`;
    }
  }

  function drawMap(umbrellas) {
    const grid = document.getElementById('grid');
    grid.innerHTML = "";

    const letters = { 1: 'A', 2: 'B', 3: 'C', 4: 'D', 5: 'E' };
    const sectors = {};

    umbrellas.forEach(u => {
      if (!sectors[u.settore]) sectors[u.settore] = [];
      sectors[u.settore].push(u);
    });

    Object.keys(sectors).sort().forEach(sectorId => {
      const letter = letters[sectorId];
      const container = document.createElement('div');
      container.className = 'sector-container';
      container.innerHTML = `<div class="sector-header">SETTORE ${letter}</div>`;

      const beachGrid = document.createElement('div');
      beachGrid.className = 'beach';

      sectors[sectorId].sort((a, b) => (a.numero_fila || a.numFila) - (b.numero_fila || b.numFila) || (a.numero_ordine || a.numPostoFila) - (b.numero_ordine || b.numPostoFila)).forEach(u => {
        const dot = document.createElement('div');
        dot.className = 'umbrella';

        if (u.occupato == 1) {
          dot.classList.add('reserved');
        }

        const fila = u.numero_fila || u.numFila || '0';
        const posto = u.numero_ordine || u.numPostoFila || '0';

        const isDisabled = (letter === 'A' && fila == 10 && posto == 20) ||
          (letter === 'B' && fila == 10 && posto == 20) ||
          (letter === 'C' && fila == 10 && posto == 20) ||
          (letter === 'D' && fila == 10 && posto == 20) ||
          (letter === 'E' && fila == 10 && posto == 1);

        if (isDisabled || (u.tipologia_nome && u.tipologia_nome.includes("Disabile"))) {
          dot.classList.add('disable');
        }

        dot.title = `${letter}.${fila}.${posto}`;
        dot.addEventListener('click', () => { apriPopupUnico(u, letter, fila, posto) });
        beachGrid.appendChild(dot);
      });

      container.appendChild(beachGrid);
      grid.appendChild(container);
    });
  }

  function formattaDataItaliana(dataStr) {
    if (!dataStr) return 'N/D';
    const parti = dataStr.split('-');
    if (parti.length !== 3) return dataStr;
    return `${parti[2]}/${parti[1]}/${parti[0]}`;
  }

  // Gestione dinamica dei Popup
  function apriPopupUnico(u, letter, fila, posto) {
    const oggi = new Date().toISOString().split('T')[0];
    const codiceOmbrellone = `${letter}.${fila}.${posto}`;

    // Reset Form ed Elementi Comuni
    document.getElementById('p-id-ombrellone').value = u.id;
    document.getElementById('info-codice-ombrellone').innerText = `OMBRELLONE ${codiceOmbrellone}`;
    document.getElementById('p-tipologia-testo').innerText = u.tipologia_nome || 'Standard';

    const pInizio = document.getElementById('p-inizio');
    const pFine = document.getElementById('p-fine');
    const form = document.getElementById('form-booking');
    const btnDelete = document.getElementById('btn-trigger-delete');

    const calcolaPrezzo = () => {
      if (pFine.value < pInizio.value) pFine.value = pInizio.value;
      const diffDays = Math.ceil(Math.abs(new Date(pFine.value) - new Date(pInizio.value)) / (1000 * 60 * 60 * 24)) + 1;
      document.getElementById('prezzo-calcolato').innerText = `${(diffDays * (u.prezzo_giornaliero ?? 15.00)).toFixed(2)} €`;
    };

    pInizio.oninput = calcolaPrezzo;
    pFine.oninput = calcolaPrezzo;

    if (u.occupato == 1) {
      // MODALITÀ: MODIFICA PRENOTAZIONE ESISTENTE
      document.getElementById('booking-modal-title').innerText = "MODIFICA PRENOTAZIONE";
      form.action = "../php/modifica_prenotazione.php";
      btnDelete.style.display = "block"; // Mostra il tasto elimina

      // Assegna ID prenotazione e precompila i dati esistenti ricevuti dal DB
      document.getElementById('p-id-prenotazione').value = u.id_prenotazione || "";
      pInizio.value = u.data_inizio || getStartDateVal();
      pFine.value = u.data_fine || getEndDateVal();
      document.getElementById('p-nome').value = u.cliente_nome || "";
      document.getElementById('p-cognome').value = u.cliente_cognome || "";
      document.getElementById('p-email').value = u.cliente_email || "";
      document.getElementById('p-telefono').value = u.cliente_telefono || "";

      // Setup Evento click sul tasto "Elimina" -> Apre il popup piccolo
      btnDelete.onclick = () => {
        document.getElementById('del-codice').innerText = codiceOmbrellone;
        document.getElementById('del-cliente').innerText = `${u.cliente_nome ?? ''} ${u.cliente_cognome ?? ''}`.trim() || "N/D";

        document.getElementById('btn-conferma-eliminazione').onclick = () => {
          window.location.href = `../php/elimina_prenotazione.php?id_prenotazione=${u.id_prenotazione}&id_ombrellone=${u.id}`;
        };

        chiudiModal('modal-booking');
        apriModal('modal-delete-confirm');
      };

    } else {
      // MODALITÀ: NUOVA PRENOTAZIONE (DEFAULT OGGI)
      document.getElementById('booking-modal-title').innerText = "PRENOTA OMBRELLONE";
      form.action = "../php/inserisci_prenotazione.php";
      btnDelete.style.display = "none"; // Nasconde il tasto elimina

      // Imposta le date di default su "Oggi" come richiesto
      pInizio.value = oggi;
      pFine.value = oggi;
      form.reset(); // Svuota i vecchi campi utente compilati

      // Ri-assegna l'id dell'ombrellone appena resettato dal form.reset()
      document.getElementById('p-id-ombrellone').value = u.id;
    }

    calcolaPrezzo();
    apriModal('modal-booking');
  }

  function apriModal(id) { document.getElementById(id).classList.add('show'); }
  function chiudiModal(id) { document.getElementById(id).classList.remove('show'); }

  window.onclick = function (event) {
    if (event.target.classList.contains('modal')) event.target.classList.remove('show');
  }
</script>

<?php include 'components/footer.php'; ?>