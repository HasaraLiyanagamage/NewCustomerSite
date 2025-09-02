import { useState, useEffect } from 'react'
import { useForm } from 'react-hook-form'
import { Save, User, Mail, Calendar } from 'lucide-react'
import { usersAPI } from '../lib/api'
import { useAuth } from '../contexts/AuthContext'
import toast from 'react-hot-toast'

interface ProfileFormData {
  firstName: string
  lastName: string
  email: string
  currentPassword: string
  newPassword: string
  confirmPassword: string
}

export const Profile: React.FC = () => {
  const [loading, setLoading] = useState(false)
  const [userData, setUserData] = useState<any>(null)
  const { user } = useAuth()

  const {
    register,
    handleSubmit,
    formState: { errors },
    reset
  } = useForm<ProfileFormData>()

  useEffect(() => {
    if (user) {
      setUserData(user)
      reset({
        firstName: user.firstName,
        lastName: user.lastName,
        email: user.email,
      })
    }
  }, [user, reset])

  const onSubmit = async (data: ProfileFormData) => {
    try {
      setLoading(true)
      
      const updateData: any = {
        firstName: data.firstName,
        lastName: data.lastName,
        email: data.email,
      }

      // Only include password fields if new password is provided
      if (data.newPassword) {
        updateData.currentPassword = data.currentPassword
        updateData.newPassword = data.newPassword
      }

      await usersAPI.update(user!.id, updateData)
      toast.success('Profile updated successfully')
      
      // Refresh user data
      const response = await usersAPI.getById(user!.id)
      setUserData(response.data)
    } catch (error: any) {
      const message = error.response?.data?.message || 'Failed to update profile'
      toast.error(message)
    } finally {
      setLoading(false)
    }
  }

  if (!userData) {
    return (
      <div className="flex items-center justify-center h-64">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <h1 className="text-2xl font-semibold text-gray-900">My Profile</h1>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Profile Info Card */}
        <div className="lg:col-span-1">
          <div className="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div className="text-center">
              <div className="w-20 h-20 bg-primary-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <User className="h-10 w-10 text-white" />
              </div>
              <h3 className="text-xl font-semibold text-gray-900">
                {userData.firstName} {userData.lastName}
              </h3>
              <p className="text-gray-500 capitalize">{userData.role}</p>
            </div>

            <div className="mt-6 space-y-4">
              <div className="flex items-center text-sm text-gray-600">
                <Mail className="h-4 w-4 mr-3 text-gray-400" />
                <span>{userData.email}</span>
              </div>
              <div className="flex items-center text-sm text-gray-600">
                <User className="h-4 w-4 mr-3 text-gray-400" />
                <span>@{userData.username}</span>
              </div>
              <div className="flex items-center text-sm text-gray-600">
                <Calendar className="h-4 w-4 mr-3 text-gray-400" />
                <span>Joined {new Date(userData.createdAt).toLocaleDateString()}</span>
              </div>
            </div>
          </div>
        </div>

        {/* Profile Form */}
        <div className="lg:col-span-2">
          <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
            {/* Personal Information */}
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
                      {...register('firstName', { required: 'First name is required' })}
                      className="input"
                      placeholder="Enter first name"
                    />
                    {errors.firstName && (
                      <p className="mt-1 text-sm text-red-600">{errors.firstName.message}</p>
                    )}
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Last Name *
                    </label>
                    <input
                      {...register('lastName', { required: 'Last name is required' })}
                      className="input"
                      placeholder="Enter last name"
                    />
                    {errors.lastName && (
                      <p className="mt-1 text-sm text-red-600">{errors.lastName.message}</p>
                    )}
                  </div>
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

                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Username
                  </label>
                  <input
                    type="text"
                    value={userData.username}
                    className="input bg-gray-50"
                    disabled
                  />
                  <p className="mt-1 text-sm text-gray-500">Username cannot be changed</p>
                </div>
              </div>
            </div>

            {/* Change Password */}
            <div className="bg-white rounded-lg shadow-sm border border-gray-200">
              <div className="px-6 py-4 border-b border-gray-200">
                <h2 className="text-lg font-medium text-gray-900">Change Password</h2>
              </div>
              <div className="p-6 space-y-6">
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-2">
                    Current Password
                  </label>
                  <input
                    type="password"
                    {...register('currentPassword')}
                    className="input"
                    placeholder="Enter current password"
                  />
                </div>

                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      New Password
                    </label>
                    <input
                      type="password"
                      {...register('newPassword', {
                        minLength: { value: 6, message: 'Password must be at least 6 characters' }
                      })}
                      className="input"
                      placeholder="Enter new password"
                    />
                    {errors.newPassword && (
                      <p className="mt-1 text-sm text-red-600">{errors.newPassword.message}</p>
                    )}
                  </div>
                  <div>
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                      Confirm New Password
                    </label>
                    <input
                      type="password"
                      {...register('confirmPassword', {
                        validate: (value, formValues) => 
                          value === formValues.newPassword || 'Passwords do not match'
                      })}
                      className="input"
                      placeholder="Confirm new password"
                    />
                    {errors.confirmPassword && (
                      <p className="mt-1 text-sm text-red-600">{errors.confirmPassword.message}</p>
                    )}
                  </div>
                </div>

                <p className="text-sm text-gray-500">
                  Leave password fields blank to keep your current password.
                </p>
              </div>
            </div>

            <div className="flex items-center justify-end">
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
                Update Profile
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  )
}
