import * as fs from 'fs';

/**
 * Utility to escape strings for SQL and handle NULL values
 */
function sqlVal(value) {
  if (value === null || value === undefined) return 'NULL';
  if (typeof value === 'string') {
    // Escape single quotes by doubling them
    return `'${value.replace(/'/g, "''")}'`;
  }
  return value;
}

/**
 * Loads a JSON file and returns the array
 */
function loadJson(fileName) {
  return JSON.parse(fs.readFileSync(fileName, 'utf8'));
}

const sqlFile = 'seeding_spiaggia.sql';
// Initialize/Clear the file
fs.writeFileSync(sqlFile, '-- Beach Database Seeding Script\n\n');

/**
 * Helper to append INSERT statements to the file
 */
function writeInserts(tableName, columns, dataArray) {
  if (dataArray.length === 0) return;
  
  fs.appendFileSync(sqlFile, `-- Data for ${tableName}\n`);
  
  // We can group inserts for better performance (bulk insert)
  const batchSize = 1000;
  for (let i = 0; i < dataArray.length; i += batchSize) {
    const batch = dataArray.slice(i, i + batchSize);
    const values = batch.map(row => {
      const rowValues = columns.map(col => sqlVal(row[col]));
      return `(${rowValues.join(', ')})`;
    }).join(',\n');

    const query = `INSERT INTO ${tableName} (${columns.join(', ')}) VALUES\n${values};\n\n`;
    fs.appendFileSync(sqlFile, query);
  }
}

// 1. Independent entities (Foreign Key level 0)
console.log('Converting Customers...');
const customers = loadJson('customers_mock.json');
writeInserts('Cliente', ['codice', 'nome', 'cognome', 'dataNascita', 'indirizzo'], 
  customers.map(c => ({ codice: c.code, nome: c.firstName, cognome: c.lastName, dataNascita: c.birthDate, indirizzo: c.address })));

// 2. Tipologia (Static data)
console.log('Converting Types...');
// Assuming you have the umbrellaTypes array from previous steps
const types = [
  { codice: 'Standard1', nome: 'Standard 1', descrizione: 'Ombrellone classico con due lettini.' },
  { codice: 'Standard2', nome: 'Standard 2', descrizione: 'Ombrellone classico con due sdraio.' },
  { codice: 'Standard3', nome: 'Standard 3', descrizione: 'Ombrellone classico con due lettini e una sdraio.' },
  { codice: 'Standard4', nome: 'Standard 4', descrizione: 'Gazebo completo.' }
];
writeInserts('Tipologia', ['codice', 'nome', 'descrizione'], types);

// 3. Dependent entities (FK Level 1)
console.log('Converting Umbrellas and Tariffs...');
const umbrellas = loadJson('umbrellas_data.json');
writeInserts('Ombrellone', ['id', 'settore', 'numFila', 'numPostoFila', 'tipologia'], 
  umbrellas.map(u => ({ id: u.id, settore: u.id_settore, numFila: u.numFila, numPostoFila: u.numPostoFile, tipologia: u.tipologia })));

const tariffs = loadJson('tariffs_2025_2028.json');
writeInserts('Tariffa', ['codice', 'prezzo', 'dataInizio', 'dataFine', 'tipo', 'numMinGiorni'], tariffs);

// 4. Relationship tables
console.log('Converting Relations...');
const typeTariff = loadJson('tipologia_tariffa_2025_2028.json');
writeInserts('TipologiaTariffa', ['codTipologia', 'codTariffa'], typeTariff);

// 5. Transactional data (FK Level 2+)
console.log('Converting Contracts and Sales...');
const contracts = loadJson('final_contracts.json');
writeInserts('Contratto', ['numProgr', 'data', 'importo', 'stipulatoDa'], contracts);

const availability = loadJson('final_availability.json');
writeInserts('GiornoDisponibilita', ['idOmbrellone', 'data'], availability);

const sales = loadJson('final_sales.json');
writeInserts('OmbrelloneVenduto', ['idOmbrellone', 'data', 'contratto'], sales);

console.log(`Done! SQL seeding file generated: ${sqlFile}`);