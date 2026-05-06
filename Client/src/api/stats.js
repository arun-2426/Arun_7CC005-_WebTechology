import api from '../lib/api'

export function getSummary() {
  return api.get('/stats/summary').then((r) => r.data)
}
