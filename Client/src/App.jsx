import { Routes, Route, Navigate } from 'react-router-dom'
import { AuthProvider } from './auth/AuthContext'
import ProtectedRoute from './components/ProtectedRoute'
import Layout from './components/Layout'
import LoginPage from './pages/LoginPage'
import RegisterPage from './pages/RegisterPage'
import DashboardPage from './pages/DashboardPage'
import MatchesPage from './pages/MatchesPage'
import MatchFormPage from './pages/MatchFormPage'
import MatchDetailPage from './pages/MatchDetailPage'
import TeamsPage from './pages/TeamsPage'
import VenuesPage from './pages/VenuesPage'

export default function App() {
  return (
    <AuthProvider>
      <Routes>
        {/* Public auth pages */}
        <Route path="/login"    element={<LoginPage />} />
        <Route path="/register" element={<RegisterPage />} />

        {/* Everything below sits inside the authenticated shell */}
        <Route element={<ProtectedRoute><Layout /></ProtectedRoute>}>
          <Route index element={<DashboardPage />} />
          <Route path="matches"           element={<MatchesPage />} />
          <Route path="matches/new"       element={<MatchFormPage />} />
          <Route path="matches/:id"       element={<MatchDetailPage />} />
          <Route path="matches/:id/edit"  element={<MatchFormPage />} />
          <Route path="teams"             element={<TeamsPage />} />
          <Route path="venues"            element={<VenuesPage />} />
        </Route>

        {/* Anything else: send the user home and let ProtectedRoute decide */}
        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </AuthProvider>
  )
}
