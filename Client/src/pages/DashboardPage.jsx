import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Row, Col, Card, Spinner, Alert, Button } from 'react-bootstrap'
import {
  ResponsiveContainer, LineChart, Line, XAxis, YAxis, Tooltip, CartesianGrid,
  PieChart, Pie, Cell, Legend,
} from 'recharts'
import { getSummary } from '../api/stats'

const RESULT_COLOURS = {
  Wins:       '#198754',
  Losses:     '#dc3545',
  Draws:      '#6c757d',
  Ties:       '#0dcaf0',
  'No result':'#adb5bd',
}

export default function DashboardPage() {
  const [data, setData] = useState(null)
  const [error, setError] = useState(null)

  useEffect(() => {
    getSummary()
      .then(setData)
      .catch(() => setError('Could not load stats. Try again in a moment.'))
  }, [])

  if (error) return <Alert variant="danger">{error}</Alert>

  if (!data) {
    return (
      <div className="text-center py-5">
        <Spinner animation="border" />
      </div>
    )
  }

  // Empty state — first-time users land here with no data at all.
  if (data.totals.matches_total === 0) {
    return (
      <Card className="text-center py-5">
        <Card.Body>
          <h2 className="mb-3">Welcome 👋</h2>
          <p className="text-muted">You haven't logged any matches yet.</p>
          <Button as={Link} to="/matches/new" variant="primary">
            Log your first match
          </Button>
        </Card.Body>
      </Card>
    )
  }

  const resultPie = [
    { name: 'Wins',       value: data.totals.wins },
    { name: 'Losses',     value: data.totals.losses },
    { name: 'Draws',      value: data.totals.draws },
    { name: 'Ties',       value: data.totals.ties },
    { name: 'No result',  value: data.totals.no_results },
  ].filter((d) => d.value > 0)

  const timeline = (data.timeline || []).map((row) => ({
    date: row.match_date,
    runs: row.batting_runs ?? 0,
  }))

  return (
    <>
      <h1 className="h3 mb-4">Dashboard</h1>

      <Row className="g-3 mb-4">
        <SummaryCard label="Matches" value={data.totals.matches_total} />
        <SummaryCard label="Wins"    value={data.totals.wins} variant="success" />
        <SummaryCard label="Runs"    value={data.batting.total_runs} />
        <SummaryCard label="Average" value={data.batting.average ?? '—'} />
        <SummaryCard label="Strike rate" value={data.batting.strike_rate ?? '—'} />
        <SummaryCard label="Wickets" value={data.bowling.total_wickets} />
      </Row>

      <Row className="g-3">
        <Col xs={12} lg={7}>
          <Card>
            <Card.Body>
              <Card.Title>Runs over time</Card.Title>
              <div style={{ width: '100%', height: 280 }}>
                <ResponsiveContainer>
                  <LineChart data={timeline} margin={{ top: 10, right: 10, left: -10, bottom: 0 }}>
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="date" tick={{ fontSize: 12 }} />
                    <YAxis tick={{ fontSize: 12 }} />
                    <Tooltip />
                    <Line type="monotone" dataKey="runs" stroke="#0d6efd" strokeWidth={2} dot={{ r: 3 }} />
                  </LineChart>
                </ResponsiveContainer>
              </div>
            </Card.Body>
          </Card>
        </Col>

        <Col xs={12} lg={5}>
          <Card>
            <Card.Body>
              <Card.Title>Results split</Card.Title>
              <div style={{ width: '100%', height: 280 }}>
                <ResponsiveContainer>
                  <PieChart>
                    <Pie data={resultPie} dataKey="value" nameKey="name" outerRadius={90} label>
                      {resultPie.map((entry) => (
                        <Cell key={entry.name} fill={RESULT_COLOURS[entry.name]} />
                      ))}
                    </Pie>
                    <Legend />
                    <Tooltip />
                  </PieChart>
                </ResponsiveContainer>
              </div>
            </Card.Body>
          </Card>
        </Col>
      </Row>
    </>
  )
}

function SummaryCard({ label, value, variant = 'light' }) {
  return (
    <Col xs={6} md={4} lg={2}>
      <Card bg={variant === 'success' ? 'success' : undefined}
            text={variant === 'success' ? 'white' : undefined}>
        <Card.Body className="text-center">
          <div className="text-uppercase small">{label}</div>
          <div className="fs-3 fw-semibold">{value}</div>
        </Card.Body>
      </Card>
    </Col>
  )
}
