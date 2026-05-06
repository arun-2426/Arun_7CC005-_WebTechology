import { describe, it, expect } from 'vitest'
import { strikeRate, formatResult } from '../format'

describe('strikeRate', () => {
  it('returns runs per 100 balls', () => {
    expect(strikeRate(64, 41)).toBe(156.1)
    expect(strikeRate(50, 50)).toBe(100)
  })

  it('returns null when no balls were faced', () => {
    expect(strikeRate(10, 0)).toBeNull()
    expect(strikeRate(0, 0)).toBeNull()
    expect(strikeRate(5, null)).toBeNull()
  })
})

describe('formatResult', () => {
  it('replaces underscores with spaces', () => {
    expect(formatResult('no_result')).toBe('no result')
  })
  it('passes through simple values', () => {
    expect(formatResult('won')).toBe('won')
  })
  it('returns dash for empty input', () => {
    expect(formatResult(null)).toBe('—')
    expect(formatResult('')).toBe('—')
  })
})
