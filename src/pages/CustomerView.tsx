import { useState, useEffect } from 'react'
import { useParams, useNavigate, Link } from 'react-router-dom'
import { ArrowLeft, Edit, Trash2, Mail, Phone, MapPin, Building, FileText } from 'lucide-react'
import { customersAPI } from '../lib/api'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

interface Customer {
  id: string
  firstName: string
  lastName: string
  email: string
  phone: string
  address: string
  city: string
  state: string
  postalCode: string
  country: string
  businessName: string
  businessType: string
  businessRegNumber: string
  tinNumber: string
  vatNumber: string
  activities: string
  createdAt: string
  updatedAt: string
  createdBy: string
}

export const CustomerView: React.FC = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [customer, setCustomer] = useState<Customer | null>(null)
  const [loading, setLoading] = useState(true)
  const { user } = useAuth()

  useEffect(() => {
    fetchCustomer()
  }, [id])

  const fetchCustomer = async () => {
    try {
      const response = await customersAPI.getById(id!)
      setCustomer(response.data)
    } catch (error) {
      toast.error('Failed to load customer data')
      navigate('/customers')
    } finally {
      setLoading(false)
    }
  }

  const handleDelete = async () => {
    if (!window.confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
      return
    }

    try {
      await customersAPI.delete(id!)
      toast.success('Customer deleted successfully')
      navigate('/customers')
    } catch (error) {
      toast.error('Failed to delete customer')
    }
  }

  const canDelete = customer && (user?.role === 'admin' || customer.createdBy === user?.id)

  if (loading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    )
  }

  if (!customer) {
    return (
      <div className="text-center py-12">
        <h3 className="text-lg font-medium text-gray-900">Customer not found</h3>
        <p className="text-gray-500 mt-2">The customer you're looking for doesn't exist.</p>
        <Link to="/customers" className="btn btn-primary mt-4">
          Back to Customers
        </Link>
      </div>
    )
  }

  const getInitials = (firstName: string, lastName: string) => {
    return `${firstName.charAt(0)}${lastName.charAt(0)}`.toUpperCase()
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-4">
          <button
            onClick={() => navigate('/customers')}
            className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
          >
            <ArrowLeft className="h-5 w-5" />
          </button>
          <div>
            <h1 className="text-2xl font-semibold text-gray-900">
              {customer.firstName} {customer.lastName}
            </h1>
            <p className="text-gray-500">Customer Details</p>
          </div>
        </div>
        <div className="flex items-center space-x-3">
          <Link
            to={`/customers/${customer.id}/edit`}
            className="btn btn-primary btn-sm"
          >
            <Edit className="h-4 w-4 mr-2" />
            Edit
          </Link>
          {canDelete && (
            <button
              onClick={handleDelete}
              className="btn btn-danger btn-sm"
            >
              <Trash2 className="h-4 w-4 mr-2" />
              Delete
            </button>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Customer Info Card */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="text-center">
              <div className="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <span className="text-white text-2xl font-bold">
                  {getInitials(customer.firstName, customer.lastName)}
                </span>
              </div>
              <h3 className="text-xl font-semibold text-gray-900">
                {customer.firstName} {customer.lastName}
              </h3>
              <p className="text-gray-500">{customer.businessName}</p>
            </div>

            <div className="mt-6 space-y-4">
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-3 text-gray-400" />
                <span>{customer.email}</span>
              </div>
              {customer.phone && (
                <div className="flex items-center text-sm text-gray-600">
                  <Phone className="h-4 w-4 mr-3 text-gray-400" />
                  <span>{customer.phone}</span>
                </div>
              )}
              {customer.address && (
                <div className="flex items-start text-sm text-gray-600">
                  <MapPin className="h-4 w-4 mr-3 text-gray-400 mt-0.5" />
                  <span>{customer.address}</span>
                </div>
              )}
            </div>

            <div className="mt-6 pt-6 border-t border-gray-200">
              <div className="text-sm text-gray-500">
                <p>Created: {new Date(customer.createdAt).toLocaleDateString()}</p>
                <p>Last updated: {new Date(customer.updatedAt).toLocaleDateString()}</p>
              </div>
            </div>
          </div>
        </div>

        {/* Details */}
        <div className="lg:col-span-2 space-y-6">
          {/* Personal Information */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-medium text-gray-900">Personal Information</h2>
            </div>
            <div className="p-6">
              <dl className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt className="text-sm font-medium text-gray-500">First Name</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.firstName}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Last Name</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.lastName}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Email</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.email}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Phone</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.phone || 'N/A'}</dd>
                </div>
              </dl>
            </div>
          </div>

          {/* Address Information */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-medium text-gray-900">Address Information</h2>
            </div>
            <div className="p-6">
              <dl className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="md:col-span-2">
                  <dt className="text-sm font-medium text-gray-500">Address</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.address || 'N/A'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">City</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.city || 'N/A'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">State</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.state || 'N/A'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Postal Code</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.postalCode || 'N/A'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Country</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.country || 'N/A'}</dd>
                </div>
              </dl>
            </div>
          </div>

          {/* Business Information */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="px-6 py-4 border-b border-gray-200">
              <h2 className="text-lg font-medium text-gray-900">Business Information</h2>
            </div>
            <div className="p-6">
              <dl className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dt className="text-sm font-medium text-gray-500">Business Name</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.businessName}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Business Type</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.businessType}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">Business Registration Number</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.businessRegNumber || 'N/A'}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">TIN Number</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.tinNumber}</dd>
                </div>
                <div>
                  <dt className="text-sm font-medium text-gray-500">VAT Number</dt>
                  <dd className="mt-1 text-sm text-gray-900">{customer.vatNumber}</dd>
                </div>
                {customer.activities && (
                  <div className="md:col-span-2">
                    <dt className="text-sm font-medium text-gray-500">Business Activities</dt>
                    <dd className="mt-1 text-sm text-gray-900">{customer.activities}</dd>
                  </div>
                )}
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}
