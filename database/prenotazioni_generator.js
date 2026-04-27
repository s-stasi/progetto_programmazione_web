import { faker } from '@faker-js/faker';
import * as fs from 'fs';

faker.seed(123);

/**
 * Utility function to safely load and parse JSON files
 */
function loadJsonFile(fileName) {
  try {
    const data = fs.readFileSync(fileName, 'utf8');
    return JSON.parse(data);
  } catch (error) {
    console.error(`Error reading ${fileName}:`, error);
    process.exit(1);
  }
}

// Loading existing datasets
const customers = loadJsonFile('customers_mock.json');
const umbrellas = loadJsonFile('umbrellas_data.json');
const tariffs = loadJsonFile('tariffs_2025_2028.json');

const CONTRACTS_TO_GENERATE = 5000;

function generateTransactionsWithIntegrity() {
  const contracts = [];
  const availabilityDays = [];
  const umbrellasSold = [];
  const occupiedSlots = new Set();
  
  let currentContractId = 1;

  for (let i = 0; i < CONTRACTS_TO_GENERATE; i++) {
    // Pick real entities from the loaded files
    const randomCustomer = faker.helpers.arrayElement(customers);
    const randomUmbrella = faker.helpers.arrayElement(umbrellas);
    
    // Pick a tariff compatible with the umbrella's type
    const compatibleTariffs = tariffs.filter(t => t.codice.includes(randomUmbrella.tipologia));
    if (compatibleTariffs.length === 0) continue;
    
    const selectedTariff = faker.helpers.arrayElement(compatibleTariffs);
    
    // Logic for booking duration based on the selected tariff
    const duration = selectedTariff.tipo === 'Giornaliera' ? 1 : (selectedTariff.numMinGiorni || 7);
    const startDate = new Date(selectedTariff.dataInizio);
    
    // Check if the umbrella is free for the duration of the tariff
    let canBook = true;
    const bookingDates = [];

    for (let d = 0; d < duration; d++) {
      const currentDate = new Date(startDate);
      currentDate.setDate(currentDate.getDate() + d);
      const dateStr = currentDate.toISOString().split('T')[0];
      
      if (occupiedSlots.has(`${randomUmbrella.id}_${dateStr}`)) {
        canBook = false;
        break;
      }
      bookingDates.push(dateStr);
    }

    if (canBook) {
      // Create the Contract
      contracts.push({
        numProgr: currentContractId,
        data: selectedTariff.dataInizio, // Contract signed at the start of season/tariff
        importo: selectedTariff.prezzo,
        stipulatoDa: randomCustomer.code // Using the real Primary Key from JSON
      });

      for (const dateStr of bookingDates) {
        occupiedSlots.add(`${randomUmbrella.id}_${dateStr}`);
        
        availabilityDays.push({
          idOmbrellone: randomUmbrella.id,
          data: dateStr
        });

        umbrellasSold.push({
          idOmbrellone: randomUmbrella.id,
          data: dateStr,
          contratto: currentContractId
        });
      }
      currentContractId++;
    }
  }

  return { contracts, availabilityDays, umbrellasSold };
}

const output = generateTransactionsWithIntegrity();

// Saving finalized transaction data
fs.writeFileSync('final_contracts.json', JSON.stringify(output.contracts, null, 2));
fs.writeFileSync('final_availability.json', JSON.stringify(output.availabilityDays, null, 2));
fs.writeFileSync('final_sales.json', JSON.stringify(output.umbrellasSold, null, 2));

console.log(`Generated ${output.contracts.length} consistent contracts referencing existing IDs.`);