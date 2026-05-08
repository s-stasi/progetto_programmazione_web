// --- CONFIGURAZIONE PER TEST MASSIVO ---
const NUMERO_CLIENTI = 100;      // Più clienti per simulare realtà
const NUMERO_CONTRATTI = 500;   // Aumentiamo drasticamente le prenotazioni
const ANNI_DA_COPRIRE = [2025, 2026, 2027, 2028]; //

function generaDatiMassivi() {
    const contratti = [];
    const vendite = [];
    const disponibilita = [];

    for (let i = 1; i <= NUMERO_CONTRATTI; i++) {
        const idCliente = Math.floor(Math.random() * NUMERO_CLIENTI) + 1;
        const anno = ANNI_DA_COPRIRE[Math.floor(Math.random() * ANNI_DA_COPRIRE.length)];
        
        // Simuliamo prenotazioni estive (Alta Stagione)
        const meseStart = 6; // Luglio
        const giornoStart = Math.floor(Math.random() * 20) + 1;
        const durata = Math.floor(Math.random() * 14) + 1; // Prenotazioni da 1 a 15 giorni

        const dataInizio = new Date(anno, meseStart, giornoStart);
        const dataFine = new Date(dataInizio);
        dataFine.setDate(dataInizio.getDate() + durata);

        // Formattazione date YYYY-MM-DD
        const strInizio = dataInizio.toISOString().split('T')[0];
        const strFine = dataFine.setDate ? dataFine.toISOString().split('T')[0] : strInizio;

        const contratto = {
            id: i,
            idCliente: idCliente,
            dataInizio: strInizio,
            dataFine: strFine,
            importo: 0 // Calcolato poi dal sistema o lasciato a 0 per test
        };
        contratti.push(contratto);

        // --- ASSEGNAZIONE OMBRELLI (Coinvolgimento multiplo) ---
        // Assegniamo da 1 a 3 ombrelloni per ogni contratto per riempire la spiaggia
        const quantiOmbrelloni = Math.floor(Math.random() * 3) + 1;
        for (let j = 0; j < quantiOmbrelloni; j++) {
            const idOmbrellone = Math.floor(Math.random() * 1000) + 1; //
            
            vendite.push({
                idContratto: i,
                idOmbrellone: idOmbrellone
            });

            // Popoliamo la tabella giorno_disponibilita per ogni singolo giorno della prenotazione
            let dataCorrente = new Date(dataInizio);
            while (dataCorrente <= dataFine) {
                disponibilita.push({
                    idOmbrellone: idOmbrellone,
                    data: dataCorrente.toISOString().split('T')[0]
                });
                dataCorrente.setDate(dataCorrente.getDate() + 1);
            }
        }
    }
    return { contratti, vendite, disponibilita };
}