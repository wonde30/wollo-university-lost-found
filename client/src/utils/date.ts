import { t, currentLocale } from '@/i18n'

/**
 * Date formatting and manipulation utilities (Pure functions)
 */

export function formatDate(
  dateString: string | null | undefined,
  format: 'short' | 'medium' | 'long' | 'relative' = 'medium',
  baseDate: Date = new Date()
): string {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Invalid date'

  if (format === 'relative') {
    return formatRelativeTime(date, baseDate)
  }

  const locale = currentLocale.value === 'am' ? 'am-ET' : 'en-US'
  const optionsMap: Record<'short' | 'medium' | 'long', Intl.DateTimeFormatOptions> = {
    short: { month: 'numeric', day: 'numeric', year: '2-digit' },
    medium: { month: 'short', day: 'numeric', year: 'numeric' },
    long: { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' },
  }

  try {
    return new Intl.DateTimeFormat(locale, optionsMap[format]).format(date)
  } catch {
    return new Intl.DateTimeFormat('en-US', optionsMap[format]).format(date)
  }
}

export function formatDateTime(dateString: string | null | undefined): string {
  return formatDate(dateString, 'long')
}

export function formatRelativeTime(date: Date | string, baseDate: Date = new Date()): string {
  const targetDate = typeof date === 'string' ? new Date(date) : date
  if (isNaN(targetDate.getTime())) return 'N/A'

  const diffInSeconds = Math.floor((baseDate.getTime() - targetDate.getTime()) / 1000)

  if (diffInSeconds < 60) return t('time.justNow')
  if (diffInSeconds < 3600) {
    const minutes = Math.max(1, Math.floor(diffInSeconds / 60))
    return t(minutes === 1 ? 'time.minutesAgo' : 'time.minutesAgo_plural', { count: minutes })
  }
  if (diffInSeconds < 86400) {
    const hours = Math.floor(diffInSeconds / 3600)
    return t(hours === 1 ? 'time.hoursAgo' : 'time.hoursAgo_plural', { count: hours })
  }
  if (diffInSeconds < 604800) {
    const days = Math.floor(diffInSeconds / 86400)
    return t(days === 1 ? 'time.daysAgo' : 'time.daysAgo_plural', { count: days })
  }
  return formatDate(targetDate.toISOString(), 'medium', baseDate)
}

export function toISODateInput(date: Date = new Date()): string {
  return date.toISOString().split('T')[0]
}

