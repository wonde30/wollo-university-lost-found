/**
 * Items feature types based on Laravel backend contract.
 */

import type { Campus, Category, Location } from '@/types/common.types'
import type { User } from '@/features/auth/types/auth.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export type ItemType = 'lost' | 'found'
export type ItemStatus = 'lost' | 'found_unclaimed' | 'found_claimed' | 'returned' | 'expired' | 'disposed'
export type ItemHeldAt = 'security_office' | 'registrar' | 'ict_office' | 'library' | 'other'

export interface ItemPhoto {
  id: number
  item_id: number
  photo_url: string
  original_name: string
  is_primary: boolean
  size_bytes: number
  mime_type: string
}

export interface ItemStatusHistory {
  id: number
  item_id: number
  previous_status: string | null
  new_status: string
  reason: string | null
  notes: string | null
  changed_by?: User
  created_at: string
}

export interface Item {
  id: number
  reference_code: string
  reporter_id: number
  user_id: number
  campus_id: number
  category_id: number
  location_id: number | null
  location_detail: string | null
  type: ItemType
  status: ItemStatus
  held_at: ItemHeldAt | null
  title: string
  description: string
  brand: string | null
  color: string | null
  serial_number: string | null
  incident_date: string
  incident_time: string | null
  estimated_value: number | null
  is_high_value: boolean
  last_activity_at: string
  expires_at: string | null
  views_count?: number
  primary_photo?: ItemPhoto | null
  photos?: ItemPhoto[]
  tags?: string[]
  category?: Category
  location?: Location
  campus?: Campus
  reporter?: User
  user?: User
  created_at: string
  updated_at: string
}

export interface ItemDetail extends Item {
  status_histories?: ItemStatusHistory[]
  custody_events?: CustodyEvent[]
  claims_count?: number
}

export interface CustodyEvent {
  id: number
  item_id: number
  actor_id: number
  storage_location_id: number | null
  event_type: string
  condition: string | null
  notes: string | null
  created_at: string
}

// ==========================================
// Request Payloads
// ==========================================

export interface StoreLostItemData {
  title: string
  description: string
  category_id: number
  location_id?: number
  location_detail?: string
  campus_id?: number
  brand?: string
  color?: string
  serial_number?: string
  incident_date: string
  incident_time?: string
  estimated_value?: number
  is_high_value?: boolean
  photos?: File[]
}

export interface StoreFoundItemData {
  title: string
  description: string
  category_id: number
  location_id?: number
  location_detail?: string
  campus_id?: number
  brand?: string
  color?: string
  serial_number?: string
  incident_date: string
  incident_time?: string
  held_at?: ItemHeldAt
  storage_location_id?: number
  photos?: File[]
}

export interface UpdateItemData {
  title?: string
  description?: string
  category_id?: number
  location_id?: number
  location_detail?: string
  brand?: string
  color?: string
  serial_number?: string
  incident_date?: string
  incident_time?: string
  estimated_value?: number
}

export interface UpdateItemStatusData {
  status: ItemStatus
  reason?: string
  notes?: string
}

export interface ItemListParams extends PaginationParams {
  type?: ItemType
  status?: ItemStatus
}

// ==========================================
// Response Types
// ==========================================

export type ItemListResponse = PaginatedResponse<Item>

export interface ItemDetailResponse {
  data: ItemDetail
}

export interface ItemCreateResponse {
  message: string
  data: ItemDetail
}

export interface ItemUpdateResponse {
  message: string
  data: ItemDetail
}

export interface TrackItemResult {
  reference_code: string
  title: string
  category: string | null
  status: ItemStatus | string
  incident_date: string | null
}

