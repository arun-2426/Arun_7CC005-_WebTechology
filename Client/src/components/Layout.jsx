import { Link, NavLink, Outlet, useNavigate } from 'react-router-dom'
import { Container, Nav, Navbar, Button } from 'react-bootstrap'
import { useAuth } from '../auth/AuthContext'

/**
 * App shell — the navbar that sits above every authenticated page,
 * plus the <Outlet/> that renders the matched route. The layout itself
 * is responsive: react-bootstrap's <Navbar expand="lg"> collapses to a
 * burger menu on screens narrower than the lg breakpoint.
 */
export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  async function handleLogout() {
    await logout()
    navigate('/login')
  }

  return (
    <>
      <Navbar bg="dark" variant="dark" expand="lg" sticky="top">
        <Container>
          <Navbar.Brand as={Link} to="/">🏏 Cricket Diary</Navbar.Brand>
          <Navbar.Toggle aria-controls="main-nav" />
          <Navbar.Collapse id="main-nav">
            <Nav className="me-auto">
              <Nav.Link as={NavLink} to="/" end>Dashboard</Nav.Link>
              <Nav.Link as={NavLink} to="/matches">Matches</Nav.Link>
              <Nav.Link as={NavLink} to="/teams">Teams</Nav.Link>
              <Nav.Link as={NavLink} to="/venues">Venues</Nav.Link>
            </Nav>
            <Nav>
              {user && (
                <Navbar.Text className="me-3 d-none d-lg-inline">
                  Signed in as <strong>{user.name}</strong>
                </Navbar.Text>
              )}
              <Button size="sm" variant="outline-light" onClick={handleLogout}>
                Log out
              </Button>
            </Nav>
          </Navbar.Collapse>
        </Container>
      </Navbar>

      <Container className="py-4">
        <Outlet />
      </Container>
    </>
  )
}
