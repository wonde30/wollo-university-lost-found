/**
 * Notifications feature types based on Laravel backend contract.
 */

import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export interface Notification {
  id: string | number
  user_id: number
  type: string
  data: Record<string, any>
  is_read?: boolean
  read_at: string | null
  created_at: string
}

export interface NotificationPreference {
  id: number
  user_id: number
  email_on_report_submitted: boolean
  email_on_match_found: boolean
  email_on_claim_received: boolean
  email_on_claim_decided: boolean
  email_on_item_returned: boolean
  email_on_expiry_warning: boolean
  email_on_item_expired: boolean
  email_on_system_announcements: boolean
  updated_at?: string
}

// ==========================================
// Request Payloads
// ==========================================

export interface NotificationListParams extends PaginationParams {
  read?: boolean
  type?: string
}

export interface UpdateNotificationPreferencesData {
  email_on_report_submitted?: boolean
  email_on_match_found?: boolean
  email_on_claim_received?: boolean
  email_on_claim_decided?: boolean
  email_on_item_returned?: boolean
  email_on_expiry_warning?: boolean
  email_on_item_expired?: boolean
  email_on_system_announcements?: boolean
}

// ==========================================
// Response Types
// ==========================================

export type NotificationListResponse = PaginatedResponse<Notification> & {
  unread_count?: number
}

export interface NotificationPreferencesResponse {
  data: NotificationPreference
}

export interface MessageResponse {
  message: string
}
