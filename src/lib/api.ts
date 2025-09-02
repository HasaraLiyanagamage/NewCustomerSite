import axios, { InternalAxiosRequestConfig, AxiosResponse, AxiosError } from 'axios'

// TODO: Replace with environment variable when TypeScript config is fixed
const API_BASE_URL = 'http://localhost:5000/api'

export const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  },
})

// Request interceptor to add auth token
api.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    const token = localStorage.getItem('token')
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error: AxiosError) => {
    return Promise.reject(error)
  }
)

// Response interceptor to handle auth errors
api.interceptors.response.use(
  (response: AxiosResponse) => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// Define types for API parameters
interface LoginCredentials {
  username: string
  password: string
}

interface User {
  id?: string
  username: string
  email: string
  first_name: string
  last_name: string
  role: string
  password?: string
}

interface Customer {
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
  created_by?: string
  created_at?: string
  updated_at?: string
}

interface CustomerParams {
  search?: string
  page?: number
  limit?: number
}

// Auth API
export const authAPI = {
  login: (credentials: LoginCredentials) =>
    api.post('/auth/login', credentials),
  logout: () => api.post('/auth/logout'),
  getProfile: () => api.get('/auth/profile'),
}

// Users API
export const usersAPI = {
  getAll: () => api.get('/users'),
  getById: (id: string) => api.get(`/users/${id}`),
  create: (user: User) => api.post('/users', user),
  update: (id: string, user: User) => api.put(`/users/${id}`, user),
  delete: (id: string) => api.delete(`/users/${id}`),
}

// Customers API
export const customersAPI = {
  getAll: async (params?: CustomerParams) => {
    const response = await api.get('/customers', { 
      params,
      // This ensures we get the response headers
      transformResponse: (res, headers) => {
        try {
          return {
            data: JSON.parse(res),
            headers: headers
          };
        } catch (e) {
          return {
            data: res,
            headers: headers
          };
        }
      }
    });
    return response;
  },
  getById: (id: string) => api.get(`/customers/${id}`),
  create: (customer: Customer) => api.post('/customers', customer),
  update: (id: string, customer: Customer) => api.put(`/customers/${id}`, customer),
  delete: (id: string) => api.delete(`/customers/${id}`),
}

// Dashboard API
export const dashboardAPI = {
  getStats: () => api.get('/dashboard/stats'),
  getRecentCustomers: () => api.get('/dashboard/recent-customers'),
}
