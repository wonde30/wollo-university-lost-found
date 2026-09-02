/**
 * Common types used across the application.
 * These are shared types not specific to any single feature.
 */

// Re-export pagination from lib
export type { PaginationMeta, PaginationParams } from '@/lib/api/pagination'
export type { ApiResponse, PaginatedResponse } from '@/lib/api/response'

/**
 * Campus domain type matching CampusResource
 */
export interface Campus {
  id: number
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  short_code: string
  code?: string
  city?: string | null
  region?: string | null
  address: string | null
  description?: string | null
  phone?: string | null
  email?: string | null
  is_active: boolean
  organizational_units?: OrganizationalUnit[]
  locations?: Location[]
  created_at?: string
  updated_at?: string
}


/**
 * Category domain type matching CategoryResource
 */
export interface Category {
  id: number
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  icon_slug?: string | null
  icon?: string | null // Alias for backwards compatibility
  sort_order?: number
  is_active: boolean
  items_count?: number
}

/**
 * Location domain type matching LocationResource
 */
export interface Location {
  id: number
  campus_id: number
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  code?: string
  building?: string | null
  floor?: string | null
  room_number?: string | null
  coordinates?: string | null
  is_active: boolean
  campus?: Campus
  created_at?: string
  updated_at?: string
}

/**
 * OrganizationalUnitType domain type matching database model & controller
 */
export interface OrganizationalUnitType {
  id: number
  code: string
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  description?: string | null
  is_root?: boolean
  is_active?: boolean
  child_type_relations?: Array<{
    id: number
    parent_type_id: number
    child_type_id: number
    child_type?: OrganizationalUnitType
  }>
  parent_type_relations?: Array<{
    id: number
    parent_type_id: number
    child_type_id: number
    parent_type?: OrganizationalUnitType
  }>
  organizational_units?: OrganizationalUnit[]
  created_at?: string
  updated_at?: string
}

/**
 * OrganizationalUnit domain type matching OrganizationalUnitResource
 */
export interface OrganizationalUnit {
  id: number
  campus_id: number
  parent_id?: number | null
  type_id: number
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  short_code: string
  description?: string | null
  is_active: boolean
  campus?: Campus
  type?: {
    id: number
    code: string
    name: string
    name_am?: string | null
    display_name_am?: string | null
  }
  parent?: OrganizationalUnit | null

  children?: OrganizationalUnit[]
  is_primary?: boolean
  enrolled_year?: number | string | null
  created_at?: string
  updated_at?: string
}

/**
 * StorageLocation domain type matching StorageLocationResource
 */
export interface StorageLocation {
  id: number
  campus_id: number
  name: string
  name_am?: string | null
  display_name_am?: string | null
  display_name?: string | null
  code?: string
  description?: string | null
  building?: string | null
  room_number?: string | null
  shelf_cabinet_code?: string | null
  capacity?: number | null
  current_occupancy?: number
  status?: string
  is_active?: boolean
  campus?: Campus
  created_at?: string
  updated_at?: string
}
