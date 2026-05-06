import { createContext, useContext, useEffect, useState } from 'react'
import api from '../lib/api'

const AuthContext = createContext(null)

/**
 * Wraps the app, holds the current user object, and exposes login /
 * register / logout helpers. Anything that needs to know "who am I"
 * pulls from useAuth().
 */
export function AuthProvider({ children }) {
  const [user, setUser] = useState(null)
  // 'loading' covers the initial /me hydrate on a page refresh so we
  // don't flash the login screen for a logged-in user.
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    const token = localStorage.getItem('cricket_token')
    if (!token) {
      setLoading(false)
      return
    }
    api.get('/auth/me')
      .then((res) => setUser(res.data.data))
      .catch(() => {
        // Stale token — wipe it. The 401 interceptor in lib/api.js will
        // also redirect, but covering this here avoids a brief flash.
        localStorage.removeItem('cricket_token')
      })
      .finally(() => setLoading(false))
  }, [])

  async function login(email, password) {
    const res = await api.post('/auth/login', { email, password })
    localStorage.setItem('cricket_token', res.data.token)
    setUser(res.data.user)
  }

  async function register(payload) {
    const res = await api.post('/auth/register', payload)
    localStorage.setItem('cricket_token', res.data.token)
    setUser(res.data.user)
  }

  async function logout() {
    try {
      await api.post('/auth/logout')
    } catch {
      // If the token was already invalid the server will 401 — we
      // still want to clear the local state.
    }
    localStorage.removeItem('cricket_token')
    setUser(null)
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used inside <AuthProvider>')
  return ctx
}
