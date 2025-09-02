import { Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './contexts/AuthContext'
import { ProtectedRoute } from './components/ProtectedRoute'
import { Layout } from './components/Layout'
import { Login } from './pages/Login'
import { Dashboard } from './pages/Dashboard'
import { Customers } from './pages/Customers'
import { CustomerForm } from './pages/CustomerForm'
import { CustomerView } from './pages/CustomerView'
import { Employees } from './pages/Employees'
import { EmployeeForm } from './pages/EmployeeForm'
import { Profile } from './pages/Profile'
import { CustomerRegistration } from './pages/CustomerRegistration'

function App() {
  return (
    <AuthProvider>
      <Routes>
        {/* Public routes */}
        <Route path="/login" element={<Login />} />
        <Route path="/register-customer" element={<CustomerRegistration />} />
        
        {/* Protected routes */}
        <Route path="/" element={
          <ProtectedRoute>
            <Layout />
          </ProtectedRoute>
        }>
          <Route index element={<Navigate to="/dashboard" replace />} />
          <Route path="dashboard" element={<Dashboard />} />
          <Route path="customers" element={<Customers />} />
          <Route path="customers/new" element={<CustomerForm />} />
          <Route path="customers/:id" element={<CustomerView />} />
          <Route path="customers/:id/edit" element={<CustomerForm />} />
          <Route path="employees" element={<Employees />} />
          <Route path="employees/new" element={<EmployeeForm />} />
          <Route path="employees/:id/edit" element={<EmployeeForm />} />
          <Route path="profile" element={<Profile />} />
        </Route>
      </Routes>
    </AuthProvider>
  )
}

export default App
