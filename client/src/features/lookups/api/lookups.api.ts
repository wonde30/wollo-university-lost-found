/**
 * Lookups/References API client.
 * Provides access to reference data (campuses, categories, locations).
 */

import { apiClient } from '@/lib/http/client'
import { PUBLIC } from '@/lib/api/endpoints'
import type { Category, Location } from '@/types/common.types'

/**
 * Fetch all active categories.
 * Public endpoint - no authentication required.
 */
export async function getCategories(): Promise<Category[]> {
  const { data } = await apiClient.get<{ data: Category[] }>(PUBLIC.CATEGORIES)
  return data.data
}

/**
 * Fetch all active locations.
 * Public endpoint - no authentication required.
 */
export async function getLocations(): Promise<Location[]> {
  const { data } = await apiClient.get<{ data: Location[] }>(PUBLIC.LOCATIONS)
  return data.data
}

/**
 * Note: Campuses endpoint not in public routes.
 * Available via admin routes only: /api/v1/admin/campuses
 */
