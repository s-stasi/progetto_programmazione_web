import * as fs from 'fs';

// The umbrella types we defined earlier
const umbrellaTypes = ["Standard1", "Standard2", "Standard3", "Standard4"];

// Defining seasons using only month and day to allow dynamic year injection
const seasons = [
  { name: "Low", startDay: "05-01", endDay: "06-15", multiplier: 1 },
  { name: "Mid", startDay: "06-16", endDay: "07-31", multiplier: 1.5 },
  { name: "High", startDay: "08-01", endDay: "08-31", multiplier: 2.2 }
];

// Base daily prices for the starting year (2025)
const basePrices2025 = {
  "Standard1": 15.00,
  "Standard2": 18.00,
  "Standard3": 22.00,
  "Standard4": 45.00
};

/**
 * Generates Tariffs and the N:M relation table TipologiaTariffa spanning multiple years
 */
function generateMultiYearTariffsAndRelations(startYear, endYear) {
  const tariffs = [];
  const typeTariffRelations = [];
  const annualInflationRate = 1.03; // 3% price increase per year

  for (let year = startYear; year <= endYear; year++) {
    // Calculate the inflation multiplier relative to the base year (2025)
    const inflationMultiplier = Math.pow(annualInflationRate, year - startYear);

    for (const type of umbrellaTypes) {
      for (const season of seasons) {
        // Calculate the base daily price for this year and season
        const currentYearBasePrice = basePrices2025[type] * inflationMultiplier;
        const finalDailyPrice = currentYearBasePrice * season.multiplier;

        // Construct dynamic start and end dates
        const startDate = `${year}-${season.startDay}`;
        const endDate = `${year}-${season.endDay}`;

        // 1. Daily Tariff (Giornaliera)
        // Included the year in the code to ensure the Primary Key is unique
        const dailyCode = `TAR-${type}-${season.name}-${year}-D`;
        tariffs.push({
          codice: dailyCode,
          prezzo: Number(finalDailyPrice.toFixed(2)),
          dataInizio: startDate,
          dataFine: endDate,
          tipo: 'Giornaliera',
          numMinGiorni: null
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: dailyCode });

        // 2. Weekly Subscription (Abbonamento - 7 days)
        const weeklyCode = `TAR-${type}-${season.name}-${year}-W`;
        tariffs.push({
          codice: weeklyCode,
          prezzo: Number((finalDailyPrice * 6).toFixed(2)), // 6 days price for 7 days access
          dataInizio: startDate,
          dataFine: endDate,
          tipo: 'Abbonamento',
          numMinGiorni: 7
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: weeklyCode });

        // 3. Monthly Subscription (Abbonamento - 30 days)
        const monthlyCode = `TAR-${type}-${season.name}-${year}-M`;
        tariffs.push({
          codice: monthlyCode,
          prezzo: Number((finalDailyPrice * 22).toFixed(2)), // 22 days price for full month
          dataInizio: startDate,
          dataFine: endDate,
          tipo: 'Abbonamento',
          numMinGiorni: 30
        });
        typeTariffRelations.push({ codTipologia: type, codTariffa: monthlyCode });
      }
    }
  }

  return { tariffs, typeTariffRelations };
}

// Generate data from 2025 to 2028
const data = generateMultiYearTariffsAndRelations(2025, 2028);

// Exporting to JSON files
try {
  fs.writeFileSync('tariffs_2025_2028.json', JSON.stringify(data.tariffs, null, 2));
  fs.writeFileSync('tipologia_tariffa_2025_2028.json', JSON.stringify(data.typeTariffRelations, null, 2));
  
  console.log(`Successfully generated ${data.tariffs.length} total tariffs across 4 years.`);
  console.log(`Successfully generated ${data.typeTariffRelations.length} N:M relations.`);
} catch (error) {
  console.error('Error saving files:', error);
}