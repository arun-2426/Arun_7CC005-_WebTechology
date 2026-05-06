// Tiny pure helpers used by a couple of places in the SPA. Pulled out
// here so they can be unit-tested without spinning up a component tree.

/**
 * Strike rate as runs per 100 balls. Mirrors the server-side calc in
 * Performance::strikeRate(). Returns null if no balls were faced — a
 * 0/0 division would lie to the user.
 */
export function strikeRate(runs, balls) {
  if (!balls || balls <= 0) return null
  return Math.round(((runs / balls) * 100) * 100) / 100
}

/**
 * Pretty-print a `result` enum so the UI doesn't have to keep doing
 * .replace('_', ' ') in every component.
 */
export function formatResult(result) {
  if (!result) return '—'
  return result.replace('_', ' ')
}
