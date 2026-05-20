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
</main>

<?php include 'popup.php'; ?>

<script>
  const startDateInput = document.getElementById('start-date');
  const endDateInput = document.getElementById('end-date');
  const mapForm = document.getElementById('form-filtri-mappa');

  window.addEventListener('load', () => {
    const today = new Date();
    startDateInput.value = today.toISOString().split('T')[0];

    const tomorrow = new Date(today);
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
        
        // CORRETTO: Sistemata la sintassi del template literal string (era '$[letter}...')
        const codiceCompleto = `${letter}.${fila}.${posto}`;

        const isDisabled = (letter === 'A' && fila == 10 && posto == 20) ||
          (letter === 'B' && fila == 10 && posto == 20) ||
          (letter === 'C' && fila == 10 && posto == 20) ||
          (letter === 'D' && fila == 10 && posto == 20) ||
          (letter === 'E' && fila == 10 && posto == 1);

        if (isDisabled || (u.tipologia_nome && u.tipologia_nome.includes("Disabile"))) {
          dot.classList.add('disable');
        }

        dot.title = codiceCompleto;

        // COLLEGARE IL POPUP REALE IN INGLESE
        dot.addEventListener('click', () => { 
          // Rimuove la selezione visiva precedente da altri elementi
          document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
          // Aggiunge la classe di selezione all'ombrellone corrente
          dot.classList.add('selected');
          
          // Estrae dinamicamente tipologia e costo (o usa valori di fallback)
          const tipologiaOmbrellone = u.tipologia_nome || u.tipologia || 'BASE';
          const costoOmbrellone = u.prezzo_base || u.prezzo || '0';
          
          // Apre il nuovo modal con i parametri configurati
          openReservationModal(codiceCompleto, tipologiaOmbrellone, costoOmbrellone); 
        });

        beachGrid.appendChild(dot);
      });

      container.appendChild(beachGrid);
      grid.appendChild(container);
    });
  }
</script>

<?php include 'components/footer.php'; ?>