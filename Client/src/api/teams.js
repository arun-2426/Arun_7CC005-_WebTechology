import api from '../lib/api'

export function listTeams() {
  return api.get('/teams').then((r) => r.data.data)
}

export function createTeam(payload) {
  return api.post('/teams', payload).then((r) => r.data.data)
}

export function updateTeam(id, payload) {
  return api.put(`/teams/${id}`, payload).then((r) => r.data.data)
}

export function deleteTeam(id) {
  return api.delete(`/teams/${id}`)
}
