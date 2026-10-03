import { describe, it, expect } from 'vitest'
import {
  formatStatus,
  formatCurrency,
  formatFileSize,
  formatReferenceCode,
  truncateText,
  capitalize,
} from '../formatters'

describe('Pure Formatting Functions', () => {
  describe('formatStatus', () => {
    it('returns None fallback for null/undefined/empty inputs', () => {
      expect(formatStatus(null)).toBe('None')
      expect(formatStatus(undefined)).toBe('None')
      expect(formatStatus('')).toBe('None')
    })

    it('formats known status keys correctly', () => {
      expect(formatStatus('under_review')).toBe('Under Review')
      expect(formatStatus('approved')).toBe('Approved')
      expect(formatStatus('found_unclaimed')).toBe('Found')
      expect(formatStatus('custom_state_name')).toBe('Custom State Name')
    })

    it('is purely deterministic (same input -> same output)', () => {
      const res1 = formatStatus('claimed')
      const res2 = formatStatus('claimed')
      expect(res1).toBe(res2)
    })
  })

  describe('formatCurrency', () => {
    it('formats numbers to 2 decimal places with currency symbol', () => {
      expect(formatCurrency(1250)).toBe('1,250.00 ETB')
      expect(formatCurrency(0)).toBe('0.00 ETB')
      expect(formatCurrency(99.5, 'USD')).toBe('99.50 USD')
    })

    it('handles null, undefined, and NaN defensively', () => {
      expect(formatCurrency(null)).toBe('N/A')
      expect(formatCurrency(undefined)).toBe('N/A')
      expect(formatCurrency(NaN)).toBe('N/A')
    })
  })

  describe('formatFileSize', () => {
    it('formats byte numbers into human-readable string', () => {
      expect(formatFileSize(0)).toBe('0 B')
      expect(formatFileSize(null)).toBe('0 B')
      expect(formatFileSize(undefined)).toBe('0 B')
      expect(formatFileSize(-100)).toBe('0 B')
      expect(formatFileSize(1024)).toBe('1 KB')
      expect(formatFileSize(1048576)).toBe('1 MB')
      expect(formatFileSize(5242880)).toBe('5 MB')
    })
  })

  describe('formatReferenceCode', () => {
    it('uppercases reference codes and handles falsy values', () => {
      expect(formatReferenceCode('wu-2026-abc')).toBe('WU-2026-ABC')
      expect(formatReferenceCode('')).toBe('N/A')
      expect(formatReferenceCode(null)).toBe('N/A')
      expect(formatReferenceCode(undefined)).toBe('N/A')
    })
  })

  describe('truncateText', () => {
    it('truncates strings longer than maxLength with ellipsis without mutating input', () => {
      const original = 'This is a long sentence that exceeds the limit.'
      expect(truncateText(original, 10)).toBe('This is a...')
      expect(original).toBe('This is a long sentence that exceeds the limit.')
      expect(truncateText('Short', 10)).toBe('Short')
      expect(truncateText(null)).toBe('')
      expect(truncateText(undefined)).toBe('')
    })
  })

  describe('capitalize', () => {
    it('capitalizes the first character', () => {
      expect(capitalize('electronics')).toBe('Electronics')
      expect(capitalize('')).toBe('')
      expect(capitalize(null)).toBe('')
      expect(capitalize(undefined)).toBe('')
    })
  })
})
