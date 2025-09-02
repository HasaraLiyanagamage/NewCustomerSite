import { useState, useEffect } from 'react'
import { Link } from 'react-router-dom'
import { Search, Plus, Eye, Edit, Trash2, ChevronLeft, ChevronRight, Users } from 'lucide-react'
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
  country: string
  businessName: string
  businessType: string
  tinNumber: string
  vatNumber: string
  createdAt: string
  createdBy: string
}

export const Customers: React.FC = () => {
  const [customers, setCustomers] = useState<Customer[]>([])
  const [loading, setLoading] = useState(true)
  const [searchTerm, setSearchTerm] = useState('')
  const [currentPage, setCurrentPage] = useState(1)
  const [totalPages, setTotalPages] = useState(1)
  const [totalCustomers, setTotalCustomers] = useState(0)
  const { user } = useAuth()

  const perPage = 10

  useEffect(() => {
    fetchCustomers()
  }, [currentPage, searchTerm])

  const fetchCustomers = async () => {
    try {
      setLoading(true)
      const params = {
        page: currentPage,
        limit: perPage,
        search: searchTerm || undefined,
      }
      
      const response = await customersAPI.getAll(params)
      const responseData = response.data.data || response.data
      const responseHeaders = response.data.headers || response.headers
      
      // Map the API response to match the Customer interface
      const mappedCustomers = Array.isArray(responseData) 
        ? responseData.map((customer: any) => ({
            id: customer.id,
            firstName: customer.owner_first_name || customer.firstName,
            lastName: customer.owner_last_name || customer.lastName,
            email: customer.email,
            phone: customer.phone,
            address: customer.address,
            city: customer.city,
            state: customer.state,
            country: customer.country,
            businessName: customer.business_name || customer.businessName,
            businessType: customer.business_type || customer.businessType,
            tinNumber: customer.tin || customer.tinNumber,
            vatNumber: customer.vat || customer.vatNumber,
            createdAt: customer.created_at || customer.createdAt,
            createdBy: customer.created_by || customer.createdBy
          }))
        : []
      
      setCustomers(mappedCustomers)
      
      // Handle pagination headers
      const totalCount = responseHeaders?.['x-total-count'] || 
                        responseHeaders?.['x-total'] || 
                        mappedCustomers.length.toString()
      
      setTotalPages(Math.ceil(parseInt(totalCount, 10) / perPage))
      setTotalCustomers(parseInt(totalCount, 10) || mappedCustomers.length)
    } catch (error) {
      toast.error('Failed to load customers')
    } finally {
      setLoading(false)
    }
  }

  const handleDeleteCustomer = async (customerId: string) => {
    if (!window.confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
      return
    }

    try {
      await customersAPI.delete(customerId)
      setCustomers(prev => prev.filter(c => c.id !== customerId))
      setTotalCustomers(prev => prev - 1)
      toast.success('Customer deleted successfully')
    } catch (error) {
      toast.error('Failed to delete customer')
    }
  }

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault()
    setCurrentPage(1)
    fetchCustomers()
  }

  const clearSearch = () => {
    setSearchTerm('')
    setCurrentPage(1)
  }

  const canDelete = (customer: Customer) => {
    return user?.role === 'admin' || customer.createdBy === user?.id
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold text-gray-900">Customers</h1>
        <Link
          to="/customers/new"
          className="btn btn-primary"
        >
          <Plus className="h-4 w-4 mr-2" />
          Add New Customer
        </Link>
      </div>

      {/* Search */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form onSubmit={handleSearch} className="flex gap-4">
          <div className="flex-1 relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search className="h-5 w-5 text-gray-400" />
            </div>
            <input
              type="text"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              placeholder="Search customers by name, email, or phone..."
              className="input pl-10"
            />
          </div>
          <button type="submit" className="btn btn-primary">
            Search
          </button>
          {searchTerm && (
            <button
              type="button"
              onClick={clearSearch}
              className="btn btn-secondary"
            >
              Clear
            </button>
          )}
        </form>
      </div>

      {/* Customers Table */}
      <div className="bg-white rounded-lg shadow-sm border border-gray-200">
        {loading ? (
          <div className="flex items-center justify-center h-64">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
          </div>
        ) : customers.length > 0 ? (
          <>
            <div className="overflow-x-auto">
              <table className="min-w-full divide-y divide-gray-200">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Contact
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Location
                    </th>
                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Business
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
                  {customers.map((customer) => (
                    <tr key={customer.id} className="hover:bg-gray-50">
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm font-medium text-gray-900">
                          {customer.firstName} {customer.lastName}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{customer.email}</div>
                        {customer.phone && (
                          <div className="text-sm text-gray-500">{customer.phone}</div>
                        )}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">
                          {[customer.city, customer.state, customer.country]
                            .filter(Boolean)
                            .join(', ') || '-'}
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap">
                        <div className="text-sm text-gray-900">{customer.businessName}</div>
                        <div className="text-sm text-gray-500">{customer.businessType}</div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {new Date(customer.createdAt).toLocaleDateString('en-US', {
                          month: 'short',
                          day: 'numeric',
                          year: 'numeric'
                        })}
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div className="flex items-center justify-end space-x-2">
                          <Link
                            to={`/customers/${customer.id}`}
                            className="text-gray-400 hover:text-green-600 p-1 rounded-full hover:bg-green-50"
                            title="View"
                          >
                            <Eye className="h-4 w-4" />
                          </Link>
                          <Link
                            to={`/customers/${customer.id}/edit`}
                            className="text-gray-400 hover:text-yellow-600 p-1 rounded-full hover:bg-yellow-50"
                            title="Edit"
                          >
                            <Edit className="h-4 w-4" />
                          </Link>
                          {canDelete(customer) && (
                            <button
                              onClick={() => handleDeleteCustomer(customer.id)}
                              className="text-gray-400 hover:text-red-600 p-1 rounded-full hover:bg-red-50"
                              title="Delete"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                <div className="flex-1 flex justify-between sm:hidden">
                  <button
                    onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                    disabled={currentPage === 1}
                    className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Previous
                  </button>
                  <button
                    onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                    disabled={currentPage === totalPages}
                    className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Next
                  </button>
                </div>
                <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                  <div>
                    <p className="text-sm text-gray-700">
                      Showing{' '}
                      <span className="font-medium">
                        {(currentPage - 1) * perPage + 1}
                      </span>{' '}
                      to{' '}
                      <span className="font-medium">
                        {Math.min(currentPage * perPage, totalCustomers)}
                      </span>{' '}
                      of{' '}
                      <span className="font-medium">{totalCustomers}</span>{' '}
                      results
                    </p>
                  </div>
                  <div>
                    <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                      <button
                        onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                        disabled={currentPage === 1}
                        className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <ChevronLeft className="h-5 w-5" />
                      </button>
                      
                      {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                        <button
                          key={page}
                          onClick={() => setCurrentPage(page)}
                          className={`relative inline-flex items-center px-4 py-2 border text-sm font-medium ${
                            page === currentPage
                              ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                              : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                          }`}
                        >
                          {page}
                        </button>
                      ))}
                      
                      <button
                        onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                        disabled={currentPage === totalPages}
                        className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <ChevronRight className="h-5 w-5" />
                      </button>
                    </nav>
                  </div>
                </div>
              </div>
            )}
          </>
        ) : (
          <div className="text-center py-12">
            <div className="mx-auto h-12 w-12 text-gray-400">
              <Users className="h-12 w-12" />
            </div>
            <h3 className="mt-2 text-sm font-medium text-gray-900">
              {searchTerm ? 'No customers found' : 'No customers yet'}
            </h3>
            <p className="mt-1 text-sm text-gray-500">
              {searchTerm ? (
                <>
                  No customers found matching your search.{' '}
                  <button
                    onClick={clearSearch}
                    className="text-primary-600 hover:text-primary-500"
                  >
                    Clear search
                  </button>{' '}
                  or{' '}
                  <Link to="/customers/new" className="text-primary-600 hover:text-primary-500">
                    add a new customer
                  </Link>
                  .
                </>
              ) : (
                <>
                  Get started by{' '}
                  <Link to="/customers/new" className="text-primary-600 hover:text-primary-500">
                    adding your first customer
                  </Link>
                  .
                </>
              )}
            </p>
          </div>
        )}
      </div>
    </div>
  )
}
