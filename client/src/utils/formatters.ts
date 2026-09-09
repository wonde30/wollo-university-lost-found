import { t } from '@/i18n'

export const claimStatusLabels: Record<string, string> = {
  pending: 'Pending Review',
  under_review: 'Under Review',
  approved: 'Approved',
  rejected: 'Rejected',
  reversed: 'Reversed',
  cancelled: 'Cancelled',
  withdrawn: 'Withdrawn',
}

export function formatStatus(status: string | null | undefined): string {
  if (!status) return t('common.none')
  if (claimStatusLabels[status]) return claimStatusLabels[status]
  const itemKey = `items.statuses.${status}`
  const trans = t(itemKey)
  if (trans !== itemKey) return trans
  const claimKey = `claims.status.${status}`
  const claimTrans = t(claimKey)
  if (claimTrans !== claimKey) return claimTrans
  return status
    .split('_')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
    .join(' ')
}

export function formatCurrency(amount: number | null | undefined, currency: string = 'ETB'): string {
  if (amount === null || amount === undefined || isNaN(amount)) return 'N/A'
  return `${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`
}

export function formatFileSize(bytes: number | null | undefined): string {
  if (!bytes || bytes === 0) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(1))} ${sizes[i]}`
}

export function formatReferenceCode(code: string | null | undefined): string {
  if (!code) return 'N/A'
  return code.toUpperCase()
}

export function truncateText(text: string | null | undefined, maxLength: number = 100): string {
  if (!text) return ''
  if (text.length <= maxLength) return text
  return text.slice(0, maxLength).trimEnd() + '...'
}

export function capitalize(str: string | null | undefined): string {
  if (!str) return ''
  return str.charAt(0).toUpperCase() + str.slice(1)
}
