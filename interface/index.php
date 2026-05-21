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
    mapForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (startDateInput.value > endDateInput.value) {
        endDateInput.value = startDateInput.value;
      }

      const grid = document.getElementById('grid');
      const isZoomed = grid.classList.contains('sector-zoomed-mode');
      let zoomedLetter = null;

      if (isZoomed) {
        const activeSector = grid.querySelector('.sector-container.is-zoomed');
        if (activeSector) {
          zoomedLetter = activeSector.id.replace('sector-container-', '');
        }
      }
      await fetchUmbrellas();
      if (isZoomed && zoomedLetter) {
        grid.classList.remove('sector-zoomed-mode'); 
        zoomSector(zoomedLetter);
      }
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
      return data;
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
      container.id = `sector-container-${letter}`;

      container.innerHTML = `
        <div class="sector-header" style="cursor: pointer;" onclick="zoomSector('${letter}')">SETTORE ${letter}</div>`;

      const beachGrid = document.createElement('div');
      beachGrid.className = 'beach';

      const sortedUmbrellas = sectors[sectorId].sort((a, b) =>
        (a.numero_fila || a.numFila) - (b.numero_fila || b.numFila) ||
        (a.numero_ordine || a.numPostoFila) - (b.numero_ordine || b.numPostoFila)
      );

      let maxFila = 0;
      let maxPosto = 0;

      sortedUmbrellas.forEach(u => {
        const f = parseInt(u.numero_fila || u.numFila || 0);
        const p = parseInt(u.numero_ordine || u.numPostoFila || 0);
        if (f > maxFila) maxFila = f;
        if (p > maxPosto) maxPosto = p;
      });

      // Store layout dimensions for the dynamic zoom view
      beachGrid.dataset.maxRows = maxFila;
      beachGrid.dataset.maxCols = maxPosto;

      beachGrid.style.setProperty('--max-rows', maxFila);
      beachGrid.style.setProperty('--max-cols', maxPosto);

      sortedUmbrellas.forEach(u => {
        const dot = document.createElement('div');
        dot.className = 'umbrella';

        if (u.occupato == 1) {
          dot.classList.add('reserved');
        }

        const fila = u.numero_fila || u.numFila || '0';
        const posto = u.numero_ordine || u.numPostoFila || '0';
        const codiceCompleto = `${letter}.${fila}.${posto}`;

        dot.style.gridRow = fila;
        dot.style.gridColumn = posto;

        const isDisabled = (letter === 'A' && fila == 10 && posto == 20) ||
          (letter === 'B' && fila == 10 && posto == 20) ||
          (letter === 'C' && fila == 10 && posto == 20) ||
          (letter === 'D' && fila == 10 && posto == 20) ||
          (letter === 'E' && fila == 10 && posto == 1);

        if (isDisabled || (u.tipologia_nome && u.tipologia_nome.includes("Disabile"))) {
          dot.classList.add('disable');
        }

        dot.title = codiceCompleto;

        dot.addEventListener('click', () => { 
          document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
          dot.classList.add('selected');
          
          const tipologiaOmbrellone = u.tipologia_nome || u.tipologia || 'BASE';
          const costoOmbrellone = u.prezzo_base || u.prezzo || '0';
          
          openReservationModal(codiceCompleto, tipologiaOmbrellone, costoOmbrellone); 
        });

        beachGrid.appendChild(dot);
      });

      container.appendChild(beachGrid);
      grid.appendChild(container);
    });
  }

  // Dynamically injects rows, columns and header components when a sector is selected
  function zoomSector(letter) {
    const grid = document.getElementById('grid');
    if (grid.classList.contains('sector-zoomed-mode')) return;

    grid.classList.add('sector-zoomed-mode');
    const container = document.getElementById(`sector-container-${letter}`);
    container.classList.add('is-zoomed');

    const beachGrid = container.querySelector('.beach');
    const maxFila = parseInt(beachGrid.dataset.maxRows);
    const maxPosto = parseInt(beachGrid.dataset.maxCols);

    const battleshipWrapper = document.createElement('div');
    battleshipWrapper.className = 'battleship-wrapper';

    const zoomHeader = document.createElement('div');
    zoomHeader.className = 'zoom-header';
    zoomHeader.innerHTML = `
      <button type="button" class="btn-back-map" onclick="resetZoom('${letter}')">← Indietro</button>
      <span class="zoom-title">SETTORE ${letter}</span>
    `;

    const topCoords = document.createElement('div');
    topCoords.className = 'coords-top';
    topCoords.style.setProperty('--max-cols', maxPosto);

    const leftCoords = document.createElement('div');
    leftCoords.className = 'coords-left';
    leftCoords.style.setProperty('--max-rows', maxFila);

    for (let p = 1; p <= maxPosto; p++) {
      topCoords.innerHTML += `<div class="coord-cell-x">${p}</div>`;
    }
    for (let f = 1; f <= maxFila; f++) {
      leftCoords.innerHTML += `<div class="coord-cell-y">${f}</div>`;
    }

    container.insertBefore(battleshipWrapper, beachGrid);
    battleshipWrapper.appendChild(zoomHeader);
    battleshipWrapper.appendChild(beachGrid);
    battleshipWrapper.appendChild(leftCoords);
    battleshipWrapper.appendChild(topCoords);
  }

  // Removes coordinates structures and restores the standard beach view layout
  function resetZoom(letter) {
    document.getElementById('grid').classList.remove('sector-zoomed-mode');
    const container = document.getElementById(`sector-container-${letter}`);
    container.classList.remove('is-zoomed');

    const battleshipWrapper = container.querySelector('.battleship-wrapper');
    const beachGrid = container.querySelector('.beach');

    if (battleshipWrapper && beachGrid) {
      container.appendChild(beachGrid);
      battleshipWrapper.remove();
    }
  }
</script>

<?php include 'components/footer.php'; ?>