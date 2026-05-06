import { Navigate } from 'react-router-dom'
import { Spinner } from 'react-bootstrap'
import { useAuth } from '../auth/AuthContext'

/**
 * Gate component. Wraps any tree that requires a logged-in user. While
 * the AuthContext is hydrating from /me we show a spinner; if there's
 * still no user after that, we bounce to /login.
 */
export default function ProtectedRoute({ children }) {
  const { user, loading } = useAuth()

  if (loading) {
    return (
      <div className="d-flex justify-content-center align-items-center" style={{ minHeight: '60vh' }}>
        <Spinner animation="border" />
      </div>
    )
  }

  if (!user) {
    return <Navigate to="/login" replace />
  }

  return children
}
