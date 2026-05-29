import { fakerIT as faker } from '@faker-js/faker';
import * as fs from 'fs';

faker.seed(123);

// Class to represent a customer matching the DB schema
class Customer {
  constructor(code, firstName, lastName, address, birthDate, email, telefono) {
    this.code = code;
    this.firstName = firstName;
    this.lastName = lastName;
    this.address = address;
    this.birthDate = birthDate;
    this.email = email;
    this.telefono = telefono;
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
  
  const formattedDate = rawDate.toISOString().split('T')[0];

  const firstName = faker.person.firstName();
  const lastName = faker.person.lastName();

  const email = faker.internet.email({ firstName: firstName, lastName: lastName });

  const newCustomer = new Customer(
    i + 1,
    firstName,
    lastName,
    faker.location.streetAddress(),
    formattedDate,
    email,
    faker.phone.number('+39 (0)### ### ###')
  );
  
  customersList.push(newCustomer);
}

fs.writeFileSync('customers_mock.json', JSON.stringify(customersList, null, 2));

console.log(`Successfully generated ${recordsToGenerate} customers and saved to customers_mock.json`);