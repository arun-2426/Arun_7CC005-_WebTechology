import { useEffect, useState } from 'react'
import { Card, Form, Button, Table, Row, Col, Alert, Badge } from 'react-bootstrap'
import { listTeams, createTeam, updateTeam, deleteTeam } from '../api/teams'

const blank = { name: '', type: 'opponent', home_ground: '', notes: '' }

export default function TeamsPage() {
  const [teams, setTeams] = useState([])
  const [form, setForm] = useState(blank)
  const [editing, setEditing] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => { reload() }, [])

  function reload() {
    listTeams().then(setTeams)
  }

  async function handleSubmit(e) {
    e.preventDefault()
    setError(null)
    try {
      if (editing) {
        await updateTeam(editing, form)
      } else {
        await createTeam(form)
      }
      setForm(blank)
      setEditing(null)
      reload()
    } catch (err) {
      const data = err.response?.data
      setError(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message ?? 'Save failed.'))
    }
  }

  function startEdit(team) {
    setEditing(team.id)
    setForm({
      name: team.name,
      type: team.type,
      home_ground: team.home_ground ?? '',
      notes: team.notes ?? '',
    })
  }

  async function handleDelete(team) {
    if (!confirm(`Delete "${team.name}"? Matches that reference it will keep working but will lose the link.`)) return
    await deleteTeam(team.id)
    reload()
  }

  return (
    <>
      <h1 className="h3 mb-3">Teams</h1>

      <Row className="g-3">
        <Col lg={5}>
          <Card>
            <Card.Body>
              <Card.Title>{editing ? 'Edit team' : 'Add a team'}</Card.Title>
              {error && <Alert variant="danger">{error}</Alert>}
              <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3">
                  <Form.Label>Name</Form.Label>
                  <Form.Control
                    required
                    value={form.name}
                    onChange={(e) => setForm({ ...form, name: e.target.value })}
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Type</Form.Label>
                  <Form.Select
                    value={form.type}
                    onChange={(e) => setForm({ ...form, type: e.target.value })}
                  >
                    <option value="own">Your team</option>
                    <option value="opponent">Opponent</option>
                  </Form.Select>
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Home ground</Form.Label>
                  <Form.Control
                    value={form.home_ground}
                    onChange={(e) => setForm({ ...form, home_ground: e.target.value })}
                  />
                </Form.Group>
                <Form.Group className="mb-3">
                  <Form.Label>Notes</Form.Label>
                  <Form.Control as="textarea" rows={2}
                    value={form.notes}
                    onChange={(e) => setForm({ ...form, notes: e.target.value })}
                  />
                </Form.Group>
                <div className="d-flex gap-2">
                  <Button type="submit">{editing ? 'Save changes' : 'Add team'}</Button>
                  {editing && (
                    <Button variant="outline-secondary" onClick={() => { setEditing(null); setForm(blank) }}>
                      Cancel
                    </Button>
                  )}
                </div>
              </Form>
            </Card.Body>
          </Card>
        </Col>

        <Col lg={7}>
          <Card>
            <Card.Body>
              <Card.Title>Your teams</Card.Title>
              {teams.length === 0 ? (
                <p className="text-muted mb-0">None yet — add one to start linking matches.</p>
              ) : (
                <div className="table-responsive">
                  <Table hover className="align-middle mb-0">
                    <thead>
                      <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Home ground</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      {teams.map((t) => (
                        <tr key={t.id}>
                          <td>{t.name}</td>
                          <td><Badge bg={t.type === 'own' ? 'success' : 'secondary'}>{t.type}</Badge></td>
                          <td>{t.home_ground ?? '—'}</td>
                          <td className="text-end">
                            <Button size="sm" variant="outline-primary" className="me-1" onClick={() => startEdit(t)}>Edit</Button>
                            <Button size="sm" variant="outline-danger" onClick={() => handleDelete(t)}>Delete</Button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </Table>
                </div>
              )}
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </>
  )
}
