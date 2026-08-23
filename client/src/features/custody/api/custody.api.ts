/**
 * Custody API client for managing physical custody of items.
 * Staff and admin only - handles custody events and storage location management.
 */

import { apiClient } from '@/lib/http/client'
import { CUSTODY } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import type { ApiResponse } from '@/lib/api/response'
import type { StorageLocation } from '@/types/common.types'
import type {
  CustodyEvent,
  CustodyListParams,
  StoreCustodyEventData,
  MoveItemCustodyData,
} from '../types/custody.types'

// ==========================================
// Custody Events Management
// ==========================================

/**
 * Get paginated list of custody events.
 * Staff/Admin only.
 */
export async function getCustodyEvents(
  filters?: CustodyListParams,
  pagination?: PaginationParams
): Promise<PaginatedResponse<CustodyEvent>> {
  const params = {
    ...filters,
    ...pagination,
  }
  
  const { data } = await apiClient.get<PaginatedResponse<CustodyEvent>>(CUSTODY.INDEX, { params })
  return data
}

/**
 * Create a new custody event.
 * Staff/Admin only.
 */
export async function createCustodyEvent(custodyData: StoreCustodyEventData): Promise<CustodyEvent> {
  const { data } = await apiClient.post<ApiResponse<CustodyEvent>>(CUSTODY.CREATE, custodyData)
  return data.data!
}

/**
 * Move an item to a different storage location.
 * Staff/Admin only.
 */
export async function moveItemCustody(itemId: number, moveData: MoveItemCustodyData): Promise<CustodyEvent> {
  const { data } = await apiClient.post<ApiResponse<CustodyEvent>>(CUSTODY.MOVE_ITEM(itemId), moveData)
  return data.data!
}

// ==========================================
// Storage Locations Management (CRUD)
// ==========================================

/**
 * Get all storage locations.
 * Staff/Admin only.
 */
export async function getStorageLocations(): Promise<StorageLocation[]> {
  const { data } = await apiClient.get<ApiResponse<StorageLocation[]>>(CUSTODY.STORAGE_LOCATIONS)
  return data.data!
}

/**
 * Get single storage location.
 * Staff/Admin only.
 */
export async function getStorageLocation(id: number): Promise<StorageLocation> {
  const { data } = await apiClient.get<ApiResponse<StorageLocation>>(CUSTODY.STORAGE_LOCATION(id))
  return data.data!
}

/**
 * Create storage location.
 * Staff/Admin only.
 */
export async function createStorageLocation(locationData: any): Promise<StorageLocation> {
  const { data } = await apiClient.post<ApiResponse<StorageLocation>>(
    CUSTODY.STORAGE_LOCATIONS,
    locationData
  )
  return data.data!
}

/**
 * Update storage location.
 * Staff/Admin only.
 */
export async function updateStorageLocation(
  id: number,
  locationData: any
): Promise<StorageLocation> {
  const { data } = await apiClient.put<ApiResponse<StorageLocation>>(
    CUSTODY.STORAGE_LOCATION(id),
    locationData
  )
  return data.data!
}

/**
 * Delete storage location.
 * Staff/Admin only.
 */
export async function deleteStorageLocation(id: number): Promise<void> {
  await apiClient.delete(CUSTODY.STORAGE_LOCATION(id))
}
