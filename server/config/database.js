import mysql from 'mysql2/promise'
import dotenv from 'dotenv'

dotenv.config()

const dbConfig = {
  host: process.env.DB_HOST || 'localhost',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'customer_management',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
  acquireTimeout: 60000,
  timeout: 60000,
  reconnect: true
}

export const pool = mysql.createPool(dbConfig)

export const initializeDatabase = async () => {
  try {
    // Test connection
    const connection = await pool.getConnection()
    console.log('Database connected successfully')
    connection.release()

    // Create tables if they don't exist
    await createTables()
    console.log('Database tables initialized')
  } catch (error) {
    console.error('Database initialization failed:', error)
    throw error
  }
}

const createTables = async () => {
  const connection = await pool.getConnection()
  
  try {
    // Create roles table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `)

    // Create users table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (role_id) REFERENCES roles(id)
      )
    `)

    // Create customers table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(20) NOT NULL,
        address TEXT,
        city VARCHAR(50),
        state VARCHAR(50),
        postal_code VARCHAR(20),
        country VARCHAR(50) DEFAULT 'Sri Lanka',
        business_name VARCHAR(100) NOT NULL,
        business_type VARCHAR(50),
        business_reg_number VARCHAR(50),
        tin_number VARCHAR(50),
        vat_number VARCHAR(50),
        activities TEXT,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `)

    // Create customer_documents table
    await connection.execute(`
      CREATE TABLE IF NOT EXISTS customer_documents (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        document_type VARCHAR(50) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        file_size INT NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    `)

    // Insert default roles
    await connection.execute(`
      INSERT IGNORE INTO roles (name, description) VALUES 
      ('admin', 'Administrator with full access'),
      ('employee', 'Regular employee with limited access'),
      ('customer', 'Customer with view-only access')
    `)

    // Insert default admin user (password: admin123)
    const hashedPassword = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    await connection.execute(`
      INSERT IGNORE INTO users (username, email, password_hash, first_name, last_name, role_id) VALUES
      ('admin', 'admin@example.com', ?, 'Admin', 'User', 1)
    `, [hashedPassword])

  } finally {
    connection.release()
  }
}
