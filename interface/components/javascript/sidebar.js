document.addEventListener("DOMContentLoaded", () => {
  // 1. Controllo per la pagina Contratti (contratti.php)
  const startContratti = document.getElementById('sidebar-contratti-da');
  const endContratti = document.getElementById('sidebar-contratti-a');

  if (startContratti && endContratti) {
    startContratti.addEventListener('input', () => {
      // Imposta il vincolo 'min' nativo sul calendario del campo di fine
      endContratti.min = startContratti.value;
      
      // Se la data di fine inserita diventa inferiore a quella d'inizio, la allinea automaticamente
      if (endContratti.value && endContratti.value < startContratti.value) {
        endContratti.value = startContratti.value;
      }
    });
  }

  // 2. Controllo per la Mappa Lido (index.php)
  const startMap = document.getElementById('start-date');
  const endMap = document.getElementById('end-date');

  if (startMap && endMap) {
    startMap.addEventListener('input', () => {
      endMap.min = startMap.value;
      if (endMap.value && endMap.value < startMap.value) {
        endMap.value = startMap.value;
      }
    });
  }
});