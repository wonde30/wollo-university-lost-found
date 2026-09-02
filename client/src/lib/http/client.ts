import axios, { type AxiosInstance } from 'axios'

/**
 * Shared Axios instance configured for Laravel Sanctum SPA authentication.
 * 
 * Uses session-cookie authentication, NOT bearer tokens.
 * Credentials and CSRF tokens are automatically handled via cookies.
 */
export const apiClient: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
  timeout: 15000,
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
})
