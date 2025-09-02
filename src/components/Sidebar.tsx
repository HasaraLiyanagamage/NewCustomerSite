import { NavLink } from 'react-router-dom'
import { 
  Home, 
  Users, 
  UserCheck, 
  User, 
  LogOut 
} from 'lucide-react'
import { useAuth } from '../contexts/AuthContext'

export const Sidebar: React.FC = () => {
  const { user, logout, isAdmin } = useAuth()

  const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: Home },
    { name: 'Customers', href: '/customers', icon: Users },
    ...(isAdmin ? [{ name: 'Employees', href: '/employees', icon: UserCheck }] : []),
    { name: 'Profile', href: '/profile', icon: User },
  ]

  return (
    <div className="w-64 bg-white shadow-lg">
      <div className="p-6">
        <div className="flex items-center">
          <div className="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
            <span className="text-white font-bold text-sm">CMS</span>
          </div>
          <span className="ml-3 text-xl font-semibold text-gray-900">CustomerMS</span>
        </div>
      </div>
      
      <nav className="mt-6">
        <div className="px-3">
          <div className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Main Menu
          </div>
          <div className="space-y-1">
            {navigation.map((item) => (
              <NavLink
                key={item.name}
                to={item.href}
                className={({ isActive }) =>
                  `group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors ${
                    isActive
                      ? 'bg-primary-50 text-primary-600 border-r-2 border-primary-600'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'
                  }`
                }
              >
                <item.icon className="mr-3 h-5 w-5" />
                {item.name}
              </NavLink>
            ))}
          </div>
        </div>
        
        <div className="mt-8 px-3">
          <div className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
            Account
          </div>
          <button
            onClick={logout}
            className="group flex items-center w-full px-3 py-2 text-sm font-medium text-gray-600 rounded-md hover:bg-gray-50 hover:text-gray-900 transition-colors"
          >
            <LogOut className="mr-3 h-5 w-5" />
            Logout
          </button>
        </div>
      </nav>
    </div>
  )
}
