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

  <div id="modal-free" class="modal">
    <div class="modal-content modal-ios">
      <div class="modal-header ios-header">
        <h3 class="txt-oro-main">PRENOTA</h3>
        <span class="close-modal" onclick="chiediConfermaChiusura('modal-free')">&times;</span>
      </div>
      
      <form id="form-nuova-prenotazione" method="POST" action="../php/inserisci_prenotazione.php">
        <input type="hidden" id="p-id-ombrellone" name="id_ombrellone">
        
        <div class="ios-info-box">
          <div id="info-posizione-ombrellone" class="txt-info-ombrellone-large txt-oro-sub"></div>
          <input type="text" id="p-tipologia" readonly class="ios-input-transparent txt-tipologia-small txt-grigio-medium">
        </div>

        <div class="ios-row-container">
          <div class="ios-date-inputs">
            <div class="ios-date-field">
              <span class="ios-label txt-oro-sub-inline">Dal:</span>
              <input type="date" id="p-inizio" name="data_inizio" required class="txt-grigio-medium">
            </div>
            <div class="ios-date-field">
              <span class="ios-label txt-oro-sub-inline">Al:</span>
              <input type="date" id="p-fine" name="data_fine" required class="txt-grigio-medium">
            </div>
          </div>
          <div class="ios-price-box">
            <div class="ios-price-label txt-oro-sub">Costo</div>
            <div id="prezzo-calcolato" class="ios-price-value txt-grigio-bold">0.00 €</div>
          </div>
        </div>

        <hr class="ios-divider">

        <div class="modal-profile-box">
          <div class="profile-box-title txt-oro-sub">Dati Cliente</div>
          
          <div class="ios-input-group row-fields">
            <input type="text" name="nome" placeholder="Nome" required class="field-half txt-grigio-medium">
            <input type="text" name="cognome" placeholder="Cognome" required class="field-half field-left-border txt-grigio-medium">
          </div>
          
          <div class="ios-input-group inline-field">
            <span class="ios-label txt-grigio-medium-label">Nascita:</span>
            <input type="date" name="data_nascita" required class="txt-grigio-medium">
          </div>

          <div class="ios-input-group">
            <input type="email" name="email" placeholder="Indirizzo Email" required class="txt-grigio-medium">
          </div>

          <div class="ios-input-group">
            <input type="tel" name="telefono" placeholder="Numero di Telefono" required class="txt-grigio-medium">
          </div>

          <div class="ios-input-group field-no-border">
            <input type="text" name="indirizzo_casa" placeholder="Indirizzo di Casa" required class="txt-grigio-medium">
          </div>
        </div>

        <div class="ios-actions">
          <button type="submit" class="btn-ios-primary">Conferma Prenotazione</button>
        </div>
      </form>
    </div>
  </div>

  <div id="modal-reserved" class="modal">
    <div class="modal-content modal-ios-box-occupato">
      <div class="modal-header ios-header">
        <h3 id="titolo-modal-occupato">Gestione Prenotazione</h3>
        <span class="close-modal" onclick="chiudiModal('modal-occupato')">&times;</span>
      </div>
      
      <div class="ios-reserved-info">
        <p><strong>Stato:</strong> <span class="txt-status-occupato">Occupato</span></p>
        <p id="info-prenotazione-corrente">Caricamento dettagli della prenotazione...</p>
      </div>

      <div class="ios-vertical-actions">
        <button id="btn-vai-modifica" class="btn-ios-action-change">
            Modifica Dettagli Prenotazione
        </button>
        <button id="btn-vai-elimina" class="btn-ios-action-delete">
            Elimina / Cancella Prenotazione
        </button>
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
        dot.addEventListener('click', () => { apriGestioneDot(u, letter); });
        beachGrid.appendChild(dot);
      });
      
      container.appendChild(beachGrid);
      grid.appendChild(container);
    });
  }

  function formattaDataItaliana(dataStr) {
    if(!dataStr) return 'N/D';
    const parti = dataStr.split('-');
    if(parti.length !== 3) return dataStr;
    return `${parti[2]}/${parti[1]}/${parti[0]}`;
  }

  function apriGestioneDot(u, letter) {
    const fila = u.numero_fila || u.numFila || 'N/D';
    const posto = u.numero_ordine || u.numPostoFila || 'N/D';

    if (u.occupato == 1) {
      document.getElementById('titolo-modal-reserved').innerText = `Ombrellone ${letter}.${fila}.${posto}`;
      document.getElementById('info-prenotazione-corrente').innerHTML = `
        <strong>Cliente:</strong> ${u.cliente_nome ?? 'N/D'}<br>
        <strong>Periodo:</strong> dal ${formattaDataItaliana(startDateInput.value)} al ${formattaDataItaliana(endDateInput.value)}
      `;
      
      document.getElementById('btn-vai-modifica').onclick = () => {
          window.location.href = `gestione_prenotazione.php?azione=modifica&id_ombrellone=${u.id}&inizio=${startDateInput.value}&fine=${endDateInput.value}`;
      };
      document.getElementById('btn-vai-elimina').onclick = () => {
          if(confirm("Sei sicuro di voler cancellare questa prenotazione?")) {
              window.location.href = `../php/elimina_prenotazione.php?id_ombrellone=${u.id}&inizio=${startDateInput.value}&fine=${endDateInput.value}`;
          }
      };
      document.getElementById('modal-occupato').style.display = 'block';
    } else {
      document.getElementById('info-posizione-ombrellone').innerText = `OMBRELLONE ${letter} • FILA ${fila} • POSTO ${posto}`;
      document.getElementById('p-id-ombrellone').value = u.id;
      document.getElementById('p-tipologia').value = u.tipologia_nome || u.nome_tipologia || 'Standard';
      
      document.getElementById('p-inizio').value = startDateInput.value;
      document.getElementById('p-fine').value = endDateInput.value;
      
      calcolaPrezzoAutomatico(u.prezzo_giornaliero ?? 15.00);
      
      const pInizio = document.getElementById('p-inizio');
      const pFine = document.getElementById('p-fine');
      
      const aggiornaPrezzo = () => {
          if (pFine.value < pInizio.value) pFine.value = pInizio.value;
          calcolaPrezzoAutomatico(u.prezzo_giornaliero ?? 15.00);
      };
      
      pInizio.oninput = aggiornaPrezzo;
      pFine.oninput = aggiornaPrezzo;
      document.getElementById('modal-free').style.display = 'block';
    }
  }

  function chiudiModal(id) {
    document.getElementById(id).style.display = 'none';
  }
  
  function chiediConfermaChiusura(id) {
    if (confirm("Sei sicuro di voler tornare indietro? I dati inseriti andranno perduti.")) {
        chiudiModal(id);
    }
  }

  function calcolaPrezzoAutomatico(prezzoGiornaliero) {
    const dataInizio = new Date(document.getElementById('p-inizio').value);
    const dataFine = new Date(document.getElementById('p-fine').value);
    if (isNaN(dataInizio) || (!isNaN(dataFine) && dataFine < dataInizio)) {
        document.getElementById('prezzo-calcolato').innerText = "0.00 €";
        return;
    }
    const diffTime = Math.abs(dataFine - dataInizio);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    document.getElementById('prezzo-calcolato').innerText = `${(diffDays * prezzoGiornaliero).toFixed(2)} €`;
  }

  window.onclick = function(event) {
    if (event.target.className === 'modal') event.target.style.display = "none";
  }
</script>

<?php include 'components/footer.php'; ?>