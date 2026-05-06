import axios from 'axios'

// Single axios instance for the whole app. Components should import this
// rather than calling axios directly so we don't sprinkle base-URL
// strings or auth-header wiring across the codebase.
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  // We never use cookies for auth (Sanctum tokens go in the
  // Authorization header) so withCredentials stays false.
  withCredentials: false,
  headers: {
    Accept: 'application/json',
  },
})

// Read the persisted token on every request rather than caching it in
// module scope. This way logging out from one tab keeps subsequent
// requests in another tab unauthenticated too — they read localStorage,
// see no token, and get 401s back.
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('cricket_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// One global place to react to auth expiry. If the API rejects us with
// 401 we clear the stored token and bounce to /login. The router picks
// it up via the AuthContext.
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('cricket_token')
      // Avoid a redirect loop on the login page itself.
      if (!window.location.pathname.startsWith('/login')) {
        window.location.replace('/login')
      }
    }
    return Promise.reject(error)
  }
)

export default api
