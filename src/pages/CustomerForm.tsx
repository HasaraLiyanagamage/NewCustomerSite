import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { ArrowLeft, Save } from 'lucide-react'
import { customersAPI } from '../lib/api'
import toast from 'react-hot-toast'

interface CustomerFormData {
  id?: string
  tin: string
  vat: string
  business_name: string
  owner_first_name: string
  owner_last_name: string
  owner_id_number: string
  owner_passport_number: string
  address: string
  city: string
  country: string
  postal_code: string
  phone: string
  email: string
  website?: string
  business_type: string
  registration_date: string
  declaration: boolean
  activities?: string
}

const businessTypes = [
  'Non Business Individual (Motor Vehicle)',
  'Business Individual',
  'Private Company',
  'Public Company',
  'Partnership',
  'Sole Proprietorship',
  'Other'
]

export const CustomerForm: React.FC = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [initialLoading, setInitialLoading] = useState(!!id)
  const isEdit = !!id

  const {
    register,
    handleSubmit,
    formState: { errors },
    setValue,
    reset
  } = useForm<CustomerFormData>({
    defaultValues: {
      country: 'Sri Lanka',
      declaration: false,
      registration_date: new Date().toISOString().split('T')[0],
      business_type: '',
      tin: '',
      vat: '',
      business_name: '',
      owner_first_name: '',
      owner_last_name: '',
      owner_id_number: '',
      owner_passport_number: '',
      address: '',
      city: '',
      postal_code: '',
      phone: '',
      email: '',
      activities: ''
    }
  })

  useEffect(() => {
    if (isEdit) {
      fetchCustomer()
    }
  }, [id])

  const fetchCustomer = async () => {
    try {
      const response = await customersAPI.getById(id!)
      const customer = response.data
      
      // Map the API response to match our form fields
      reset({
        id: customer.id,
        tin: customer.tin,
        vat: customer.vat,
        business_name: customer.business_name,
        owner_first_name: customer.owner_first_name,
        owner_last_name: customer.owner_last_name,
        owner_id_number: customer.owner_id_number,
        owner_passport_number: customer.owner_passport_number,
        address: customer.address,
        city: customer.city,
        country: customer.country,
        postal_code: customer.postal_code,
        phone: customer.phone,
        email: customer.email,
        website: customer.website,
        business_type: customer.business_type,
        registration_date: customer.registration_date,
        declaration: customer.declaration,
        activities: customer.activities
      })
    } catch (error) {
      toast.error('Failed to load customer data')
      navigate('/customers')
    } finally {
      setInitialLoading(false)
    }
  }

  const onSubmit = async (data: CustomerFormData) => {
    try {
      setLoading(true)
      
      if (isEdit) {
        await customersAPI.update(id!, data)
        toast.success('Customer updated successfully')
      } else {
        await customersAPI.create(data)
        toast.success('Customer created successfully')
      }
      
      navigate('/customers')
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to save customer'
      toast.error(message)
    } finally {
      setLoading(false)
    }
  }

  if (initialLoading) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center space-x-4">
        <button
          onClick={() => navigate('/customers')}
          className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
        >
          <ArrowLeft className="h-5 w-5" />
        </button>
        <h1 className="text-2xl font-semibold text-gray-900">
          {isEdit ? 'Edit Customer' : 'Add New Customer'}
        </h1>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-medium text-gray-900">Personal Information</h2>
          </div>
          <div className="p-6 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  First Name *
                </label>
                <input
                  {...register('owner_first_name', { required: 'Owner first name is required' })}
                  className="input"
                  placeholder="Enter owner's first name"
                />
                {errors.owner_first_name && (
                  <p className="mt-1 text-sm text-red-600">{errors.owner_first_name.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Last Name *
                </label>
                <input
                  {...register('owner_last_name', { required: 'Owner last name is required' })}
                  className="input"
                  placeholder="Enter owner's last name"
                />
                {errors.owner_last_name && (
                  <p className="mt-1 text-sm text-red-600">{errors.owner_last_name.message}</p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Email *
                </label>
                <input
                  type="email"
                  {...register('email', { 
                    required: 'Email is required',
                    pattern: {
                      value: /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i,
                      message: 'Invalid email address'
                    }
                  })}
                  className="input"
                  placeholder="Enter email address"
                />
                {errors.email && (
                  <p className="mt-1 text-sm text-red-600">{errors.email.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Phone *
                </label>
                <input
                  {...register('phone', { required: 'Phone number is required' })}
                  className="input"
                  placeholder="Enter phone number"
                />
                {errors.phone && (
                  <p className="mt-1 text-sm text-red-600">{errors.phone.message}</p>
                )}
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-medium text-gray-900">Address Information</h2>
          </div>
          <div className="p-6 space-y-6">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Address *
              </label>
              <textarea
                {...register('address', { required: 'Address is required' })}
                rows={3}
                className="input"
                placeholder="Enter full address"
              />
              {errors.address && (
                <p className="mt-1 text-sm text-red-600">{errors.address.message}</p>
              )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  City
                </label>
                <input
                  {...register('city')}
                  className="input"
                  placeholder="Enter city"
                />
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Country *
                </label>
                <input
                  {...register('country', { required: 'Country is required' })}
                  className="input"
                  placeholder="Enter country"
                />
                {errors.country && (
                  <p className="mt-1 text-sm text-red-600">{errors.country.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Postal Code
                </label>
                <input
                  {...register('postal_code')}
                  className="input"
                  placeholder="Enter postal code"
                />
              </div>
            </div>
          </div>
        </div>

        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-medium text-gray-900">Business Information</h2>
          </div>
          <div className="p-6 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Business Name *
                </label>
                <input
                  {...register('business_name', { required: 'Business name is required' })}
                  className="input"
                  placeholder="Enter business name"
                />
                {errors.business_name && (
                  <p className="mt-1 text-sm text-red-600">{errors.business_name.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Business Type *
                </label>
                <select
                  {...register('business_type', { required: 'Business type is required' })}
                  className="input"
                >
                  <option value="">Select business type</option>
                  {businessTypes.map((type) => (
                    <option key={type} value={type}>
                      {type}
                    </option>
                  ))}
                </select>
                {errors.business_type && (
                  <p className="mt-1 text-sm text-red-600">{errors.business_type.message}</p>
                )}
              </div>
            </div>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  TIN Number *
                </label>
                <input
                  {...register('tin', { required: 'TIN number is required' })}
                  className="input"
                  placeholder="Enter TIN number"
                />
                {errors.tin && (
                  <p className="mt-1 text-sm text-red-600">{errors.tin.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  VAT Number *
                </label>
                <input
                  {...register('vat', { required: 'VAT number is required' })}
                  className="input"
                  placeholder="Enter VAT number"
                />
                {errors.vat && (
                  <p className="mt-1 text-sm text-red-600">{errors.vat.message}</p>
                )}
              </div>
            </div>

            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">
                Business Activities
              </label>
              <textarea
                {...register('activities')}
                rows={3}
                className="input"
                placeholder="Describe business activities"
              />
            </div>
          </div>
        </div>

        <div className="flex items-center justify-end space-x-4">
          <button
            type="button"
            onClick={() => navigate('/customers')}
            className="btn btn-secondary"
          >
            Cancel
          </button>
          <button
            type="submit"
            disabled={loading}
            className="btn btn-primary"
          >
            {loading ? (
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
            ) : (
              <Save className="h-4 w-4 mr-2" />
            )}
            {isEdit ? 'Update Customer' : 'Create Customer'}
          </button>
        </div>
      </form>
    </div>
  )
}
