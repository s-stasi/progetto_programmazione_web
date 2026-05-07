import * as fs from 'fs';

const umbrellaTypes = ["Base", "VIP", "Gazebo", "Disabile"];

const seasons = [
  { name: "Low", startDay: "05-01", endDay: "06-15", multiplier: 1 },
  { name: "Mid", startDay: "06-16", endDay: "07-31", multiplier: 1.5 },
  { name: "High", startDay: "08-01", endDay: "08-31", multiplier: 2.2 }
];

const basePrices2025 = {
  "Base": 18.00,
  "VIP": 25.00,
  "Gazebo": 60.00,
  "Disabile": 40.00
};

function generateMultiYearTariffsAndRelations(startYear, endYear) {
  const tariffs = [];
  const typeTariffRelations = [];
  const annualInflationRate = 1.03;

  for (let year = startYear; year <= endYear; year++) {
    const inflationMultiplier = Math.pow(annualInflationRate, year - startYear);

    for (const type of umbrellaTypes) {
      const basePrice = basePrices2025[type] * inflationMultiplier;

      for (const season of seasons) {
        const finalDailyPrice = basePrice * season.multiplier;
        const startDate = `${year}-${season.startDay}`;
        const endDate = `${year}-${season.endDay}`;

        const dailyCode = `TAR-${type}-${season.name}-${year}-D`;
        tariffs.push({ codice: dailyCode, prezzo: Number(finalDailyPrice.toFixed(2)), dataInizio: startDate, dataFine: endDate, tipo: 'Giornaliera', numMinGiorni: null });
        typeTariffRelations.push({ codTipologia: type, codTariffa: dailyCode });

        const weeklyCode = `TAR-${type}-${season.name}-${year}-W`;
        tariffs.push({ codice: weeklyCode, prezzo: Number((finalDailyPrice * 6).toFixed(2)), dataInizio: startDate, dataFine: endDate, tipo: 'Abbonamento', numMinGiorni: 7 });
        typeTariffRelations.push({ codTipologia: type, codTariffa: weeklyCode });

        const monthlyCode = `TAR-${type}-${season.name}-${year}-M`;
        tariffs.push({ codice: monthlyCode, prezzo: Number((finalDailyPrice * 22).toFixed(2)), dataInizio: startDate, dataFine: endDate, tipo: 'Abbonamento', numMinGiorni: 30 });
        typeTariffRelations.push({ codTipologia: type, codTariffa: monthlyCode });
      }
    }
  }
  return { tariffs, typeTariffRelations };
}

const data = generateMultiYearTariffsAndRelations(2025, 2028);
fs.writeFileSync('tariffs_2025_2028.json', JSON.stringify(data.tariffs, null, 2));
fs.writeFileSync('tipologia_tariffa_2025_2028.json', JSON.stringify(data.typeTariffRelations, null, 2));
console.log("Tariffe aggiornate!");