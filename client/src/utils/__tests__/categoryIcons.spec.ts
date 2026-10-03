import { describe, it, expect } from 'vitest'
import { getCategoryIcon, FallbackCategoryIcon } from '../categoryIcons'
import { Smartphone, Laptop, Backpack, KeyRound, Package } from 'lucide-vue-next'

describe('Pure Category Icon Resolver', () => {
  it('maps known slugs to correct Lucide icon components', () => {
    expect(getCategoryIcon('phone')).toBe(Smartphone)
    expect(getCategoryIcon('laptop')).toBe(Laptop)
    expect(getCategoryIcon('bag')).toBe(Backpack)
    expect(getCategoryIcon('key')).toBe(KeyRound)
  })

  it('normalizes slugs with whitespace and hyphens', () => {
    expect(getCategoryIcon(' smart-phone ')).toBe(Smartphone)
    expect(getCategoryIcon('FLASH-DRIVE')).toBe(getCategoryIcon('usb'))
  })

  it('returns Package fallback for unknown or null/empty slugs', () => {
    expect(getCategoryIcon(null)).toBe(Package)
    expect(getCategoryIcon(undefined)).toBe(Package)
    expect(getCategoryIcon('')).toBe(Package)
    expect(getCategoryIcon('unknown_alien_category_xyz')).toBe(FallbackCategoryIcon)
  })
})
