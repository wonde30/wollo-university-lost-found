/**
 * Custody feature types based on Laravel backend contract.
 * Staff and admin only.
 */

import type { Item } from '@/features/items/types/item.types'
import type { User } from '@/features/auth/types/auth.types'
import type { StorageLocation } from '@/types/common.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export type CustodyEventType = 'deposited' | 'transferred' | 'inspected' | 'withdrawn' | 'returned'

export interface CustodyEvent {
  id: number
  item_id: number
  event_type: CustodyEventType
  condition: string | null
  notes: string | null
  reference_photo: string | null
  item?: Item
  actor?: User
  performed_by?: User
  storage_location?: StorageLocation
  created_at: string
}

// ==========================================
// Request Payloads
// ==========================================

export interface StoreCustodyEventData {
  item_id: number
  event_type: CustodyEventType
  storage_location_id?: number
  condition?: string
  notes?: string
  reference_photo?: File
}

export type { StorageLocation } from '@/types/common.types'
export type StoreCustodyData = StoreCustodyEventData

export interface MoveItemCustodyData {
  storage_location_id: number
  condition?: string
  notes?: string
}

export interface CustodyListParams extends PaginationParams {
  item_id?: number
  event_type?: CustodyEventType
  storage_location_id?: number
}

// ==========================================
// Response Types
// ==========================================

export type CustodyListResponse = PaginatedResponse<CustodyEvent>

export interface CustodyDetailResponse {
  data: CustodyEvent
}

export interface CustodyCreateResponse {
  message: string
  data: CustodyEvent
}

export interface StorageLocationResponse {
  data: StorageLocation[]
}

export interface StorageLocationDetailResponse {
  data: StorageLocation
}
