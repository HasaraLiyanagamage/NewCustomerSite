<<<<<<< HEAD
# Customer Management System - React Edition

A modern, full-stack customer management system built with React, TypeScript, Node.js, and MySQL. This application provides comprehensive customer and employee management features with role-based access control.

## Features

### 🎯 Core Features
- **User Authentication**: Secure login/logout with JWT tokens
- **Role-Based Access Control**: Admin and Employee roles with different permissions
- **Customer Management**: Full CRUD operations for customer records
- **Employee Management**: Admin-only employee management
- **Dashboard**: Statistics and recent activity overview
- **Profile Management**: User profile and password management
- **Public Customer Registration**: Self-service customer registration

### 🎨 Modern UI/UX
- **Responsive Design**: Works on desktop, tablet, and mobile
- **Modern Interface**: Clean, professional design with Tailwind CSS
- **Interactive Components**: Smooth animations and transitions
- **Form Validation**: Real-time validation with helpful error messages
- **Toast Notifications**: User-friendly success/error messages

### 🔧 Technical Features
- **TypeScript**: Full type safety throughout the application
- **React Router**: Client-side routing with protected routes
- **React Hook Form**: Efficient form handling and validation
- **Axios**: HTTP client with interceptors for authentication
- **Context API**: Global state management for authentication
- **MySQL Database**: Relational database with proper schema design

## Tech Stack

### Frontend
- **React 18** - UI library
- **TypeScript** - Type safety
- **Vite** - Build tool and dev server
- **Tailwind CSS** - Styling
- **React Router DOM** - Routing
- **React Hook Form** - Form management
- **Axios** - HTTP client
- **Lucide React** - Icons
- **React Hot Toast** - Notifications

### Backend
- **Node.js** - Runtime environment
- **Express.js** - Web framework
- **MySQL2** - Database driver
- **JWT** - Authentication
- **Bcryptjs** - Password hashing
- **Express Validator** - Input validation
- **CORS** - Cross-origin resource sharing
- **Helmet** - Security headers

## Getting Started

### Prerequisites
- Node.js (v16 or higher)
- MySQL (v8.0 or higher)
- npm or yarn

### Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd customer-management-react
   ```

2. **Install frontend dependencies**
   ```bash
   npm install
   ```

3. **Install backend dependencies**
   ```bash
   cd server
   npm install
   cd ..
   ```

4. **Set up the database**
   - Create a MySQL database named `customer_management`
   - Update the database configuration in `server/.env` (copy from `env.example`)

5. **Configure environment variables**
   ```bash
   cp env.example .env
   # Edit .env with your database credentials
   ```

6. **Start the backend server**
   ```bash
   cd server
   npm run dev
   ```

7. **Start the frontend development server**
   ```bash
   npm run dev
   ```

8. **Access the application**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:5000

### Default Login Credentials
- **Username**: admin
- **Password**: admin123
- **Role**: Admin

## Project Structure

```
customer-management-react/
├── src/                          # Frontend source code
│   ├── components/               # Reusable React components
│   │   ├── Layout.tsx           # Main layout component
│   │   ├── Sidebar.tsx          # Navigation sidebar
│   │   ├── Header.tsx           # Top header
│   │   └── ProtectedRoute.tsx   # Route protection
│   ├── contexts/                # React contexts
│   │   └── AuthContext.tsx      # Authentication context
│   ├── lib/                     # Utility libraries
│   │   └── api.ts              # API client configuration
│   ├── pages/                   # Page components
│   │   ├── Login.tsx           # Login page
│   │   ├── Dashboard.tsx       # Dashboard page
│   │   ├── Customers.tsx       # Customer list page
│   │   ├── CustomerForm.tsx    # Customer form page
│   │   ├── CustomerView.tsx    # Customer details page
│   │   ├── Employees.tsx       # Employee list page
│   │   ├── EmployeeForm.tsx    # Employee form page
│   │   ├── Profile.tsx         # User profile page
│   │   └── CustomerRegistration.tsx # Public registration
│   ├── App.tsx                 # Main app component
│   ├── main.tsx               # App entry point
│   └── index.css              # Global styles
├── server/                     # Backend source code
│   ├── config/                # Configuration files
│   │   └── database.js        # Database configuration
│   ├── middleware/            # Express middleware
│   │   ├── auth.js           # Authentication middleware
│   │   └── errorHandler.js   # Error handling middleware
│   ├── routes/               # API routes
│   │   ├── auth.js          # Authentication routes
│   │   ├── users.js         # User management routes
│   │   ├── customers.js     # Customer management routes
│   │   └── dashboard.js     # Dashboard data routes
│   └── index.js             # Server entry point
├── package.json              # Frontend dependencies
├── server/package.json       # Backend dependencies
├── tailwind.config.js        # Tailwind configuration
├── tsconfig.json            # TypeScript configuration
└── README.md               # This file
```

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `GET /api/auth/profile` - Get user profile
- `POST /api/auth/logout` - User logout

### Users
- `GET /api/users` - Get all users (admin only)
- `GET /api/users/:id` - Get user by ID
- `POST /api/users` - Create new user (admin only)
- `PUT /api/users/:id` - Update user
- `DELETE /api/users/:id` - Delete user (admin only)

### Customers
- `GET /api/customers` - Get all customers (with pagination and search)
- `GET /api/customers/:id` - Get customer by ID
- `POST /api/customers` - Create new customer
- `PUT /api/customers/:id` - Update customer
- `DELETE /api/customers/:id` - Delete customer

### Dashboard
- `GET /api/dashboard/stats` - Get dashboard statistics
- `GET /api/dashboard/recent-customers` - Get recent customers

## Database Schema

### Tables
- **roles** - User roles (admin, employee, customer)
- **users** - System users (employees and admins)
- **customers** - Customer records
- **customer_documents** - Customer document attachments

### Key Features
- Role-based access control
- Audit trails (created_at, updated_at)
- Foreign key relationships
- Proper indexing for performance

## Security Features

- **JWT Authentication**: Secure token-based authentication
- **Password Hashing**: Bcrypt for secure password storage
- **Input Validation**: Server-side validation for all inputs
- **SQL Injection Protection**: Parameterized queries
- **CORS Configuration**: Controlled cross-origin requests
- **Rate Limiting**: API rate limiting to prevent abuse
- **Security Headers**: Helmet.js for security headers

## Development

### Available Scripts

#### Frontend
- `npm run dev` - Start development server
- `npm run build` - Build for production
- `npm run preview` - Preview production build
- `npm run lint` - Run ESLint

#### Backend
- `npm run dev` - Start development server with nodemon
- `npm start` - Start production server

### Code Style
- TypeScript for type safety
- ESLint for code linting
- Prettier for code formatting
- Consistent naming conventions

## Deployment

### Frontend Deployment
1. Build the application: `npm run build`
2. Deploy the `dist` folder to your hosting service
3. Configure environment variables for production

### Backend Deployment
1. Set up a production database
2. Configure environment variables
3. Install dependencies: `npm install --production`
4. Start the server: `npm start`

### Environment Variables for Production
- Set `NODE_ENV=production`
- Use a strong `JWT_SECRET`
- Configure production database credentials
- Set appropriate `FRONTEND_URL` for CORS
=======
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
>>>>>>> 40ab0dd64fc3c58f1cc750db36e6039696ab5549

## Contributing

1. Fork the repository
<<<<<<< HEAD
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please open an issue in the repository.

---

**Note**: This is a converted version of the original PHP-based customer management system, now built with modern React and Node.js technologies for better performance, maintainability, and user experience.
=======
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please contact [your-email@example.com] or open an issue in the repository.




>>>>>>> 40ab0dd64fc3c58f1cc750db36e6039696ab5549
