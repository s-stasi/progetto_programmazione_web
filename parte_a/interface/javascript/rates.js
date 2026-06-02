document.addEventListener("DOMContentLoaded", () => {
  const formFiltri = document.getElementById('form-filtri-tariffe');
  const filtroInizio = document.getElementById('sidebar-tariffe-da');
  const filtroFine = document.getElementById('sidebar-tariffe-a');

  const formattaData = (isoString) => {
    if (!isoString) return '-';
    const [year, month, day] = isoString.split('-');
    return `${day}/${month}/${year}`;
  };

  function gestisciBottoneReset(mostra) {
    if (!formFiltri) return;
    
    // Controlla se il link esiste già nel DOM
    let resetBtn = formFiltri.querySelector('.js-reset-filtri');

    if (mostra) {
      if (!resetBtn) {
        resetBtn = document.createElement('a');
        resetBtn.className = 'js-reset-filtri';
        resetBtn.href = 'rates.php';
        resetBtn.innerText = 'Resetta Filtri';
        resetBtn.style.cssText = "text-align: center; margin-top: 15px; display: block; color: var(--text-muted, #888); font-size: 13px; text-decoration: none;";
        
        resetBtn.addEventListener('click', (e) => {
          e.preventDefault();
          
          const oggi = new Date().toISOString().split('T')[0];
          if (filtroInizio) filtroInizio.value = oggi;
          if (filtroFine) filtroFine.value = oggi;
          
          // Pulisce l'URL del browser
          history.pushState({}, '', 'rates.php');
          
          aggiornaPrezziTariffe();
          gestisciBottoneReset(false);
        });

        formFiltri.appendChild(resetBtn);
      }
    } else {
      if (resetBtn) {
        resetBtn.remove();
      }
    }
  }

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
      const receiptBox = card.querySelector('.card-receipt');

      if (dateRender) dateRender.innerText = testoPeriodo;
      if (priceDisplay) priceDisplay.innerText = "...";
      if (prezzoBox) prezzoBox.style.opacity = '0.5';
      if (receiptBox) receiptBox.style.display = 'none';

      try {
        const priceUrl = `../php/umbrella/get_rates.php?tipo=${encodeURIComponent(tipo)}&inizio=${dataInizio}&fine=${dataFine}`;
        const priceResponse = await fetch(priceUrl);
        const priceData = await priceResponse.json();

        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';

          if (priceData && priceData.success && priceData.totale !== undefined) {
            const prezzoFormattato = parseFloat(priceData.totale).toLocaleString('it-IT', {
              minimumFractionDigits: 2,
              maximumFractionDigits: 2
            });
            priceDisplay.innerText = prezzoFormattato;

            if (receiptBox) {
              const tariffaInteraTotale = (parseFloat(priceData.prezzo_lordo_unitario) * priceData.giorni).toFixed(2);
              const valoreSconto = (tariffaInteraTotale - priceData.totale).toFixed(2);
              const labelGiorni = priceData.giorni === 1 ? "1 giorno selezionato" : `${priceData.giorni} giorni selezionati`;

              receiptBox.innerHTML = `
              <div class="receipt-line receipt-bold receipt-period-highlight">
                <span>PERIODO:</span><span>${labelGiorni}</span>
              </div>
              <div class="receipt-line"><span>Prezzo Listino:</span><span>€ ${parseFloat(priceData.prezzo_lordo_unitario).toFixed(2)}/gg</span></div>
              <div class="receipt-line"><span>Tariffa Base:</span><span>€ ${parseFloat(tariffaInteraTotale).toLocaleString('it-IT', { minimumFractionDigits: 2 })}</span></div>
              <div class="receipt-line receipt-discount-red"><span>Sconto Applicato (${priceData.sconto}):</span><span>- € ${valoreSconto}</span></div>
              `;
              receiptBox.style.display = 'block';
            }

          } else {
            priceDisplay.innerText = "0.00";
            if (receiptBox) receiptBox.style.display = 'none';
          }
        }

      } catch (err) {
        console.error(`Errore fetch per ${tipo}:`, err);
        if (priceDisplay) {
          if (prezzoBox) prezzoBox.style.opacity = '1';
          priceDisplay.innerText = "0.00";
        }
      }
    }
  }

  const urlParams = new URLSearchParams(window.location.search);
  if (urlParams.has('data_da') || urlParams.has('data_a')) {
    gestisciBottoneReset(true);
  }

  aggiornaPrezziTariffe();

  if (formFiltri) {
    formFiltri.addEventListener('submit', (e) => {
      e.preventDefault(); 
      
      const dataInizio = filtroInizio ? filtroInizio.value : '';
      const dataFine = filtroFine ? filtroFine.value : '';

      const nuovoUrl = `rates.php?data_da=${encodeURIComponent(dataInizio)}&data_a=${encodeURIComponent(dataFine)}`;
      history.pushState({}, '', nuovoUrl);

      aggiornaPrezziTariffe();

      gestisciBottoneReset(true);
    });
  }
});