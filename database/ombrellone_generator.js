import { faker } from '@faker-js/faker';
import * as fs from 'fs';

faker.seed(123);

// The types provided in your request
const umbrellaTypes = [
  { "codice": "Base", "nome": "Ombrellone Base", "descrizione": "Ombrellone + 2 lettini." },
  { "codice": "VIP", "nome": "Ombrellone VIP", "descrizione": "Fila fronte mare con ombrellone + due lettini." },
  { "codice": "Gazebo", "nome": "Gazebo Executive", "descrizione": "Gazebo con privacy." },
  { "codice": "Disabile", "nome": "Gazebo Disabili", "descrizione": "Accesso facilitato passerella." }
];

/**
 * Generates a full beach layout based on sectors, rows, and seats.
 */
function generateUmbrellas() {
  const umbrellas = [];
  const sectorsCount = 5;
  const rowsCount = 10;   // <-- Ridotto a 10 file
  const seatsPerRow = 20; // <-- Ridotto a 20 posti

  let currentId = 1;

  for (let s = 1; s <= sectorsCount; s++) {
    for (let r = 1; r <= rowsCount; r++) {
      for (let p = 1; p <= seatsPerRow; p++) {


        let tipoAssegnato = "Base"; // Default: Standard 

        // the first 2 rows of sectors A,B,C are VIP
        if (s <= 3 && r <= 2) {
          tipoAssegnato = "VIP";
        }
        // the last 2 rows of sectors D,E are GAZEBO
        if (s >= 4 && r >= 9) {
          tipoAssegnato = "Gazebo";
        }

        if (r === 10 && (p === 10 || p === 11)) tipoAssegnato = "Disabile";

        umbrellas.push({
          id: currentId++,
          id_settore: s,
          numFila: r,
          numPostoFile: p,
          tipologia: tipoAssegnato
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