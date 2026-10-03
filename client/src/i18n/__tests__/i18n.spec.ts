import { describe, it, expect, beforeEach } from 'vitest'
import { t, currentLocale, setLocale, getLocalizedName } from '../index'

describe('i18n System', () => {
  beforeEach(() => {
    setLocale('en')
  })

  it('translates navigation keys in English', () => {
    expect(t('nav.home')).toBe('Home')
    expect(t('nav.analytics')).toBe('Analytics')
  })

  it('translates navigation keys in Amharic when switched', () => {
    setLocale('am')
    expect(currentLocale.value).toBe('am')
    expect(t('nav.home')).toBe('ዋና ገጽ')
    expect(t('nav.analytics')).toBe('ትንታኔዎች')
  })

  it('handles interpolation parameters correctly', () => {
    const res = t('common.welcomeBack', { name: 'Abebe' })
    expect(res).toBe('Welcome back, Abebe!')

    setLocale('am')
    const resAm = t('common.welcomeBack', { name: 'አበበ' })
    expect(resAm).toBe('እንኳን ደህና መጡ፣ አበበ!')
  })

  it('returns key path as fallback when key does not exist', () => {
    expect(t('nonexistent.deep.nested.key')).toBe('nonexistent.deep.nested.key')
  })

  it('resolves localized entity names with getLocalizedName', () => {
    const category = {
      name: 'Electronics',
      display_name: 'Electronics & Gadgets',
      display_name_am: 'ኤሌክትሮኒክስ እና መሣሪያዎች',
    }

    setLocale('en')
    expect(getLocalizedName(category)).toBe('Electronics & Gadgets')

    setLocale('am')
    expect(getLocalizedName(category)).toBe('ኤሌክትሮኒክስ እና መሣሪያዎች')
  })
})
