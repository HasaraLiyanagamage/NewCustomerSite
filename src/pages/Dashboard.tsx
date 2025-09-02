import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { Users, UserCheck, DollarSign, TrendingUp, Plus, Eye, Edit, Trash2 } from 'lucide-react'
import { dashboardAPI, customersAPI } from '../lib/api'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

interface DashboardStats {
  totalCustomers: number
  totalEmployees: number
  pendingTasks: number
  monthlyRevenue: number
}

interface RecentCustomer {
  id: string
  firstName: string
  lastName: string
  email: string
  phone: string
  createdAt: string
}

export const Dashboard: React.FC = () => {
  const [stats, setStats] = useState<DashboardStats | null>(null)
  const [recentCustomers, setRecentCustomers] = useState<RecentCustomer[]>([])
  const [loading, setLoading] = useState(true)
  const { user, isAdmin } = useAuth()

  useEffect(() => {
    const fetchDashboardData = async () => {
      try {
        const [statsResponse, customersResponse] = await Promise.all([
          dashboardAPI.getStats(),
          dashboardAPI.getRecentCustomers()
        ])
        
        setStats(statsResponse.data)
        setRecentCustomers(customersResponse.data)
      } catch (error) {
        toast.error('Failed to load dashboard data')
      } finally {
        setLoading(false)
      }
    }

    fetchDashboardData()
  }, [])

  const handleDeleteCustomer = async (customerId: string) => {
    if (!window.confirm('Are you sure you want to delete this customer?')) {
      return
    }

    try {
      await customersAPI.delete(customerId)
      setRecentCustomers(prev => prev.filter(c => c.id !== customerId))
      toast.success('Customer deleted successfully')
    } catch (error) {
      toast.error('Failed to delete customer')
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    )
  }

  const getInitials = (firstName: string, lastName: string) => {
    return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
  }

  const getRandomColor = (index: number) => {
    const colors = ['#4361ee', '#3f37c9', '#3a0ca3', '#4cc9f0', '#4895ef', '#f72585', '#7209b7']
    return colors[index % colors.length]
  }

  return (
    <div className="space-y-6">
      {/* Welcome Banner */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h1 className="text-2xl font-semibold text-gray-900">
          Welcome back, {user?.firstName}! 👋
        </h1>
        <p className="text-gray-600 mt-1">
          Here's what's happening with your business today.
        </p>
      </div>

      {/* Stats Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
              <Users className="h-6 w-6 text-primary-600" />
            </div>
            <div className="text-right">
              <p className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                Total Customers
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {stats?.totalCustomers || 0}
              </p>
              <div className="flex items-center text-sm text-green-600 mt-1">
                <TrendingUp className="h-4 w-4 mr-1" />
                <span>12.5% from last month</span>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <UserCheck className="h-6 w-6 text-green-600" />
            </div>
            <div className="text-right">
              <p className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                Active Employees
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {stats?.totalEmployees || 0}
              </p>
              <div className="flex items-center text-sm text-green-600 mt-1">
                <TrendingUp className="h-4 w-4 mr-1" />
                <span>5.2% this year</span>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <TrendingUp className="h-6 w-6 text-yellow-600" />
            </div>
            <div className="text-right">
              <p className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                Pending Tasks
              </p>
              <p className="text-2xl font-bold text-gray-900">
                {stats?.pendingTasks || 0}
              </p>
              <div className="flex items-center text-sm text-red-600 mt-1">
                <TrendingUp className="h-4 w-4 mr-1 rotate-180" />
                <span>3.1% from yesterday</span>
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div className="flex items-center justify-between">
            <div className="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
              <DollarSign className="h-6 w-6 text-purple-600" />
            </div>
            <div className="text-right">
              <p className="text-sm font-medium text-gray-500 uppercase tracking-wide">
                Monthly Revenue
              </p>
              <p className="text-2xl font-bold text-gray-900">
                ${stats?.monthlyRevenue?.toLocaleString() || '0'}
              </p>
              <div className="flex items-center text-sm text-green-600 mt-1">
                <TrendingUp className="h-4 w-4 mr-1" />
                <span>18.7% vs last month</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Recent Customers */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        <div className="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 className="text-lg font-semibold text-gray-900">Recent Customers</h2>
          <Link
            to="/customers/new"
            className="btn btn-primary btn-sm"
          >
            <Plus className="h-4 w-4 mr-2" />
            Add New
          </Link>
        </div>

        {recentCustomers.length > 0 ? (
          <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-gray-200">
              <thead className="bg-gray-50">
                <tr>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Customer
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Email
                  </th>
                  <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date Added
                  </th>
                  <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody className="bg-white divide-y divide-gray-200">
                {recentCustomers.map((customer, index) => (
                  <tr key={customer.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="flex items-center">
                        <div
                          className="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-medium"
                          style={{ backgroundColor: getRandomColor(index) }}
                        >
                          {getInitials(customer.firstName, customer.lastName)}
                        </div>
                        <div className="ml-4">
                          <div className="text-sm font-medium text-gray-900">
                            {customer.firstName} {customer.lastName}
                          </div>
                          <div className="text-sm text-gray-500">
                            {customer.phone || 'N/A'}
                          </div>
                        </div>
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {customer.email}
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap">
                      <div className="text-sm text-gray-900">
                        {new Date(customer.createdAt).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric'
                        })}
                      </div>
                      <div className="text-sm text-gray-500">
                        {new Date(customer.createdAt).toLocaleTimeString('en-US', {
                          hour: 'numeric',
                          minute: '2-digit'
                        })}
                      </div>
                    </td>
                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div className="flex items-center justify-end space-x-2">
                        <Link
                          to={`/customers/${customer.id}`}
                          className="text-gray-400 hover:text-primary-600 p-1 rounded-full hover:bg-primary-50"
                        >
                          <Eye className="h-4 w-4" />
                        </Link>
                        <Link
                          to={`/customers/${customer.id}/edit`}
                          className="text-gray-400 hover:text-blue-600 p-1 rounded-full hover:bg-blue-50"
                        >
                          <Edit className="h-4 w-4" />
                        </Link>
                        <button
                          onClick={() => handleDeleteCustomer(customer.id)}
                          className="text-gray-400 hover:text-red-600 p-1 rounded-full hover:bg-red-50"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        ) : (
          <div className="text-center py-12">
            <Users className="mx-auto h-12 w-12 text-gray-400" />
            <h3 className="mt-2 text-sm font-medium text-gray-900">No customers yet</h3>
            <p className="mt-1 text-sm text-gray-500">Get started by adding your first customer</p>
            <div className="mt-6">
              <Link
                to="/customers/new"
                className="btn btn-primary"
              >
                <Plus className="h-4 w-4 mr-2" />
                Add Customer
              </Link>
            </div>
          </div>
        )}
      </div>
    </div>
  )
}
