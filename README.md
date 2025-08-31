# Customer Management System

A comprehensive web application for managing customers and employees with role-based access control.

## Features

- User authentication (login/logout)
- Role-based access control (Admin, Employee)
- Customer management (CRUD operations)
- Employee management (Admin only)
- Responsive design
- Secure password hashing
- Form validation

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (for dependency management)

## Installation

1. **Clone the repository**
   ```
   git clone [repository-url]
   cd customer-management-system
   ```

2. **Create a MySQL database**
   ```sql
   CREATE DATABASE customer_management;
   ```

3. **Import the database schema**
   - Open phpMyAdmin or your preferred MySQL client
   - Import the `database/schema.sql` file

4. **Configure the database connection**
   Edit `config/database.php` with your database credentials:
   ```php
   private $host = "localhost";
   private $db_name = "customer_management";
   private $username = "your_username";
   private $password = "your_password";
   ```

5. **Set up the web server**
   - Point your web server's document root to the `public` directory
   - Ensure `mod_rewrite` is enabled (for Apache)
   - Set proper file permissions

6. **Access the application**
   - Open your browser and navigate to `http://localhost`
   - Login with the default admin credentials:
     - Username: `admin`
     - Password: `admin123`

## Default Accounts

### Admin
- **Username:** admin
- **Password:** admin123

## Directory Structure

```
/
├── assets/                 # Static assets
│   └── css/                # Stylesheets
├── config/                 # Configuration files
│   └── database.php        # Database configuration
├── database/               # Database files
│   └── schema.sql          # Database schema
├── includes/               # Included PHP files
├── public/                 # Publicly accessible files
│   ├── index.php           # Entry point
│   └── .htaccess          # Apache configuration
└── src/                    # Source files
    ├── controllers/        # Controller classes
    ├── models/             # Model classes
    └── views/              # View templates
```

## Security

- All passwords are hashed using PHP's `password_hash()`
- Prepared statements are used to prevent SQL injection
- Input validation on all forms
- Session management with proper timeouts
- CSRF protection on forms
- Output escaping to prevent XSS

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please contact [your-email@example.com] or open an issue in the repository.




