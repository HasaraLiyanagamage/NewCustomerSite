import { useState, useEffect } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { ArrowLeft, Save } from 'lucide-react'
import { usersAPI } from '../lib/api'
import toast from 'react-hot-toast'

interface EmployeeFormData {
  username: string
  email: string
  first_name: string
  last_name: string
  role: string
  password?: string
  current_password?: string
  new_password?: string
}

export const EmployeeForm: React.FC = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [initialLoading, setInitialLoading] = useState(!!id)
  const isEdit = !!id

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset
  } = useForm<EmployeeFormData>()

  useEffect(() => {
    if (isEdit) {
      fetchEmployee()
    }
  }, [id])

  const fetchEmployee = async () => {
    try {
      const response = await usersAPI.getById(id!)
      const employee = response.data
      
reset({
        username: employee.username,
        email: employee.email,
        first_name: employee.first_name,
        last_name: employee.last_name,
        role: employee.role || 'employee'
      })
    } catch (error) {
      toast.error('Failed to load employee data')
      navigate('/employees')
    } finally {
      setInitialLoading(false)
    }
  }

  const onSubmit = async (formData: EmployeeFormData) => {
    try {
      setLoading(true)
      
      // Map form data to API user format
      const { current_password, new_password, ...userData } = formData
      
      // Create base payload
      const payload: any = {
        ...userData,
        role: formData.role || 'employee' // Default role
      }
      
      // Add password update fields if they exist
      if (isEdit && new_password && current_password) {
        payload.password = new_password
        payload.current_password = current_password
      }
      
      if (isEdit) {
        await usersAPI.update(id!, payload)
        toast.success('Employee updated successfully')
      } else {
await usersAPI.create(userData)
        toast.success('Employee created successfully')
      }
      
      navigate('/employees')
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to save employee'
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
          onClick={() => navigate('/employees')}
          className="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100"
        >
          <ArrowLeft className="h-5 w-5" />
        </button>
        <h1 className="text-2xl font-semibold text-gray-900">
          {isEdit ? 'Edit Employee' : 'Add New Employee'}
        </h1>
      </div>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        <div className="bg-white rounded-lg shadow-sm border border-gray-200">
          <div className="px-6 py-4 border-b border-gray-200">
            <h2 className="text-lg font-medium text-gray-900">Employee Information</h2>
          </div>
          <div className="p-6 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  First Name *
                </label>
                <input
                  {...register('first_name', { required: 'First name is required' })}
                  className="input"
                  placeholder="Enter first name"
                />
                {errors.first_name && (
                  <p className="mt-1 text-sm text-red-600">{errors.first_name.message}</p>
                )}
              </div>
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Last Name *
                </label>
                <input
                  {...register('last_name', { required: 'Last name is required' })}
                  className="input"
                  placeholder="Enter last name"
                />
                {errors.last_name && (
                  <p className="mt-1 text-sm text-red-600">{errors.last_name.message}</p>
                )}
              </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Username *
                </label>
                <input
                  {...register('username', { 
                    required: 'Username is required',
                    minLength: { value: 3, message: 'Username must be at least 3 characters' }
                  })}
                  className="input"
                  placeholder="Enter username"
                  disabled={isEdit}
                />
                {errors.username && (
                  <p className="mt-1 text-sm text-red-600">{errors.username.message}</p>
                )}
                {isEdit && (
                  <p className="mt-1 text-sm text-gray-500">Username cannot be changed</p>
                )}
              </div>
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
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Role *
                </label>
                <select
                  {...register('role', { required: 'Role is required' })}
                  className="input"
                  defaultValue="employee"
                >
                  <option value="admin">Admin</option>
                  <option value="employee">Employee</option>
                </select>
                {errors.role && (
                  <p className="mt-1 text-sm text-red-600">{errors.role.message}</p>
                )}
              </div>
            </div>

            {!isEdit && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-2">
                  Password *
                </label>
                <input
                  type="password"
                  {...register('password', { 
                    required: 'Password is required',
                    minLength: { value: 6, message: 'Password must be at least 6 characters' }
                  })}
                  className="input"
                  placeholder="Enter password"
                />
                {errors.password && (
                  <p className="mt-1 text-sm text-red-600">{errors.password.message}</p>
                )}
              </div>
            )}

            {isEdit && (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                  </label>
                  <input
                    type="password"
                    {...register('current_password')}
                    className="input"
                    placeholder="Enter current password to change password"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    New Password
                  </label>
                  <input
                    type="password"
                    {...register('new_password', {
                      minLength: { value: 6, message: 'Password must be at least 6 characters' }
                    })}
                    className="input"
                    placeholder="Enter new password (leave blank to keep current)"
                  />
                  {errors.password && (
                    <p className="mt-1 text-sm text-red-600">{errors.password.message}</p>
                  )}
                </div>
              </div>
            )}
          </div>
        </div>

        <div className="flex items-center justify-end space-x-4">
          <button
            type="button"
            onClick={() => navigate('/employees')}
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
            {isEdit ? 'Update Employee' : 'Create Employee'}
          </button>
        </div>
      </form>
    </div>
  )
}
