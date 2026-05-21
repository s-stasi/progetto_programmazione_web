import * as fs from 'fs';

const umbrellaTypes = ["Base", "VIP", "Gazebo", "Disabile"];

const seasons = [
  { name: "Bassa", startDay: "01-01", endDay: "03-31", multiplier: 0.7 },
  { name: "Media", startDay: "04-01", endDay: "05-31", multiplier: 1.2 },
  { name: "Alta",  startDay: "06-01", endDay: "09-30", multiplier: 2.2 },
  { name: "Media", startDay: "10-01", endDay: "10-31", multiplier: 1.2 },
  { name: "Bassa", startDay: "11-01", endDay: "12-31", multiplier: 0.7 }
];

const basePrices = {
  "Base": 18.00,      
  "VIP": 23.00,       
  "Gazebo": 35.00,    
  "Disabile": 20.00   
};

function generateMultiYearTariffsAndRelations(startYear, endYear) {
  const tariffs = [];
  const typeTariffRelations = [];

  for (let year = startYear; year <= endYear; year++) {
    for (const type of umbrellaTypes) {
      const basePriceFisso = basePrices[type];

      for (const season of seasons) {
        const finalDailyPrice = basePriceFisso * season.multiplier;
        const startDate = `${year}-${season.startDay}`;
        const endDate = `${year}-${season.endDay}`;

        // 1. TARIFFA GIORNALIERA (Prezzo Pieno, Nessuno Sconto)
        const dailyCode = `TAR-${type}-${season.name}-${year}-D`;
        tariffs.push({ 
          codice: dailyCode, 
          prezzo: Number(finalDailyPrice.toFixed(2)),
          dataInizio: startDate, 
          dataFine: endDate, 
          tipo: 'Giornaliera', 
          numMinGiorni: 0
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: dailyCode });

        // 2. ABBONAMENTO 1-7 GIORNI (Sconto 7%)
        const shortCode = `TAR-${type}-${season.name}-${year}-SHORT`;
        const priceShort = finalDailyPrice * 0.93;
        tariffs.push({ 
          codice: shortCode, 
          prezzo: Number(priceShort.toFixed(2)), 
          dataInizio: startDate, 
          dataFine: endDate, 
          tipo: 'Abbonamento', 
          numMinGiorni: 1 
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: shortCode });

        // 3. ABBONAMENTO 8-20 GIORNI (Sconto 10%)
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

        // 4. ABBONAMENTO LUNGO > 20 GIORNI (Sconto 15%)
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

const data = generateMultiYearTariffsAndRelations(2025, 2028);
fs.writeFileSync('tariffs_aggiornate.json', JSON.stringify(data.tariffs, null, 2));
fs.writeFileSync('tipologia_tariffa_aggiornata.json', JSON.stringify(data.typeTariffRelations, null, 2));

console.log("Generazione completata! Creati i 4 scaglioni (Giornaliera Piena, 1-7 scontata, 8-20 scontata, 21+ scontata).");