/**
 * Global settings store — fetches public system settings from the API
 * and provides reactive access to branding, theme, locale, and config values.
 *
 * Settings are loaded on app boot (before auth) and cached in localStorage.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { apiClient } from '@/lib/http/client'
import { PUBLIC } from '@/lib/api/endpoints'

const CACHE_KEY = 'app_public_settings'
const CACHE_TTL_MS = 60 * 60 * 1000 // 1 hour

interface SettingsMap {
  [key: string]: string | number | boolean | null
}

export const useSettingsStore = defineStore('settings', () => {
  const settings = ref<SettingsMap>({})
  const loaded = ref(false)
  const loading = ref(false)

  // =====================================================
  // Core getter
  // =====================================================

  function get(key: string, fallback: string = ''): string {
    const val = settings.value[key]
    if (val === null || val === undefined) return fallback
    return String(val)
  }

  function getBoolean(key: string, fallback: boolean = false): boolean {
    const val = settings.value[key]
    if (val === null || val === undefined) return fallback
    if (typeof val === 'boolean') return val
    return val === 'true' || val === '1'
  }

  // =====================================================
  // Computed branding helpers
  // =====================================================

  const institutionName = computed(() => get('institution_name', 'Wollo University'))
  const institutionShortName = computed(() => get('institution_short_name', 'WU'))
  const institutionWebsite = computed(() => get('institution_website', 'https://wu.edu.et'))
  const siteName = computed(() => get('site_name', 'Wollo University Lost & Found'))
  const systemShortName = computed(() => get('system_short_name', 'WU-L&F'))
  const tagline = computed(() => get('institution_tagline', 'Together for a Safer Campus'))
  const institutionDescription = computed(() => get('institution_description', 'Official Lost and Found Property Recovery Portal of Wollo University.'))
  const campusesText = computed(() => get('institution_campuses_text', 'Dessie Main Campus & Kombolcha Institute of Technology'))
  const logoUrl = computed(() => get('logo_url', '/images/wu-logo.png'))
  const developerCredit = computed(() => get('developer_credit', ''))
  const csvExportPrefix = computed(() => get('csv_export_prefix', 'export'))

  // Theme colors
  const primaryColor = computed(() => get('theme_primary_color', '#0B5D3B'))
  const primaryLight = computed(() => get('theme_primary_light', '#75bd97'))
  const primaryHover = computed(() => get('theme_primary_hover', '#084C30'))
  const accentColor = computed(() => get('theme_accent_color', '#D4AF37'))
  const primaryBgLight = computed(() => get('theme_primary_bg_light', '#E8F4EE'))
  const primaryBgDark = computed(() => get('theme_primary_bg_dark', '#153C2D'))

  // Locale
  const defaultLocale = computed(() => get('default_locale', 'en'))
  const availableLocales = computed(() => get('available_locales', 'en,am').split(',').map(l => l.trim()))

  // Localized helpers
  const institutionNameAm = computed(() => get('institution_name_am', institutionName.value))
  const taglineAm = computed(() => get('institution_tagline_am', tagline.value))
  const appNameAm = computed(() => get('app_name_am', siteName.value))

  // Contact
  const contactEmail = computed(() => get('contact_email', ''))
  const contactPhone = computed(() => get('contact_phone', ''))

  // =====================================================
  // Fetch & cache
  // =====================================================

  async function fetchSettings(): Promise<void> {
    // Try cache first
    if (!loaded.value) {
      try {
        const cached = localStorage.getItem(CACHE_KEY)
        if (cached) {
          const parsed = JSON.parse(cached)
          if (parsed.ts && Date.now() - parsed.ts < CACHE_TTL_MS) {
            settings.value = parsed.data
            loaded.value = true
            applyThemeColors()
          }
        }
      } catch {
        // ignore parse errors
      }
    }

    loading.value = true
    try {
      const { data } = await apiClient.get<{ data: SettingsMap }>(PUBLIC.SETTINGS)
      settings.value = data.data
      loaded.value = true

      // Cache to localStorage
      localStorage.setItem(CACHE_KEY, JSON.stringify({
        ts: Date.now(),
        data: data.data,
      }))

      applyThemeColors()
    } catch {
      // If fetch fails but we have cache, that's fine
      if (!loaded.value) {
        loaded.value = true // mark loaded to avoid infinite retries
      }
    } finally {
      loading.value = false
    }
  }

  /**
   * Apply theme colors as CSS custom properties on :root
   * so they cascade throughout the entire app.
   */
  function applyThemeColors(): void {
    const root = document.documentElement
    root.style.setProperty('--app-primary', primaryColor.value)
    root.style.setProperty('--app-primary-light', primaryLight.value)
    root.style.setProperty('--app-primary-hover', primaryHover.value)
    root.style.setProperty('--app-accent', accentColor.value)
    root.style.setProperty('--app-primary-bg-light', primaryBgLight.value)
    root.style.setProperty('--app-primary-bg-dark', primaryBgDark.value)
  }

  function clearCache(): void {
    localStorage.removeItem(CACHE_KEY)
    loaded.value = false
  }

  function exportFilename(baseName: string, ext: string = 'csv'): string {
    const prefix = csvExportPrefix.value || 'export'
    const dateStr = new Date().toISOString().slice(0, 10)
    return `${prefix}_${baseName}_${dateStr}.${ext}`
  }

  return {
    settings,
    loaded,
    loading,
    get,
    getBoolean,
    fetchSettings,
    clearCache,
    applyThemeColors,
    exportFilename,

    // Branding
    institutionName,
    institutionShortName,
    institutionWebsite,
    siteName,
    systemShortName,
    tagline,
    institutionDescription,
    campusesText,
    logoUrl,
    developerCredit,
    csvExportPrefix,

    // Theme
    primaryColor,
    primaryLight,
    primaryHover,
    accentColor,
    primaryBgLight,
    primaryBgDark,

    // Locale
    defaultLocale,
    availableLocales,
    institutionNameAm,
    taglineAm,
    appNameAm,

    // Contact
    contactEmail,
    contactPhone,
  }
})

/**
 * Standalone helper to generate dynamic export filenames with the configured prefix.
 */
export function getExportFilename(baseName: string, ext: string = 'csv'): string {
  try {
    const cached = localStorage.getItem('app_public_settings')
    if (cached) {
      const parsed = JSON.parse(cached)
      if (parsed?.data?.csv_export_prefix) {
        return `${parsed.data.csv_export_prefix}_${baseName}_${new Date().toISOString().slice(0, 10)}.${ext}`
      }
    }
  } catch {}
  return `export_${baseName}_${new Date().toISOString().slice(0, 10)}.${ext}`
}
