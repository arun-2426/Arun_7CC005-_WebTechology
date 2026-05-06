import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import { MemoryRouter } from 'react-router-dom'
import Layout from '../Layout'
import { AuthProvider } from '../../auth/AuthContext'

/**
 * Sanity check that the navbar mounts and renders the brand. We
 * deliberately don't sign in the AuthContext here — the Layout's
 * navbar should still render even when the user prop is null
 * (the ProtectedRoute is what blocks access, not the layout itself).
 */
describe('<Layout/>', () => {
  it('renders the Cricket Diary brand', () => {
    render(
      <MemoryRouter>
        <AuthProvider>
          <Layout />
        </AuthProvider>
      </MemoryRouter>
    )
    expect(screen.getByText(/Cricket Diary/i)).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /Dashboard/i })).toBeInTheDocument()
    expect(screen.getByRole('link', { name: /Matches/i })).toBeInTheDocument()
  })
})
