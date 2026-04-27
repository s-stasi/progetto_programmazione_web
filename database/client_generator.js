import { faker } from '@faker-js/faker';
import * as fs from 'fs';

faker.seed(123);

// Class to represent a customer matching the DB schema
class Customer {
  constructor(code, firstName, lastName, address, birthDate) {
    this.code = code;
    this.firstName = firstName;
    this.lastName = lastName;
    this.address = address;
    this.birthDate = birthDate;
  }
}

const customersList = [];
const recordsToGenerate = 100000;

for (let i = 0; i < recordsToGenerate; i++) {
  // Generate a JS Date object
  const rawDate = faker.date.between({ 
    from: '1930-01-01T00:00:00.000Z', 
    to: '2004-01-01T00:00:00.000Z' 
  });
  
  // Format the date to 'YYYY-MM-DD' for SQL compatibility
  const formattedDate = rawDate.toISOString().split('T')[0];

  // Create the new customer instance
  const newCustomer = new Customer(
    i + 1,
    faker.person.firstName(),
    faker.person.lastName(),
    faker.location.streetAddress(),
    formattedDate
  );
  
  // Add to the array
  customersList.push(newCustomer);
}

// Write the entire array to a JSON file instead of printing to console
// The '2' parameter adds two-space indentation to the output JSON
fs.writeFileSync('customers_mock.json', JSON.stringify(customersList, null, 2));

console.log(`Successfully generated ${recordsToGenerate} customers and saved to customers_mock.json`);