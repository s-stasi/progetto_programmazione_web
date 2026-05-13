<?php include 'components/header.php'; ?>
<?php include 'components/sidebar.php'; ?>

<main class="corpo-pagina">
  <h2>Operational Map</h2>
  
  <div style="display: flex; gap: 15px; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #d4c59f;">
    <div class="date-field">
      <label>From:</label>
      <input type="date" id="start-date" onchange="handleDateChange()">
    </div>
    <div class="date-field">
      <label>To:</label>
      <input type="date" id="end-date" onchange="handleDateChange()">
    </div>
  </div>

  <div class="legenda">
    <div class="item"><span class="badge libero"></span> Available</div>
    <div class="item"><span class="badge occupato"></span> Occupied</div>
    <div class="item"><span class="badge disabile"></span> Disabled</div>
  </div>

  <div class="striscia-mare">SEA</div>
  <div id="griglia-spiaggia"></div>

</main>

<script>
  // DOM Elements for date filtering
  const startDateInput = document.getElementById('start-date');
  const endDateInput = document.getElementById('end-date');

  // Initialize dates on page load and trigger first fetch
  window.addEventListener('load', () => {
    const today = new Date();
    startDateInput.value = today.toISOString().split('T')[0];
    
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    endDateInput.value = tomorrow.toISOString().split('T')[0];
    
    fetchUmbrellas();
  });

  // Ensure start date is not greater than end date
  function handleDateChange() {
    if (startDateInput.value > endDateInput.value) {
      endDateInput.value = startDateInput.value;
    }
    fetchUmbrellas();
  }

  // Fetch availability data from the PHP API
  async function fetchUmbrellas() {
    const grid = document.getElementById('griglia-spiaggia');
    grid.innerHTML = "<p style='grid-column: 1/-1; text-align:center;'>Loading...</p>";
    
    try {
      const url = `../php/get_umbrellas.php?inizio=${startDateInput.value}&fine=${endDateInput.value}`;
      const response = await fetch(url);
      
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      
      const data = await response.json();
      drawMap(data);
    } catch (e) {
      grid.innerHTML = `<p style='grid-column: 1/-1; text-align:center; color:red;'>Technical error: ${e.message}</p>`;
    }
  }

  // Render the beach map dynamically
  function drawMap(umbrellas) {
    const grid = document.getElementById('griglia-spiaggia');
    grid.innerHTML = "";
    
    const letters = { 1: 'A', 2: 'B', 3: 'C', 4: 'D', 5: 'E' };
    const sectors = {};

    // Group umbrellas by sector
    umbrellas.forEach(u => {
      if (!sectors[u.settore]) sectors[u.settore] = [];
      sectors[u.settore].push(u);
    });

    // Render each sector
    Object.keys(sectors).sort().forEach(sectorId => {
      const letter = letters[sectorId];
      const container = document.createElement('div');
      container.className = 'settore-container';
      container.innerHTML = `<div class="settore-header">SECTOR ${letter}</div>`;

      const beachGrid = document.createElement('div');
      beachGrid.className = 'spiaggia-grid';

      // Sort umbrellas by row and seat, then render dots
      sectors[sectorId].sort((a, b) => a.numero_fila - b.numero_fila || a.numero_ordine - b.numero_ordine).forEach(u => {
        const dot = document.createElement('div');
        dot.className = 'ombrellone';
        
        // Mark as occupied if the API returns 1
        if (u.occupato == 1) {
          dot.classList.add('occupato');
        }

        // Hardcoded logic for disabled spots based on previous setup
        const isDisabled = (letter === 'A' && u.numero_fila == 10 && u.numero_ordine == 20) ||
                           (letter === 'B' && u.numero_fila == 10 && u.numero_ordine == 20) ||
                           (letter === 'C' && u.numero_fila == 10 && u.numero_ordine == 20) ||
                           (letter === 'D' && u.numero_fila == 10 && u.numero_ordine == 20) ||
                           (letter === 'E' && u.numero_fila == 10 && u.numero_ordine == 1);

        if (isDisabled || (u.tipologia_nome && u.tipologia_nome.includes("Disabile"))) {
          dot.classList.add('disabile');
        }

        // Add a tooltip to show the exact spot code on hover
        dot.title = `${letter}.${u.numero_fila}.${u.numero_ordine}`;
        
        beachGrid.appendChild(dot);
      });
      
      container.appendChild(beachGrid);
      grid.appendChild(container);
    });
  }
</script>

<?php include 'components/footer.php'; ?>