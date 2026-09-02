import { ref, computed } from 'vue'
import { en } from './locales/en'
import { am } from './locales/am'

export type Locale = 'en' | 'am'

export const currentLocale = ref<Locale>(
  (typeof localStorage !== 'undefined' ? (localStorage.getItem('wu_locale') as Locale) : null) || 'en'
)

const dictionaries = {
  en,
  am,
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

export function setLocale(newLocale: Locale): void {
  currentLocale.value = newLocale
  if (typeof window !== 'undefined') {
    localStorage.setItem('wu_locale', newLocale)
    document.documentElement.setAttribute('lang', newLocale)
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

  return {
    locale,
    isAmharic,
    t,
    setLocale,
    toggleLocale,
  }
}
