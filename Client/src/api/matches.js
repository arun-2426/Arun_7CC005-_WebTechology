import api from '../lib/api'

// Thin wrappers around the matches endpoints. Each returns the parsed
// payload so pages can stay declarative — no axios noise in components.

export function listMatches(params = {}) {
  return api.get('/matches', { params }).then((r) => r.data)
}

export function getMatch(id) {
  return api.get(`/matches/${id}`).then((r) => r.data.data)
}

export function createMatch(payload) {
  return api.post('/matches', payload).then((r) => r.data.data)
}

export function updateMatch(id, payload) {
  return api.put(`/matches/${id}`, payload).then((r) => r.data.data)
}

export function deleteMatch(id) {
  return api.delete(`/matches/${id}`)
}
