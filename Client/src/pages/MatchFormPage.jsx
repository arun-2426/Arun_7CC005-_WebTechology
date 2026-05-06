import { useEffect, useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import { Form, Button, Row, Col, Card, Alert, Spinner } from 'react-bootstrap'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { listTeams } from '../api/teams'
import { listVenues } from '../api/venues'
import { createMatch, getMatch, updateMatch, deleteMatch } from '../api/matches'

// Client-side schema. Server is authoritative — this is just for UX.
// Keeping bowling overs as a flexible string mirrors the cricket
// notation (`4.3` = four overs and three balls).
const schema = z.object({
  match_date: z.string().min(1, 'Date is required'),
  format: z.enum(['T20', 'ODI', 'Test', 'Club', 'Other']),
  own_team_id: z.string().optional(),
  opponent_team_id: z.string().optional(),
  venue_id: z.string().optional(),
  result: z.string().optional(),
  own_team_score: z.string().optional(),
  opponent_score: z.string().optional(),
  notes: z.string().optional(),

  performance: z.object({
    batting_runs: z.coerce.number().min(0).max(600).optional(),
    batting_balls: z.coerce.number().min(0).max(1000).optional(),
    batting_fours: z.coerce.number().min(0).max(100).optional(),
    batting_sixes: z.coerce.number().min(0).max(100).optional(),
    batting_dismissal: z.string().optional(),
    bowling_overs: z.string().optional(),
    bowling_maidens: z.coerce.number().min(0).max(50).optional(),
    bowling_runs: z.coerce.number().min(0).max(500).optional(),
    bowling_wickets: z.coerce.number().min(0).max(10).optional(),
    fielding_catches: z.coerce.number().min(0).max(20).optional(),
    fielding_stumpings: z.coerce.number().min(0).max(20).optional(),
    fielding_runouts: z.coerce.number().min(0).max(20).optional(),
  }).optional(),
})

const DISMISSALS = [
  ['did_not_bat', 'Did not bat'],
  ['not_out', 'Not out'],
  ['bowled', 'Bowled'],
  ['caught', 'Caught'],
  ['lbw', 'LBW'],
  ['run_out', 'Run out'],
  ['stumped', 'Stumped'],
  ['hit_wicket', 'Hit wicket'],
  ['retired', 'Retired'],
]

export default function MatchFormPage() {
  const { id } = useParams()
  const isEdit = Boolean(id)
  const navigate = useNavigate()

  const [teams, setTeams] = useState([])
  const [venues, setVenues] = useState([])
  const [loading, setLoading] = useState(isEdit)
  const [serverErrors, setServerErrors] = useState(null)

  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm({
    resolver: zodResolver(schema),
    defaultValues: {
      match_date: new Date().toISOString().slice(0, 10),
      format: 'Club',
      performance: {
        batting_dismissal: 'did_not_bat',
      },
    },
  })

  // Pull dropdown data + the existing match in parallel.
  useEffect(() => {
    let alive = true
    Promise.all([listTeams(), listVenues()])
      .then(([t, v]) => {
        if (!alive) return
        setTeams(t)
        setVenues(v)
      })
    if (isEdit) {
      getMatch(id).then((m) => {
        if (!alive) return
        // Coerce nullable FKs to '' so the <select> shows "—" again.
        reset({
          match_date: m.match_date,
          format: m.format,
          own_team_id: m.own_team?.id?.toString() ?? '',
          opponent_team_id: m.opponent_team?.id?.toString() ?? '',
          venue_id: m.venue?.id?.toString() ?? '',
          result: m.result ?? '',
          own_team_score: m.own_team_score ?? '',
          opponent_score: m.opponent_score ?? '',
          notes: m.notes ?? '',
          performance: m.performance ?? { batting_dismissal: 'did_not_bat' },
        })
        setLoading(false)
      })
    }
    return () => { alive = false }
  }, [id, isEdit, reset])

  async function onSubmit(values) {
    setServerErrors(null)

    // Strip empty strings on FK fields so they go as nulls — the API
    // expects either an integer ID or null, not "".
    const payload = {
      ...values,
      own_team_id: values.own_team_id ? Number(values.own_team_id) : null,
      opponent_team_id: values.opponent_team_id ? Number(values.opponent_team_id) : null,
      venue_id: values.venue_id ? Number(values.venue_id) : null,
      result: values.result || null,
    }

    try {
      const saved = isEdit
        ? await updateMatch(id, payload)
        : await createMatch(payload)
      navigate(`/matches/${saved.id}`)
    } catch (err) {
      const data = err.response?.data
      if (data?.errors) {
        setServerErrors(Object.values(data.errors).flat())
      } else {
        setServerErrors([data?.message ?? 'Could not save the match.'])
      }
    }
  }

  async function handleDelete() {
    if (!confirm('Delete this match? This cannot be undone.')) return
    await deleteMatch(id)
    navigate('/matches')
  }

  if (loading) return <div className="text-center py-5"><Spinner animation="border" /></div>

  return (
    <>
      <h1 className="h3 mb-3">{isEdit ? 'Edit match' : 'Log a match'}</h1>

      {serverErrors && (
        <Alert variant="danger">
          <ul className="mb-0">{serverErrors.map((m, i) => <li key={i}>{m}</li>)}</ul>
        </Alert>
      )}

      <Form onSubmit={handleSubmit(onSubmit)} noValidate>
        <Card className="mb-3">
          <Card.Body>
            <Card.Title>Fixture</Card.Title>
            <Row className="g-3">
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Date</Form.Label>
                  <Form.Control type="date" isInvalid={!!errors.match_date} {...register('match_date')} />
                  <Form.Control.Feedback type="invalid">{errors.match_date?.message}</Form.Control.Feedback>
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Format</Form.Label>
                  <Form.Select {...register('format')}>
                    <option>T20</option><option>ODI</option><option>Test</option>
                    <option>Club</option><option>Other</option>
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Result</Form.Label>
                  <Form.Select {...register('result')}>
                    <option value="">—</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                    <option value="drawn">Drawn</option>
                    <option value="tied">Tied</option>
                    <option value="no_result">No result</option>
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Your team</Form.Label>
                  <Form.Select {...register('own_team_id')}>
                    <option value="">—</option>
                    {teams.filter((t) => t.type === 'own').map((t) => (
                      <option key={t.id} value={t.id}>{t.name}</option>
                    ))}
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Opponent</Form.Label>
                  <Form.Select {...register('opponent_team_id')}>
                    <option value="">—</option>
                    {teams.filter((t) => t.type === 'opponent').map((t) => (
                      <option key={t.id} value={t.id}>{t.name}</option>
                    ))}
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Venue</Form.Label>
                  <Form.Select {...register('venue_id')}>
                    <option value="">—</option>
                    {venues.map((v) => (
                      <option key={v.id} value={v.id}>{v.name}</option>
                    ))}
                  </Form.Select>
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group>
                  <Form.Label>Your team score</Form.Label>
                  <Form.Control placeholder="e.g. 184/6" {...register('own_team_score')} />
                </Form.Group>
              </Col>
              <Col md={6}>
                <Form.Group>
                  <Form.Label>Opponent score</Form.Label>
                  <Form.Control placeholder="e.g. 160/9" {...register('opponent_score')} />
                </Form.Group>
              </Col>
            </Row>
          </Card.Body>
        </Card>

        <Card className="mb-3">
          <Card.Body>
            <Card.Title>Your performance</Card.Title>
            <h6 className="text-muted">Batting</h6>
            <Row className="g-3 mb-3">
              <Col xs={6} md={2}>
                <Form.Group>
                  <Form.Label>Runs</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.batting_runs')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={2}>
                <Form.Group>
                  <Form.Label>Balls</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.batting_balls')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={2}>
                <Form.Group>
                  <Form.Label>Fours</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.batting_fours')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={2}>
                <Form.Group>
                  <Form.Label>Sixes</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.batting_sixes')} />
                </Form.Group>
              </Col>
              <Col md={4}>
                <Form.Group>
                  <Form.Label>Dismissal</Form.Label>
                  <Form.Select {...register('performance.batting_dismissal')}>
                    {DISMISSALS.map(([v, l]) => <option key={v} value={v}>{l}</option>)}
                  </Form.Select>
                </Form.Group>
              </Col>
            </Row>

            <h6 className="text-muted">Bowling</h6>
            <Row className="g-3 mb-3">
              <Col xs={6} md={3}>
                <Form.Group>
                  <Form.Label>Overs</Form.Label>
                  <Form.Control placeholder="e.g. 4 or 4.3" {...register('performance.bowling_overs')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={3}>
                <Form.Group>
                  <Form.Label>Maidens</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.bowling_maidens')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={3}>
                <Form.Group>
                  <Form.Label>Runs conceded</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.bowling_runs')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={3}>
                <Form.Group>
                  <Form.Label>Wickets</Form.Label>
                  <Form.Control type="number" min="0" max="10" {...register('performance.bowling_wickets')} />
                </Form.Group>
              </Col>
            </Row>

            <h6 className="text-muted">Fielding</h6>
            <Row className="g-3">
              <Col xs={6} md={4}>
                <Form.Group>
                  <Form.Label>Catches</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.fielding_catches')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={4}>
                <Form.Group>
                  <Form.Label>Stumpings</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.fielding_stumpings')} />
                </Form.Group>
              </Col>
              <Col xs={6} md={4}>
                <Form.Group>
                  <Form.Label>Run outs</Form.Label>
                  <Form.Control type="number" min="0" {...register('performance.fielding_runouts')} />
                </Form.Group>
              </Col>
            </Row>
          </Card.Body>
        </Card>

        <Card className="mb-3">
          <Card.Body>
            <Form.Group>
              <Form.Label>Notes</Form.Label>
              <Form.Control as="textarea" rows={3} {...register('notes')} />
            </Form.Group>
          </Card.Body>
        </Card>

        <div className="d-flex gap-2 flex-wrap">
          <Button type="submit" variant="primary" disabled={isSubmitting}>
            {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Save match'}
          </Button>
          <Button variant="outline-secondary" onClick={() => navigate(-1)}>Cancel</Button>
          {isEdit && (
            <Button variant="outline-danger" onClick={handleDelete} className="ms-auto">
              Delete match
            </Button>
          )}
        </div>
      </Form>
    </>
  )
}
