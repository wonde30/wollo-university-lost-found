/**
 * Returns feature types based on Laravel backend contract.
 * Staff and admin only.
 */

import type { Item } from '@/features/items/types/item.types'
import type { Claim } from '@/features/claims/types/claim.types'
import type { User } from '@/features/auth/types/auth.types'
import type { StorageLocation } from '@/types/common.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export interface ReturnDocument {
  id: number
  return_record_id: number
  document_type: string
  file_path: string
  file_url: string
  file_size: number
  mime_type: string
  created_at: string
}

export interface ReturnRecord {
  id: number
  claim_id: number | null
  item_id: number
  returned_to: number
  handed_over_by: number
  storage_location_id: number | null
  return_date: string
  return_time: string | null
  condition_on_return: string | null
  notes: string | null
  recipient_confirmed: boolean
  confirmed_at: string | null
  item?: Item
  claim?: Claim
  recipient?: User
  staff?: User
  storage_location?: StorageLocation
  documents?: ReturnDocument[]
  created_at: string
}

// ==========================================
// Request Payloads
// ==========================================

export interface StoreReturnData {
  claim_id?: number
  item_id: number
  returned_to: number
  storage_location_id?: number
  return_date: string
  return_time?: string
  condition_on_return?: string
  notes?: string
  documents?: File[]
}

export interface ReturnListParams extends PaginationParams {
  item_id?: number
  claim_id?: number
  recipient_confirmed?: boolean
}

// ==========================================
// Response Types
// ==========================================

export type ReturnListResponse = PaginatedResponse<ReturnRecord>

export interface ReturnDetailResponse {
  data: ReturnRecord
}

export interface ReturnCreateResponse {
  message: string
  data: ReturnRecord
}

/** Alias for ReturnRecord — kept for composable/component compatibility */
export type ItemReturn = ReturnRecord
