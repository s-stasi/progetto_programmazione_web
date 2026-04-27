import { faker } from '@faker-js/faker';
import * as fs from 'fs';

faker.seed(123);

// The types provided in your request
const umbrellaTypes = [
  { "codice": "Standard1", "nome": "Standard 1", "descrizione": "Ombrellone classico con due lettini." },
  { "codice": "Standard2", "nome": "Standard 2", "descrizione": "Ombrellone classico con due sdraio." },
  { "codice": "Standard3", "nome": "Standard 3", "descrizione": "Ombrellone classico con due lettini e una sdraio." },
  { "codice": "Standard4", "nome": "Standard 4", "descrizione": "Gazebo completo." }
];

/**
 * Generates a full beach layout based on sectors, rows, and seats.
 */
function generateUmbrellas() {
  const umbrellas = [];
  const sectorsCount = 5;
  const rowsCount = 15;
  const seatsPerRow = 30;
  
  let currentId = 1;

  for (let s = 1; s <= sectorsCount; s++) {
    for (let r = 1; r <= rowsCount; r++) {
      for (let p = 1; p <= seatsPerRow; p++) {
        // Randomly assign one of the four types
        const randomType = faker.helpers.arrayElement(umbrellaTypes);

        umbrellas.push({
          id: currentId++,
          id_settore: s,
          numFila: r,
          numPostoFile: p,
          tipologia: randomType.codice
        });
      }
    }
  }
  
  return umbrellas;
}

const beachData = generateUmbrellas();

// Saving to JSON file
try {
  fs.writeFileSync('umbrellas_data.json', JSON.stringify(beachData, null, 2));
  console.log(`Successfully generated ${beachData.length} umbrellas.`);
  console.log('File saved as umbrellas_data.json');
} catch (error) {
  console.error('Error writing the file:', error);
}