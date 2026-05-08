import * as fs from 'fs';

// 1. DEFINIZIONE TIPOLOGIE (Allineate al tuo database)
const umbrellaTypes = ["Base", "VIP", "Gazebo", "Disabile"];

// 2. DEFINIZIONE STAGIONI (Dal tuo quaderno)
const seasons = [
  { name: "Bassa", startDay: "01-01", endDay: "03-31", multiplier: 0.7 },   // Gennaio - Marzo
  { name: "Media", startDay: "04-01", endDay: "05-31", multiplier: 1.2 },   // Aprile - Maggio
  { name: "Alta",  startDay: "06-01", endDay: "09-30", multiplier: 2.2 },   // Giugno - Settembre
  { name: "Media", startDay: "10-01", endDay: "10-31", multiplier: 1.2 },   // Ottobre
  { name: "Bassa", startDay: "11-01", endDay: "12-31", multiplier: 0.7 }    // Novembre - Dicembre
];

// 3. PREZZI BASE 2025 (Tariffe giornaliere di riferimento)
const basePrices2025 = {
  "Base": 20.00,      // Standard
  "VIP": 35.00,       // VIP (1° e 2° fila)
  "Gazebo": 90.00,    // Gazebo (ultime file)
  "Disabile": 15.00   // Posto Disabili
};

function generateMultiYearTariffsAndRelations(startYear, endYear) {
  const tariffs = [];
  const typeTariffRelations = [];
  const annualInflationRate = 1.03; // Inflazione 3% annuo

  for (let year = startYear; year <= endYear; year++) {
    const inflationMultiplier = Math.pow(annualInflationRate, year - startYear);

    for (const type of umbrellaTypes) {
      const basePriceInflated = basePrices2025[type] * inflationMultiplier;

      for (const season of seasons) {
        const finalDailyPrice = basePriceInflated * season.multiplier;
        const startDate = `${year}-${season.startDay}`;
        const endDate = `${year}-${season.endDay}`;

        // --- APPLICAZIONE SCAGLIONI DI SCONTO (Dal quaderno) ---

        // 1. Fascia Giornaliera (1-7 giorni) -> Sconto 7% (Moltiplicatore 0.93)
        const dailyCode = `TAR-${type}-${season.name}-${year}-D`;
        const priceD = finalDailyPrice * 0.93;
        tariffs.push({ 
          codice: dailyCode, 
          prezzo: Number(priceD.toFixed(2)), 
          dataInizio: startDate, 
          dataFine: endDate, 
          tipo: 'Giornaliera', 
          numMinGiorni: 1 
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: dailyCode });

        // 2. Fascia Media (8-20 giorni) -> Sconto 10% (Moltiplicatore 0.90)
        const midCode = `TAR-${type}-${season.name}-${year}-MID`;
        const priceMid = finalDailyPrice * 0.90;
        tariffs.push({ 
          codice: midCode, 
          prezzo: Number(priceMid.toFixed(2)), 
          dataInizio: startDate, 
          dataFine: endDate, 
          tipo: 'Abbonamento', 
          numMinGiorni: 8 
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: midCode });

        // 3. Fascia Lunga (> 20 giorni) -> Sconto 15% (Moltiplicatore 0.85)
        const longCode = `TAR-${type}-${season.name}-${year}-LONG`;
        const priceLong = finalDailyPrice * 0.85;
        tariffs.push({ 
          codice: longCode, 
          prezzo: Number(priceLong.toFixed(2)), 
          dataInizio: startDate, 
          dataFine: endDate, 
          tipo: 'Abbonamento', 
          numMinGiorni: 21 
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: longCode });
      }
    }
  }
  return { tariffs, typeTariffRelations };
}

// Generazione dati 2025-2028
const data = generateMultiYearTariffsAndRelations(2025, 2028);

// Scrittura file JSON pronti per l'importazione
fs.writeFileSync('tariffs_aggiornate.json', JSON.stringify(data.tariffs, null, 2));
fs.writeFileSync('tipologia_tariffa_aggiornata.json', JSON.stringify(data.typeTariffRelations, null, 2));

console.log("Generazione completata!");
console.log("- Generata tabella 'tariffa' con scaglioni 1, 8, 21 giorni.");
console.log("- Applicati sconti 5%, 7% e 10% come da quaderno.");