import { ref, computed } from 'vue'
import { en } from './locales/en'
import { am } from './locales/am'

export type Locale = 'en' | 'am'

export interface LocaleMeta {
  code: Locale
  name: string
  nativeName: string
  dir: 'ltr' | 'rtl'
  flag?: string
}

export const SUPPORTED_LOCALES: Record<Locale, LocaleMeta> = {
  en: { code: 'en', name: 'English', nativeName: 'English', dir: 'ltr' },
  am: { code: 'am', name: 'Amharic', nativeName: 'አማርኛ', dir: 'ltr' },
}

const initialLocale: Locale =
  (typeof localStorage !== 'undefined' && typeof localStorage.getItem === 'function'
    ? (localStorage.getItem('wu_locale') as Locale)
    : null) || 'en'

export const currentLocale = ref<Locale>(initialLocale)

// Synchronize document attributes on load in browser environment
if (typeof document !== 'undefined' && document.documentElement) {
  document.documentElement.setAttribute('lang', initialLocale)
  document.documentElement.setAttribute('dir', SUPPORTED_LOCALES[initialLocale]?.dir || 'ltr')
}

const dictionaries: Record<string, any> = {
  en,
  am,
}

export function registerLocale(code: string, meta: LocaleMeta, dict: any): void {
  (SUPPORTED_LOCALES as Record<string, LocaleMeta>)[code] = meta
  dictionaries[code] = dict
}

function getNestedValue(obj: any, path: string): string | undefined {
  const keys = path.split('.')
  let current = obj
  for (const k of keys) {
    if (current && typeof current === 'object' && k in current) {
      current = current[k]
    } else {
      return undefined
    }
  }
  return typeof current === 'string' ? current : undefined
}

export function t(path: string, params?: Record<string, any>): string {
  const dict = dictionaries[currentLocale.value] || dictionaries.en
  let template = getNestedValue(dict, path)

  // Fallback to English if translation is missing in Amharic
  if (template === undefined && currentLocale.value !== 'en') {
    template = getNestedValue(dictionaries.en, path)
  }

  // If still missing, return the key path itself
  if (template === undefined) {
    return path
  }

  // Parameter replacement
  if (params) {
    return template.replace(/\{(\w+)\}/g, (match, key) => {
      return key in params ? String(params[key]) : match
    })
  }

  return template
}

/**
 * Universal entity name resolver.
 * Eliminates scattered `if (currentLocale === 'am')` checks across the codebase.
 * Prioritizes localized fields (name_am, display_name_am, title_am) when currentLocale is 'am',
 * with seamless fallback to English defaults, or vice versa.
 */
export function getLocalizedName(
  entity: Record<string, any> | null | undefined,
  fallback = ''
): string {
  if (!entity) return fallback

  const loc = currentLocale.value
  if (loc === 'am') {
    return (
      entity.display_name_am ||
      entity.name_am ||
      entity.title_am ||
      entity.display_name ||
      entity.name ||
      entity.title ||
      fallback
    )
  }

  return (
    entity.display_name ||
    entity.name ||
    entity.title ||
    entity.display_name_am ||
    entity.name_am ||
    fallback
  )
}

export function setLocale(newLocale: Locale): void {
  currentLocale.value = newLocale
  if (typeof window !== 'undefined') {
    if (typeof localStorage !== 'undefined' && typeof localStorage.setItem === 'function') {
      localStorage.setItem('wu_locale', newLocale)
    }
    const meta = SUPPORTED_LOCALES[newLocale]
    if (typeof document !== 'undefined' && document.documentElement) {
      document.documentElement.setAttribute('lang', newLocale)
      document.documentElement.setAttribute('dir', meta?.dir || 'ltr')
    }
  }
}

export function toggleLocale(): void {
  setLocale(currentLocale.value === 'en' ? 'am' : 'en')
}

export function useI18n() {
  const locale = computed({
    get: () => currentLocale.value,
    set: (l: Locale) => setLocale(l),
  })

  const isAmharic = computed(() => currentLocale.value === 'am')
  const currentMeta = computed(() => SUPPORTED_LOCALES[currentLocale.value] || SUPPORTED_LOCALES.en)

  return {
    locale,
    isAmharic,
    currentMeta,
    supportedLocales: SUPPORTED_LOCALES,
    t,
    getLocalizedName,
    setLocale,
    toggleLocale,
  }
}
