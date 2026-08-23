/**
 * Common types used across the application.
 * These are shared types not specific to any single feature.
 */

// Re-export pagination from lib
export type { PaginationMeta, PaginationParams } from '@/lib/api/pagination'
export type { ApiResponse, PaginatedResponse } from '@/lib/api/response'

/**
 * Common domain types used across features
 */

export interface Campus {
  id: number
  name: string
  code: string
  address: string | null
  description: string | null
  is_active: boolean
  departments?: Department[]
  locations?: Location[]
  created_at: string
  updated_at: string
}

export interface Category {
  id: number
  name: string
  name_am: string | null
  icon_slug: string | null
  icon?: string | null  // Alias for icon_slug for backwards compatibility
  sort_order: number
  is_active: boolean
  items_count?: number
}

export interface Location {
  id: number
  campus_id: number
  name: string
  code: string
  building: string | null
  floor: string | null
  room_number: string | null
  coordinates: string | null
  is_active: boolean
  campus?: Campus
  created_at: string
  updated_at: string
}

export interface Department {
  id: number
  campus_id: number
  name: string
  code: string
  description: string | null
  is_active: boolean
  campus?: Campus
  created_at: string
  updated_at: string
}

export interface StorageLocation {
  id: number
  campus_id: number
  name: string
  building: string | null
  room_number: string | null
  shelf_cabinet_code: string | null
  capacity: number | null
  current_occupancy: number
  status: string
  campus?: Campus
  created_at: string
  updated_at: string
}
