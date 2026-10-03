import { describe, it, expect } from 'vitest'
import { resolveStorageUrl } from '../url'

describe('Pure Storage URL Resolver', () => {
  it('returns empty string for null/empty path', () => {
    expect(resolveStorageUrl(null)).toBe('')
    expect(resolveStorageUrl(undefined)).toBe('')
    expect(resolveStorageUrl('')).toBe('')
    expect(resolveStorageUrl('   ')).toBe('')
  })

  it('preserves full absolute URLs and data URIs', () => {
    expect(resolveStorageUrl('https://example.com/photo.jpg')).toBe('https://example.com/photo.jpg')
    expect(resolveStorageUrl('http://example.com/photo.jpg')).toBe('http://example.com/photo.jpg')
    expect(resolveStorageUrl('data:image/png;base64,abc')).toBe('data:image/png;base64,abc')
    expect(resolveStorageUrl('blob:http://localhost/xyz')).toBe('blob:http://localhost/xyz')
  })

  it('resolves relative storage paths correctly', () => {
    const res = resolveStorageUrl('item-photos/test.jpg')
    expect(res).toContain('/storage/item-photos/test.jpg')
  })
})
