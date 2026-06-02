document.addEventListener("DOMContentLoaded", () => {
  const formFiltri = document.getElementById('form-filtri-tariffe');
  const filtroInizio = document.getElementById('filtro-inizio');
  const filtroFine = document.getElementById('filtro-fine');

  // Sincronizzazione automatica e vincolo min nativo sulle date nei calendari
  if (filtroInizio && filtroFine) {
    filtroInizio.addEventListener('input', () => {
      filtroFine.min = filtroInizio.value;
      if (filtroFine.value && filtroFine.value < filtroInizio.value) {
        filtroFine.value = filtroInizio.value;
      }
    });
  }

  // Converte la data dal formato standard HTML (YYYY-MM-DD) al formato italiano (DD/MM/YYYY)
  const formattaData = (isoString) => {
    if (!isoString) return '-';
    const [year, month, day] = isoString.split('-');
    return `${day}/${month}/${year}`;
  };

  // Funzione asincrona core per il recupero dei dati strutturata sulla logica della reservation
  async function aggiornaPrezziTariffe() {
    // Estrae i valori effettivi correnti dai calendari input della sidebar
    // Se non ancora valorizzati, applica il fallback sicuro per la data odierna del sistema
    const dataInizioCorrente = filtroInizio && filtroInizio.value ? filtroInizio.value : new Date().toISOString().split('T')[0];
    const dataFineCorrente = filtroFine && filtroFine.value ? filtroFine.value : new Date().toISOString().split('T')[0];

    // Genera la stringa descrittiva temporale per il box della card
    let testoPeriodo = "";
    if (dataInizioCorrente === dataFineCorrente) {
      testoPeriodo = `Giorno: ${formattaData(dataInizioCorrente)}`;
    } else {
      testoPeriodo = `da: ${formattaData(dataInizioCorrente)} a: ${formattaData(dataFineCorrente)}`;
    }

    // Seleziona ed itera sulle 4 card delle tipologie
    const cards = document.querySelectorAll('.tariffa-card');

    for (const card of cards) {
      const tipo = card.dataset.tipo; // Recupera la tipologia ('Base', 'VIP', 'Gazebo', 'Disabile')
      const priceDisplay = card.querySelector('.prezzo-render');
      const dateRender = card.querySelector('.card-date-render');
      const prezzoBox = card.querySelector('.prezzo-valore');

      // Impostazione degli stati di caricamento asincrono nei nodi del DOM
      if (dateRender) dateRender.innerText = testoPeriodo;
      if (priceDisplay) priceDisplay.innerText = "...";
      if (prezzoBox) prezzoBox.style.opacity = '0.5';

      try {
        // Genera l'URL di chiamata puntando al corretto endpoint umbrella/get_price.php
        const urlChiamata = `../php/umbrella/get_price.php?tipo=${encodeURIComponent(tipo)}&inizio=${dataInizioCorrente}&fine=${dataFineCorrente}`;
        
        const response = await fetch(urlChiamata);
        const priceData = await response.json();

        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';

          // Estrazione diretta di priceData.totale ricalcolato, proprio come fa handlePopupDateChange()
          if (priceData && !priceData.error && priceData.totale !== undefined) {
            
            // Formattazione localizzata del prezzo in stringa monetaria italiana (es. 23.40 -> 23,40)
            const prezzoFormattato = parseFloat(priceData.totale).toLocaleString('it-IT', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });

            priceDisplay.innerText = prezzoFormattato;
          } else {
            // Fallback pulito se la query non trova righe corrispondenti per la tipologia nel DB
            console.warn(`[Tariffe] Nessun listino trovato per ${tipo}:`, priceData ? priceData.error : 'Dati assenti');
            priceDisplay.innerText = "0.00";
          }
        }

      } catch (error) {
        console.error(`[Tariffe] Errore di comunicazione durante la fetch per ${tipo}:`, error);
        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';
          priceDisplay.innerText = "0.00";
        }
      }
    }
  }

  // Esecuzione automatica al primo caricamento per visualizzare le tariffe odierne
  aggiornaPrezziTariffe();

  // Intercettazione globale dell'evento submit sul form filtri della sidebar ("Applica Filtri")
  if (formFiltri) {
    formFiltri.addEventListener('submit', (e) => {
      e.preventDefault(); // Blocca il reload della pagina nativo del browser
      aggiornaPrezziTariffe(); // Lancia il ricalcolo e aggiorna i costi a schermo
    });
  }
});