/**
 * Date formatting and manipulation utilities
 */

export function formatDate(dateString: string | null | undefined, format: 'short' | 'medium' | 'long' | 'relative' = 'medium'): string {
  if (!dateString) return 'N/A'

  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Invalid date'

  if (format === 'relative') {
    return formatRelativeTime(date)
  }

  const optionsMap: Record<'short' | 'medium' | 'long', Intl.DateTimeFormatOptions> = {
    short: { month: 'numeric', day: 'numeric', year: '2-digit' },
    medium: { month: 'short', day: 'numeric', year: 'numeric' },
    long: { month: 'long', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' },
  }

  return new Intl.DateTimeFormat('en-US', optionsMap[format]).format(date)
}

export function formatDateTime(dateString: string | null | undefined): string {
  return formatDate(dateString, 'long')
}

export function formatRelativeTime(date: Date | string): string {
  const targetDate = typeof date === 'string' ? new Date(date) : date
  if (isNaN(targetDate.getTime())) return 'N/A'

  const now = new Date()
  const diffInSeconds = Math.floor((now.getTime() - targetDate.getTime()) / 1000)

  if (diffInSeconds < 60) return 'Just now'
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`
  return formatDate(targetDate.toISOString(), 'medium')
}

export function toISODateInput(date: Date = new Date()): string {
  return date.toISOString().split('T')[0]
}
