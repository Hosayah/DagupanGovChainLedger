const mysql = require('mysql2');

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',        // change to your MySQL user
  password: '',        // change to your MySQL password
  database: 'govledger' // create this database in MySQL
});

db.connect(err => {
  if (err) throw err;
  console.log('✅ MySQL Connected...');
});

module.exports = db;
