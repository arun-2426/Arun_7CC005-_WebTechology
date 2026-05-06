import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Table, Button, Form, Row, Col, Spinner, Alert, Badge, Card } from 'react-bootstrap'
import { listMatches } from '../api/matches'

const RESULT_VARIANT = {
  won: 'success',
  lost: 'danger',
  drawn: 'secondary',
  tied: 'info',
  no_result: 'light',
}

export default function MatchesPage() {
  const [matches, setMatches] = useState(null)
  const [error, setError] = useState(null)
  const [filters, setFilters] = useState({ format: '', result: '' })

  useEffect(() => {
    setMatches(null)
    setError(null)

    // Strip empty filters before sending — Laravel's `filled` would
    // ignore them anyway but keeping the URL clean is nicer to debug.
    const params = Object.fromEntries(
      Object.entries(filters).filter(([, v]) => v !== '')
    )

    listMatches(params)
      .then((res) => setMatches(res.data ?? []))
      .catch(() => setError('Could not load matches.'))
  }, [filters])

  return (
    <>
      <div className="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h1 className="h3 mb-0">Matches</h1>
        <Button as={Link} to="/matches/new" variant="primary">+ Add match</Button>
      </div>

      <Card className="mb-3">
        <Card.Body>
          <Form>
            <Row className="g-2">
              <Col xs={6} md={3}>
                <Form.Select
                  value={filters.format}
                  onChange={(e) => setFilters((f) => ({ ...f, format: e.target.value }))}
                  aria-label="Format filter"
                >
                  <option value="">All formats</option>
                  <option>T20</option>
                  <option>ODI</option>
                  <option>Test</option>
                  <option>Club</option>
                  <option>Other</option>
                </Form.Select>
              </Col>
              <Col xs={6} md={3}>
                <Form.Select
                  value={filters.result}
                  onChange={(e) => setFilters((f) => ({ ...f, result: e.target.value }))}
                  aria-label="Result filter"
                >
                  <option value="">All results</option>
                  <option value="won">Won</option>
                  <option value="lost">Lost</option>
                  <option value="drawn">Drawn</option>
                  <option value="tied">Tied</option>
                  <option value="no_result">No result</option>
                </Form.Select>
              </Col>
            </Row>
          </Form>
        </Card.Body>
      </Card>

      {error && <Alert variant="danger">{error}</Alert>}

      {matches === null ? (
        <div className="text-center py-5"><Spinner animation="border" /></div>
      ) : matches.length === 0 ? (
        <Card><Card.Body className="text-center py-4 text-muted">
          No matches found. Try clearing filters or <Link to="/matches/new">add one</Link>.
        </Card.Body></Card>
      ) : (
        <div className="table-responsive">
          <Table hover className="align-middle">
            <thead>
              <tr>
                <th>Date</th>
                <th>Format</th>
                <th>Opponent</th>
                <th>Venue</th>
                <th>Score</th>
                <th>Result</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              {matches.map((m) => (
                <tr key={m.id}>
                  <td>{m.match_date}</td>
                  <td><Badge bg="info" pill>{m.format}</Badge></td>
                  <td>{m.opponent_team?.name ?? '—'}</td>
                  <td>{m.venue?.name ?? '—'}</td>
                  <td>
                    {m.own_team_score ?? '—'}
                    {m.opponent_score && <> vs {m.opponent_score}</>}
                  </td>
                  <td>
                    {m.result ? (
                      <Badge bg={RESULT_VARIANT[m.result] ?? 'secondary'}>
                        {m.result.replace('_', ' ')}
                      </Badge>
                    ) : <span className="text-muted">—</span>}
                  </td>
                  <td className="text-end">
                    <Button as={Link} to={`/matches/${m.id}`} size="sm" variant="outline-primary">
                      View
                    </Button>
                  </td>
                </tr>
              ))}
            </tbody>
          </Table>
        </div>
      )}
    </>
  )
}
