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
        zoomedLetter = activeSector.dataset.sectorLetter;
      }
    }
    await fetchUmbrellas();
    if (isZoomed && zoomedLetter) {
      grid.classList.remove('sector-zoomed-mode');
      zoomSector(zoomedLetter);
    }
  });
}

document.getElementById('grid').addEventListener('click', (e) => {
  const sectorHeader = e.target.closest('.sector-header');
  if (sectorHeader) {
    const letter = sectorHeader.dataset.sectorLetter;
    if (letter) {
      zoomSector(letter);
    }
    return;
  }

  const backButton = e.target.closest('.btn-back-map');
  if (backButton) {
    const letter = backButton.dataset.sectorLetter;
    if (letter) {
      resetZoom(letter);
    }
  }
});

async function fetchUmbrellas() {
  const grid = document.getElementById('grid');
  grid.innerHTML = "<p class='center-text' style='grid-column: 1/-1;'>Caricamento in corso...</p>";

  try {
    const url = '/lido/api/ombrelloni?inizio=' + encodeURIComponent(startDateInput.value)
      + '&fine=' + encodeURIComponent(endDateInput.value);
    const response = await fetch(url);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    drawMap(data);
    return data;
  } catch (e) {
    grid.innerHTML = "<p class='center-text' style='grid-column: 1/-1; color:red;'>Errore tecnico: "
      + e.message + "</p>";
  }
}

function drawMap(umbrellas) {
  const grid = document.getElementById('grid');
  grid.innerHTML = "";

  grid.classList.remove('sector-zoomed-mode');

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
    container.id = 'sector-container-' + letter;
    container.dataset.sectorLetter = letter;

    const sectorHeader = document.createElement('div');
    sectorHeader.className = 'sector-header';
    sectorHeader.style.cursor = 'pointer';
    sectorHeader.dataset.sectorLetter = letter;
    sectorHeader.textContent = 'SETTORE ' + letter;
    container.appendChild(sectorHeader);

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
      const codiceCompleto = letter + '.' + fila + '.' + posto;
      dot.style.gridRow = fila;
      dot.style.gridColumn = posto;

      const isDisabled = (letter === 'A' && fila == 10 && posto == 20) ||
        (letter === 'B' && fila == 10 && posto == 20) ||
        (letter === 'C' && fila == 10 && posto == 20) ||
        (letter === 'D' && fila == 10 && posto == 20) ||
        (letter === 'E' && fila == 10 && posto == 1);
      if (isDisabled || (!!(u.tipologia_nome && u.tipologia_nome.includes("Disabile")))) {
        dot.classList.add('disable');
      }
      dot.title = codiceCompleto;

      const isReserved = (u.occupato == 1);

      dot.addEventListener('click', async () => {
        document.querySelectorAll('.umbrella.selected').forEach(el => el.classList.remove('selected'));
        dot.classList.add('selected');

        let tipologiaOmbrellone = u.tipologia_nome || u.tipologia || 'BASE';
        if (isDisabled) {
          tipologiaOmbrellone = 'Ombrellone Disabili';
        }

        let costoOmbrellone = '0.00';

        try {
          const priceUrl = '/lido/api/tariffe?tipo=' + encodeURIComponent(tipologiaOmbrellone)
            + '&inizio=' + encodeURIComponent(startDateInput.value)
            + '&fine=' + encodeURIComponent(endDateInput.value);
          const priceResponse = await fetch(priceUrl);

          if (priceResponse.ok) {
            const priceData = await priceResponse.json();
            if (priceData.success && priceData.totale !== undefined) {
              costoOmbrellone = priceData.totale;
            } else if (priceData.message) {
              console.warn('Price calculation warning:', priceData.message);
            }
          }
        } catch (err) {
          console.error('Error fetching umbrella price:', err);
        }

        const reservationUrl = '/lido/api/prenotazioni?id_ombrellone='
          + encodeURIComponent(u.id_ombrellone) + '&data_inizio=' + encodeURIComponent(startDateInput.value);
        const reservationResponse = await fetch(reservationUrl);

        if (reservationResponse.ok) {
          const reservationJson = await reservationResponse.json();
          if (!reservationJson.success && reservationJson.message) {
            console.warn('Reservation data warning:', reservationJson.message);
          }
          openReservationModal(
            codiceCompleto,
            tipologiaOmbrellone,
            costoOmbrellone,
            isReserved,
            reservationJson.success ? reservationJson : { id_ombrellone: u.id_ombrellone }
          );
        } else {
          console.error('Error fetching reservation data:', reservationResponse.statusText);
          openReservationModal(
            codiceCompleto,
            tipologiaOmbrellone,
            costoOmbrellone,
            isReserved,
            { id_ombrellone: u.id_ombrellone }
          );
        }
      });
      beachGrid.appendChild(dot);
    });
    container.appendChild(beachGrid);
    grid.appendChild(container);
  });
}

function zoomSector(letter) {
  const grid = document.getElementById('grid');
  if (grid.classList.contains('sector-zoomed-mode')) return;

  grid.classList.add('sector-zoomed-mode');
  const container = document.getElementById('sector-container-' + letter);
  container.classList.add('is-zoomed');

  const beachGrid = container.querySelector('.beach');
  const maxFila = parseInt(beachGrid.dataset.maxRows);
  const maxPosto = parseInt(beachGrid.dataset.maxCols);
  const battleshipWrapper = document.createElement('div');
  battleshipWrapper.className = 'battleship-wrapper';

  const zoomHeader = document.createElement('div');
  zoomHeader.className = 'zoom-header';

  const backButton = document.createElement('button');
  backButton.type = 'button';
  backButton.className = 'btn-back-map';
  backButton.dataset.sectorLetter = letter;
  backButton.textContent = '\u2190 Indietro';

  const zoomTitle = document.createElement('span');
  zoomTitle.className = 'zoom-title';
  zoomTitle.textContent = 'SETTORE ' + letter;

  zoomHeader.appendChild(backButton);
  zoomHeader.appendChild(zoomTitle);

  const topCoords = document.createElement('div');
  topCoords.className = 'coords-top';
  topCoords.style.setProperty('--max-cols', maxPosto);

  const leftCoords = document.createElement('div');
  leftCoords.className = 'coords-left';
  leftCoords.style.setProperty('--max-rows', maxFila);

  for (let p = 1; p <= maxPosto; p++) {
    const cell = document.createElement('div');
    cell.className = 'coord-cell-x';
    cell.textContent = String(p);
    topCoords.appendChild(cell);
  }
  for (let f = 1; f <= maxFila; f++) {
    const cell = document.createElement('div');
    cell.className = 'coord-cell-y';
    cell.textContent = String(f);
    leftCoords.appendChild(cell);
  }

  container.insertBefore(battleshipWrapper, beachGrid);
  battleshipWrapper.appendChild(zoomHeader);
  battleshipWrapper.appendChild(beachGrid);
  battleshipWrapper.appendChild(leftCoords);
  battleshipWrapper.appendChild(topCoords);
}

function resetZoom(letter) {
  document.getElementById('grid').classList.remove('sector-zoomed-mode');
  const container = document.getElementById('sector-container-' + letter);
  container.classList.remove('is-zoomed');
  const battleshipWrapper = container.querySelector('.battleship-wrapper');
  const beachGrid = container.querySelector('.beach');

  if (battleshipWrapper && beachGrid) {
    container.appendChild(beachGrid);
    battleshipWrapper.remove();
  }
}
