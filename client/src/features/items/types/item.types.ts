/**
 * Items feature types based on Laravel backend contract.
 */

import type { Campus, Category, Location } from '@/types/common.types'
import type { User } from '@/features/auth/types/auth.types'
import type { CustodyEvent } from '@/features/custody/types/custody.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export type ItemType = 'lost' | 'found'

/**
 * Item status values matching backend ItemStatusEnum exactly.
 * Source: server/app/Models/Item.php + ItemStatusController
 */
export type ItemStatus =
  | 'lost'                  // Lost item reported
  | 'found_unclaimed'       // Found item, no claim yet
  | 'found_claimed'         // Found item, claim approved
  | 'in_custody'            // Item in physical storage custody
  | 'matched'               // AI match suggestion confirmed
  | 'claimed'               // Claim submitted (pending review)
  | 'returned'              // Item returned to owner
  | 'pending_verification'  // Awaiting staff verification
  | 'pending_surrender'     // Owner agreed to surrender
  | 'withdrawn'             // Reporter withdrew item report
  | 'cancelled'             // Cancelled by system/admin
  | 'expired'               // Item report expired
  | 'disposed'              // Item disposed of
  | string                  // Forward-compatible catch-all

export type ItemHeldAt = 'security_office' | 'with_finder' | 'unknown' | string


export interface ItemPhoto {
  id: number
  item_id: number
  path: string
  url: string
  photo_url?: string
  original_name: string
  mime_type: string
  size_bytes: number
  is_primary: boolean
  created_at?: string
}

/**
 * ItemStatusHistory matching ItemStatusHistoryResource.
 * Note: changed_by is a full UserResource object when loaded (not integer).
 */
export interface ItemStatusHistory {
  id: number
  item_id: number
  /** User object when loaded via whenLoaded('changedByUser'), otherwise null */
  changed_by?: User | null
  previous_status?: string | null
  new_status?: string
  reason?: string | null
  notes?: string | null
  /** Forward-compatible aliases */
  from_status?: string | null
  to_status?: string
  changed_by_role?: string | null
  changed_by_user?: User
  user?: User
  created_at: string
}

export interface Item {
  id: number
  reference_code: string
  reporter_id: number
  user_id?: number
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

// ==========================================
// Request Payloads
// ==========================================

export interface StoreLostItemData {
  title: string
  description: string
  category_id: number
  campus_id?: number
  location_id?: number | null
  location_detail?: string | null
  brand?: string | null
  color?: string | null
  serial_number?: string | null
  incident_date: string
  incident_time?: string | null
  estimated_value?: number | null
  is_high_value?: boolean
  tags?: string[]
  photos?: File[]
}

export interface StoreFoundItemData {
  title: string
  description: string
  category_id: number
  campus_id?: number
  location_id?: number | null
  location_detail?: string | null
  brand?: string | null
  color?: string | null
  serial_number?: string | null
  incident_date: string
  incident_time?: string | null
  held_at?: ItemHeldAt | null
  storage_location_id?: number | null
  tags?: string[]
  photos?: File[]
}

export interface UpdateItemData {
  title?: string
  description?: string
  category_id?: number
  campus_id?: number
  location_id?: number | null
  location_detail?: string | null
  brand?: string | null
  color?: string | null
  serial_number?: string | null
  incident_date?: string
  incident_time?: string | null
  estimated_value?: number | null
  is_high_value?: boolean
  held_at?: ItemHeldAt | null
  tags?: string[]
}

export interface UpdateItemStatusData {
  status: ItemStatus
  reason?: string
  notes?: string
  remarks?: string
}

export interface ItemListParams extends PaginationParams {
  type?: ItemType
  status?: ItemStatus
  category_id?: number
  campus_id?: number
  location_id?: number
  reporter_id?: number
  mine?: boolean
  search?: string
  tag?: string
  from_date?: string
  to_date?: string
  sort?: 'newest' | 'oldest' | 'category_az' | string
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
  held_at?: string | null
  campus?: string | null
  location?: string | null
}
