import { useEffect, useState } from 'react'
import { Card, Form, Button, Table, Row, Col, Alert } from 'react-bootstrap'
import { listVenues, createVenue, updateVenue, deleteVenue } from '../api/venues'

const blank = { name: '', city: '', country: '', pitch_type: 'turf', notes: '' }

const PITCHES = [
  ['turf', 'Turf'],
  ['matting', 'Matting'],
  ['astro', 'Astro'],
  ['concrete', 'Concrete'],
  ['other', 'Other'],
]

export default function VenuesPage() {
  const [venues, setVenues] = useState([])
  const [form, setForm] = useState(blank)
  const [editing, setEditing] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => { reload() }, [])
  function reload() { listVenues().then(setVenues) }

  async function handleSubmit(e) {
    e.preventDefault()
    setError(null)
    try {
      if (editing) await updateVenue(editing, form)
      else await createVenue(form)
      setForm(blank)
      setEditing(null)
      reload()
    } catch (err) {
      const data = err.response?.data
      setError(data?.errors ? Object.values(data.errors).flat().join(' ') : (data?.message ?? 'Save failed.'))
    }
  }

  function startEdit(v) {
    setEditing(v.id)
    setForm({
      name: v.name,
      city: v.city ?? '',
      country: v.country ?? '',
      pitch_type: v.pitch_type ?? 'turf',
      notes: v.notes ?? '',
    })
  }

  async function handleDelete(v) {
    if (!confirm(`Delete "${v.name}"?`)) return
    await deleteVenue(v.id)
    reload()
  }

  return (
    <>
      <h1 className="h3 mb-3">Venues</h1>
      <Row className="g-3">
        <Col lg={5}>
          <Card><Card.Body>
            <Card.Title>{editing ? 'Edit venue' : 'Add a venue'}</Card.Title>
            {error && <Alert variant="danger">{error}</Alert>}
            <Form onSubmit={handleSubmit}>
              <Form.Group className="mb-3">
                <Form.Label>Name</Form.Label>
                <Form.Control required value={form.name}
                  onChange={(e) => setForm({ ...form, name: e.target.value })} />
              </Form.Group>
              <Row className="g-2 mb-3">
                <Col xs={7}>
                  <Form.Group>
                    <Form.Label>City</Form.Label>
                    <Form.Control value={form.city}
                      onChange={(e) => setForm({ ...form, city: e.target.value })} />
                  </Form.Group>
                </Col>
                <Col xs={5}>
                  <Form.Group>
                    <Form.Label>Country</Form.Label>
                    <Form.Control value={form.country}
                      onChange={(e) => setForm({ ...form, country: e.target.value })} />
                  </Form.Group>
                </Col>
              </Row>
              <Form.Group className="mb-3">
                <Form.Label>Pitch type</Form.Label>
                <Form.Select value={form.pitch_type}
                  onChange={(e) => setForm({ ...form, pitch_type: e.target.value })}>
                  {PITCHES.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                </Form.Select>
              </Form.Group>
              <Form.Group className="mb-3">
                <Form.Label>Notes</Form.Label>
                <Form.Control as="textarea" rows={2} value={form.notes}
                  onChange={(e) => setForm({ ...form, notes: e.target.value })} />
              </Form.Group>
              <div className="d-flex gap-2">
                <Button type="submit">{editing ? 'Save changes' : 'Add venue'}</Button>
                {editing && (
                  <Button variant="outline-secondary"
                          onClick={() => { setEditing(null); setForm(blank) }}>Cancel</Button>
                )}
              </div>
            </Form>
          </Card.Body></Card>
        </Col>

        <Col lg={7}>
          <Card><Card.Body>
            <Card.Title>Your venues</Card.Title>
            {venues.length === 0 ? (
              <p className="text-muted mb-0">None yet.</p>
            ) : (
              <div className="table-responsive">
                <Table hover className="align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Name</th><th>City</th><th>Pitch</th><th></th>
                    </tr>
                  </thead>
                  <tbody>
                    {venues.map((v) => (
                      <tr key={v.id}>
                        <td>{v.name}</td>
                        <td>{v.city ?? '—'}</td>
                        <td>{v.pitch_type}</td>
                        <td className="text-end">
                          <Button size="sm" variant="outline-primary" className="me-1" onClick={() => startEdit(v)}>Edit</Button>
                          <Button size="sm" variant="outline-danger" onClick={() => handleDelete(v)}>Delete</Button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </Table>
              </div>
            )}
          </Card.Body></Card>
        </Col>
      </Row>
    </>
  )
}
