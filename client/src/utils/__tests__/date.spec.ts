import { describe, it, expect } from 'vitest'
import { formatDate, formatDateTime, formatRelativeTime, toISODateInput } from '../date'

describe('Date Utilities', () => {
  it('formats dates in short, medium, and long formats', () => {
    const isoString = '2026-03-15T10:30:00.000Z'

    const short = formatDate(isoString, 'short')
    expect(short).toContain('26')

    const medium = formatDate(isoString, 'medium')
    expect(medium).toContain('2026')
    expect(medium).toContain('Mar')

    const long = formatDateTime(isoString)
    expect(long).toContain('March')
    expect(long).toContain('2026')
  })

  it('handles null, undefined, and invalid date strings gracefully', () => {
    expect(formatDate(null)).toBe('N/A')
    expect(formatDate(undefined)).toBe('N/A')
    expect(formatDate('not-a-valid-date')).toBe('Invalid date')
  })

  it('generates YYYY-MM-DD input string with toISODateInput', () => {
    const fixedDate = new Date('2026-09-12T15:00:00.000Z')
    expect(toISODateInput(fixedDate)).toBe('2026-09-12')
  })

  it('formats relative time for recent events with pure deterministic baseDate', () => {
    const fixedNow = new Date('2026-09-23T12:00:00.000Z')
    const justNow = new Date('2026-09-23T11:59:45.000Z')
    expect(formatRelativeTime(justNow, fixedNow)).toBe('Just now')

    const tenMinutesAgo = new Date('2026-09-23T11:50:00.000Z')
    expect(formatRelativeTime(tenMinutesAgo, fixedNow)).toBe('10 minutes ago')

    const twoHoursAgo = new Date('2026-09-23T10:00:00.000Z')
    expect(formatRelativeTime(twoHoursAgo, fixedNow)).toBe('2 hours ago')

    const threeDaysAgo = new Date('2026-09-20T12:00:00.000Z')
    expect(formatRelativeTime(threeDaysAgo, fixedNow)).toBe('3 days ago')
  })
})
