/**
 * Notification message, title, and action URL resolver.
 * Bridges backend structured notification payloads with the bilingual i18n system.
 */

import type { Notification } from '../types/notification.types'
import { t } from '@/i18n'

/**
 * Normalizes legacy or non-canonical notification types to standard domain event types.
 */
function normalizeNotificationType(rawType: string): string {
  switch (rawType) {
    case 'system_report_ready':
      return 'report_generated'
    case 'custody_expiry_warning':
      return 'item_expiring'
    case 'new_claim_submitted':
    case 'claim_under_review':
      return 'claim_submitted'
    case 'item_matched':
      return 'item_match'
    case 'return_confirmation_request':
      return 'item_returned'
    default:
      return rawType
  }
}

/**
 * Resolves the localized display title for a notification.
 */
export function resolveNotificationTitle(notification: Notification): string {
  const type = normalizeNotificationType(notification.type || '')
  const typeKey = `notifications.types.${type}`
  const translated = t(typeKey)

  if (translated !== typeKey) {
    return translated
  }

  // Fallback: title-case the normalized type string
  if (type) {
    return type
      .split('_')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1))
      .join(' ')
  }

  return t('notifications.title')
}

/**
 * Resolves the localized display message for a notification with parameter interpolation.
 */
export function resolveNotificationMessage(notification: Notification): string {
  const data = notification.data || {}
  const type = normalizeNotificationType(notification.type || '')

  let messageKey: string | null = null
  let params: Record<string, any> = { ...data }

  switch (type) {
    case 'claim_submitted':
      if (data.sub_type === 'staff') {
        messageKey = 'claim_submitted_staff'
      } else if (data.sub_type === 'reporter') {
        messageKey = 'claim_submitted_reporter'
      } else {
        messageKey = 'claim_submitted_claimant'
      }
      break

    case 'claim_approved':
      messageKey = 'claim_approved'
      break

    case 'claim_rejected':
      if (data.review_note) {
        messageKey = 'claim_rejected_with_reason'
        params.reason = data.review_note
      } else {
        messageKey = 'claim_rejected'
      }
      break

    case 'item_match':
      messageKey = 'item_match'
      params.score = data.score ?? 90
      break

    case 'item_returned':
      messageKey = 'item_returned'
      break

    case 'return_confirmed':
      messageKey = 'return_confirmed'
      break

    case 'item_reported':
      messageKey = data.type === 'found' ? 'item_reported_found' : 'item_reported_lost'
      break

    case 'item_status_changed':
      messageKey = 'item_status_changed'
      break

    case 'item_expiring':
      messageKey = 'item_expiring'
      params.days_remaining = data.days_remaining ?? 7
      break

    case 'item_expired':
      messageKey = 'item_expired'
      break

    case 'report_generated':
      messageKey = 'report_generated'
      params.report_type = data.report_type || 'Analytics'
      break

    case 'report_failed':
      messageKey = 'report_failed'
      params.report_type = data.report_type || 'Analytics'
      break

    case 'custody_transferred':
      messageKey = 'custody_transferred'
      break

    default:
      messageKey = null
  }

  if (messageKey) {
    const fullPath = `notifications.messages.${messageKey}`
    const translated = t(fullPath, params)
    if (translated !== fullPath) {
      return translated
    }
  }

  // Fallback to title
  return resolveNotificationTitle(notification)
}

/**
 * Resolves the client route for navigating when a notification is clicked.
 */
export function resolveNotificationActionUrl(notification: Notification): string | null {
  if (notification.action_url) {
    return notification.action_url
  }

  const data = notification.data || {}

  // Explicit URL or link in data payload
  if (data.action_url && typeof data.action_url === 'string') {
    return data.action_url
  }
  if (data.link && typeof data.link === 'string') {
    return data.link
  }

  const type = normalizeNotificationType(notification.type || '')

  if (type === 'report_generated' || type === 'report_failed') {
    return '/admin/reports'
  }

  if (data.confirmation_token || data.token) {
    const token = data.confirmation_token || data.token
    return `/confirm-return/${token}`
  }

  if (type === 'claim_submitted' || type === 'claim_approved' || type === 'claim_rejected') {
    if (data.sub_type === 'staff') {
      return '/staff/review-claims'
    }
    return '/student/my-claims'
  }

  if (type === 'return_confirmed' || type === 'custody_transferred') {
    return '/staff/manage-custody'
  }

  if (data.lost_item_id) {
    return `/items/${data.lost_item_id}`
  }

  if (data.found_item_id) {
    return `/items/${data.found_item_id}`
  }

  if (data.item_id) {
    return `/items/${data.item_id}`
  }

  return null
}
