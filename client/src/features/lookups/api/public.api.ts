/**
 * Public API client for guest/unauthenticated access.
 * Includes public item discovery, categories, locations, and tracking.
 */

import { apiClient } from '@/lib/http/client'
import { PUBLIC } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import type { Item, ItemListParams } from '@/features/items/types/item.types'
import type { Category, Location } from '@/types/common.types'
import type { PublicStatistics } from '../types/landing.types'

// ==========================================
// Public Items Discovery
// ==========================================

/**
 * Fetch paginated public items (lost & found).
 * No authentication required.
 */
export async function getPublicItems(
  filters?: ItemListParams,
  pagination?: PaginationParams
): Promise<PaginatedResponse<Item>> {
  const params = {
    ...filters,
    ...pagination,
  }
  
  const { data } = await apiClient.get<PaginatedResponse<Item>>(PUBLIC.ITEMS, { params })
  return data
}

/**
 * Fetch single public item detail.
 * No authentication required.
 */
export async function getPublicItemDetail(id: number): Promise<Item> {
  const { data } = await apiClient.get<{ data: Item }>(PUBLIC.ITEM_DETAIL(id))
  return data.data
}

// ==========================================
// Public Reference Data
// ==========================================

/**
 * Fetch all active categories.
 * No authentication required.
 */
export async function getPublicCategories(): Promise<Category[]> {
  const { data } = await apiClient.get<{ data: Category[] }>(PUBLIC.CATEGORIES)
  return data.data
}

/**
 * Fetch all active locations.
 * No authentication required.
 */
export async function getPublicLocations(): Promise<Location[]> {
  const { data } = await apiClient.get<{ data: Location[] }>(PUBLIC.LOCATIONS)
  return data.data
}

// ==========================================
// Public Item Tracking
// ==========================================

/**
 * Track item by reference code.
 * No authentication required but rate-limited (20 requests per minute).
 */
export async function trackItem(referenceCode: string): Promise<any> {
  const { data } = await apiClient.get<any>(PUBLIC.TRACK(referenceCode))
  return data?.data ?? data
}

// ==========================================
// Public Landing Page Statistics
// ==========================================

/**
 * Fetch aggregate platform statistics for the public landing page.
 * Returns safe, non-sensitive counts only.
 * No authentication required. Cached server-side for 5 minutes.
 */
export async function getPublicStatistics(): Promise<PublicStatistics> {
  const { data } = await apiClient.get<{ data: PublicStatistics }>(PUBLIC.STATISTICS)
  return data.data
}

