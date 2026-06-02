document.addEventListener("DOMContentLoaded", () => {
  const formFiltri = document.getElementById('form-filtri-tariffe');
  const filtroInizio = document.getElementById('filtro-inizio');
  const filtroFine = document.getElementById('filtro-fine');

  if (filtroInizio && filtroFine) {
    filtroInizio.addEventListener('input', () => {
      filtroFine.min = filtroInizio.value;
      if (filtroFine.value && filtroFine.value < filtroInizio.value) {
        filtroFine.value = filtroInizio.value;
      }
    });
  }

  const formattaData = (isoString) => {
    if (!isoString) return '-';
    const [year, month, day] = isoString.split('-');
    return `${day}/${month}/${year}`;
  };

  async function aggiornaPrezziTariffe() {
    const dataInizio = filtroInizio && filtroInizio.value ? filtroInizio.value : '2026-06-02';
    const dataFine = filtroFine && filtroFine.value ? filtroFine.value : '2026-06-02';

    let testoPeriodo = "";
    if (dataInizio === dataFine) {
      testoPeriodo = `Giorno: ${formattaData(dataInizio)}`;
    } else {
      testoPeriodo = `da: ${formattaData(dataInizio)} a: ${formattaData(dataFine)}`;
    }

    const cards = document.querySelectorAll('.tariffa-card');

    for (const card of cards) {
      const tipo = card.dataset.tipo; 
      const priceDisplay = card.querySelector('.prezzo-render');
      const dateRender = card.querySelector('.card-date-render');
      const prezzoBox = card.querySelector('.prezzo-valore');

      if (dateRender) dateRender.innerText = testoPeriodo;
      if (priceDisplay) priceDisplay.innerText = "...";
      if (prezzoBox) prezzoBox.style.opacity = '0.5';

      try {
        // PUNTA AL TUO NUOVO FILE GET_RATES.PHP
        const priceUrl = `../php/umbrella/get_rates.php?tipo=${encodeURIComponent(tipo)}&inizio=${dataInizio}&fine=${dataFine}`;
        const priceResponse = await fetch(priceUrl);
        const priceData = await priceResponse.json();

        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';

          // Verifica se il tuo file get_rates ha risposto con successo
          if (priceData && priceData.success && priceData.totale !== undefined) {
            const prezzoFormattato = parseFloat(priceData.totale).toLocaleString('it-IT', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });
            priceDisplay.innerText = prezzoFormattato;
          } else {
            priceDisplay.innerText = "0.00";
          }
        }

      } catch (err) {
        console.error(`Errror fetch per ${tipo}:`, err);
        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';
          priceDisplay.innerText = "0.00";
        }
      }
    }
  }

  // Esecuzione immediata dei calcoli all'avvio della pagina
  aggiornaPrezziTariffe();

  if (filtroInizio) filtroInizio.addEventListener('change', aggiornaPrezziTariffe);
  if (filtroFine) filtroFine.addEventListener('change', aggiornaPrezziTariffe);

  if (formFiltri) {
    formFiltri.addEventListener('submit', (e) => {
      e.preventDefault();
      aggiornaPrezziTariffe();
    });
  }
});