document.addEventListener("DOMContentLoaded", () => {
  
  // 1. Controllo per la pagina Contratti (contratti.php)
  const startContratti = document.getElementById('sidebar-contratti-da');
  const endContratti = document.getElementById('sidebar-contratti-a');

  if (startContratti && endContratti) {
    const allineaContratti = () => {
      endContratti.min = startContratti.value;
      if (endContratti.value && endContratti.value < startContratti.value) {
        endContratti.value = startContratti.value;
      }
    };
    // Sincronizza subito all'avvio con i valori del PHP
    allineaContratti();
    // Sincronizza al cambio manuale
    startContratti.addEventListener('input', allineaContratti);
  }

  // 2. Controllo per la Mappa Lido (index.php)
  const startMap = document.getElementById('start-date');
  const endMap = document.getElementById('end-date');

  if (startMap && endMap) {
    const allineaMappa = () => {
      endMap.min = startMap.value;
      if (endMap.value && endMap.value < startMap.value) {
        endMap.value = startMap.value;
      }
    };
    allineaMappa();
    startMap.addEventListener('input', allineaMappa);
  }

  // 3. Controllo per il Listino Tariffe (rates.php)
  const startTariffe = document.getElementById('sidebar-tariffe-da');
  const endTariffe = document.getElementById('sidebar-tariffe-a');

  if (startTariffe && endTariffe) {
    const allineaTariffe = () => {
      endTariffe.min = startTariffe.value;
      
      if (endTariffe.value && endTariffe.value < startTariffe.value) {
        endTariffe.value = startTariffe.value;
      }
    };
    
    allineaTariffe();
    
    startTariffe.addEventListener('input', allineaTariffe);
  }
});