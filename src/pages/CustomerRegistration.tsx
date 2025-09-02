import { useState } from 'react'
import { useForm } from 'react-hook-form'
import { Save, Building, User, MapPin, FileText } from 'lucide-react'
import { customersAPI } from '../lib/api'
import toast from 'react-hot-toast'

interface CustomerRegistrationData {
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
  vatNumber: string
  activities: string
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

export const CustomerRegistration: React.FC = () => {
  const [loading, setLoading] = useState(false)
  const [activeTab, setActiveTab] = useState('basic')

  const {
    register,
    handleSubmit,
    formState: { errors },
    watch,
    setValue
  } = useForm<CustomerRegistrationData>({
    defaultValues: {
      country: 'Sri Lanka',
      registration_date: new Date().toISOString().split('T')[0],
      declaration: false
    }
  })

  const onSubmit = async (data: CustomerRegistrationData) => {
    try {
      setLoading(true)
      await customersAPI.create(data)
      toast.success('Registration successful! Your customer ID has been generated.')
      
      // Reset form
      window.location.reload()
    } catch (error: any) {
      const message = error.response?.data?.message || 'Registration failed'
      toast.error(message)
    } finally {
      setLoading(false)
    }
  }

  const tabs = [
    { id: 'basic', label: 'Basic', icon: Building },
    { id: 'personal', label: 'Personal', icon: User },
    { id: 'address', label: 'Address', icon: MapPin },
    { id: 'business', label: 'Business', icon: FileText },
  ]

  return (
    <div className="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
      <div className="max-w-4xl mx-auto">
        <div className="text-center mb-8">
          <h1 className="text-3xl font-bold text-gray-900">
            Electronic Registration of Traders & Logistics Operators
          </h1>
          <p className="mt-2 text-gray-600">
            Please fill out the form below to register as a customer
          </p>
        </div>

        <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
          {/* Tab Navigation */}
          <div className="bg-white rounded-lg shadow-sm border border-gray-200">
            <div className="border-b border-gray-200">
              <nav className="flex space-x-8 px-6" aria-label="Tabs">
                {tabs.map((tab) => {
                  const Icon = tab.icon
                  return (
                    <button
                      key={tab.id}
                      type="button"
                      onClick={() => setActiveTab(tab.id)}
                      className={`${
                        activeTab === tab.id
                          ? 'border-primary-500 text-primary-600'
                          : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                      } whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center`}
                    >
                      <Icon className="h-5 w-5 mr-2" />
                      {tab.label}
                    </button>
                  )
                })}
              </nav>
            </div>

            <div className="p-6">
              {/* Basic Information Tab */}
              {activeTab === 'basic' && (
                <div className="space-y-6">
                  <div>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                      Business / Individual Details
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Name of Business / Individual Name *
                        </label>
                        <input
                          {...register('business_name', { required: 'Business name is required' })}
                          className="input"
                          placeholder="Enter business or individual name"
                        />
                        {errors.business_name && (
                          <p className="mt-1 text-sm text-red-600">{errors.business_name.message}</p>
                        )}
                      </div>
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Type of Business *
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
                  </div>

                  <div>
                    <h3 className="text-lg font-medium text-gray-900 mb-4">
                      TIN / VAT / Permit Information
                    </h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <div>
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                          Tax Identification Number *
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
                    <div className="mt-6">
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Business Registration Number
                      </label>
                      <input
                        {...register('owner_id_number')}
                        className="input"
                        placeholder="Enter owner ID number"
                      />
                      {errors.owner_id_number && (
                        <p className="mt-1 text-sm text-red-600">{errors.owner_id_number.message}</p>
                      )}
                    </div>
                  </div>
                </div>
              )}

              {/* Personal Information Tab */}
              {activeTab === 'personal' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900 mb-4">
                    Personal Details
                  </h3>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        First Name *
                      </label>
                      <input
                        {...register('owner_first_name', { required: 'First name is required' })}
                        className="input"
                        placeholder="Enter first name"
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
                        {...register('owner_last_name', { required: 'Last name is required' })}
                        className="input"
                        placeholder="Enter last name"
                      />
                      {errors.owner_last_name && (
                        <p className="mt-1 text-sm text-red-600">{errors.owner_last_name.message}</p>
                      )}
                    </div>
                  </div>
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label className="block text-sm font-medium text-gray-700 mb-2">
                        Email Address *
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
                        Phone Number *
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
              )}

              {/* Address Information Tab */}
              {activeTab === 'address' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900 mb-4">
                    Address Information
                  </h3>
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
                        State/Province
                      </label>
                      <input
                        {...register('activities')}
                        className="input"
                        placeholder="Enter state/province"
                      />
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
                      {errors.postal_code && (
                        <p className="mt-1 text-sm text-red-600">{errors.postal_code.message}</p>
                      )}
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Country
                    </label>
                    <input
                      {...register('country')}
                      className="input"
                      placeholder="Enter country"
                    />
                  </div>
                </div>
              )}

              {/* Business Information Tab */}
              {activeTab === 'business' && (
                <div className="space-y-6">
                  <h3 className="text-lg font-medium text-gray-900 mb-4">
                    Business Activities
                  </h3>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Declaration
                    </label>
                    <div className="flex items-center">
                      <input
                        type="checkbox"
                        id="declaration"
                        {...register('declaration', { required: 'You must accept the declaration' })}
                        className="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                      />
                      <label htmlFor="declaration" className="ml-2 block text-sm text-gray-700">
                        I declare that the information provided is true and correct to the best of my knowledge.
                      </label>
                    </div>
                    {errors.declaration && (
                      <p className="mt-1 text-sm text-red-600">{errors.declaration.message}</p>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Navigation Buttons */}
          <div className="flex items-center justify-between">
            <div className="flex space-x-3">
              {tabs.map((tab, index) => {
                const currentIndex = tabs.findIndex(t => t.id === activeTab)
                if (index < currentIndex) {
                  return (
                    <button
                      key={tab.id}
                      type="button"
                      onClick={() => setActiveTab(tab.id)}
                      className="btn btn-secondary btn-sm"
                    >
                      {tab.label}
                    </button>
                  )
                }
                return null
              })}
            </div>
            <div className="flex space-x-3">
              {activeTab !== tabs[tabs.length - 1].id ? (
                <button
                  type="button"
                  onClick={() => {
                    const currentIndex = tabs.findIndex(t => t.id === activeTab)
                    setActiveTab(tabs[currentIndex + 1].id)
                  }}
                  className="btn btn-primary"
                >
                  Next
                </button>
              ) : (
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
                  Register Application
                </button>
              )}
            </div>
          </div>
        </form>
      </div>
    </div>
  )
}
