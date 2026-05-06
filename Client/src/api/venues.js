import api from '../lib/api'

export function listVenues() {
  return api.get('/venues').then((r) => r.data.data)
}

export function createVenue(payload) {
  return api.post('/venues', payload).then((r) => r.data.data)
}

export function updateVenue(id, payload) {
  return api.put(`/venues/${id}`, payload).then((r) => r.data.data)
}

export function deleteVenue(id) {
  return api.delete(`/venues/${id}`)
}
