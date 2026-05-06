import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { Card, Row, Col, Spinner, Alert, Button, Badge, Table } from 'react-bootstrap'
import { getMatch } from '../api/matches'

export default function MatchDetailPage() {
  const { id } = useParams()
  const [match, setMatch] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    getMatch(id).then(setMatch).catch(() => setError('Could not load this match.'))
  }, [id])

  if (error) return <Alert variant="danger">{error}</Alert>
  if (!match) return <div className="text-center py-5"><Spinner animation="border" /></div>

  const p = match.performance

  return (
    <>
      <div className="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
        <div>
          <h1 className="h3 mb-1">{match.match_date}</h1>
          <Badge bg="info" className="me-2">{match.format}</Badge>
          {match.result && <Badge bg="secondary">{match.result.replace('_', ' ')}</Badge>}
        </div>
        <div className="d-flex gap-2">
          <Button as={Link} to={`/matches/${id}/edit`} variant="outline-primary">Edit</Button>
          <Button as={Link} to="/matches" variant="outline-secondary">Back</Button>
        </div>
      </div>

      <Row className="g-3">
        <Col md={6}>
          <Card>
            <Card.Body>
              <Card.Title>Fixture</Card.Title>
              <dl className="row mb-0">
                <dt className="col-sm-4">Your team</dt>
                <dd className="col-sm-8">{match.own_team?.name ?? '—'}</dd>
                <dt className="col-sm-4">Opponent</dt>
                <dd className="col-sm-8">{match.opponent_team?.name ?? '—'}</dd>
                <dt className="col-sm-4">Venue</dt>
                <dd className="col-sm-8">{match.venue?.name ?? '—'}</dd>
                <dt className="col-sm-4">Score</dt>
                <dd className="col-sm-8">
                  {match.own_team_score ?? '—'}
                  {match.opponent_score && <> vs {match.opponent_score}</>}
                </dd>
              </dl>
            </Card.Body>
          </Card>
        </Col>

        <Col md={6}>
          <Card>
            <Card.Body>
              <Card.Title>Your performance</Card.Title>
              {p ? (
                <Table size="sm" borderless className="mb-0">
                  <tbody>
                    <tr><td>Batting</td><td>
                      {p.batting_runs} ({p.batting_balls}) — {p.batting_fours}×4, {p.batting_sixes}×6
                      {p.strike_rate !== null && <span className="text-muted"> · SR {p.strike_rate}</span>}
                    </td></tr>
                    <tr><td>Dismissal</td><td>{p.batting_dismissal?.replace('_', ' ') ?? '—'}</td></tr>
                    <tr><td>Bowling</td><td>
                      {p.bowling_overs ?? '—'}–{p.bowling_maidens}–{p.bowling_runs}–{p.bowling_wickets}
                      {p.economy !== null && <span className="text-muted"> · Econ {p.economy}</span>}
                    </td></tr>
                    <tr><td>Fielding</td><td>
                      {p.fielding_catches} catches, {p.fielding_stumpings} stumpings, {p.fielding_runouts} run outs
                    </td></tr>
                  </tbody>
                </Table>
              ) : (
                <p className="text-muted mb-0">No performance recorded.</p>
              )}
            </Card.Body>
          </Card>
        </Col>

        {match.notes && (
          <Col xs={12}>
            <Card>
              <Card.Body>
                <Card.Title>Notes</Card.Title>
                <p className="mb-0" style={{ whiteSpace: 'pre-wrap' }}>{match.notes}</p>
              </Card.Body>
            </Card>
          </Col>
        )}
      </Row>
    </>
  )
}
