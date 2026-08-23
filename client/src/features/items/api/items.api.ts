/**
 * Items API client for managing lost and found items (authenticated).
 * For authenticated user operations on items.
 */

import { apiClient } from '@/lib/http/client'
import { ITEMS, PUBLIC } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import type { ApiResponse } from '@/lib/api/response'
import type {
  Item,
  ItemListParams,
  StoreLostItemData,
  StoreFoundItemData,
  UpdateItemData,
  UpdateItemStatusData,
  ItemPhoto,
  TrackItemResult,
} from '../types/item.types'

// ==========================================
// Authenticated Items Management
// ==========================================

/**
 * Get paginated list of items (authenticated user's items or all if staff/admin).
 */
export async function getItems(
  filters?: ItemListParams,
  pagination?: PaginationParams
): Promise<PaginatedResponse<Item>> {
  const params = {
    ...filters,
    ...pagination,
  }
  
  const { data } = await apiClient.get<PaginatedResponse<Item>>(ITEMS.INDEX, { params })
  return data
}

/**
 * Get single item details (authenticated).
 */
export async function getItem(id: number): Promise<Item> {
  const { data } = await apiClient.get<ApiResponse<Item>>(ITEMS.SHOW(id))
  return data.data!
}

/**
 * Report a lost item.
 */
export async function createLostItem(itemData: StoreLostItemData): Promise<Item> {
  const formData = new FormData()

  Object.entries(itemData).forEach(([key, value]) => {
    // Skip empty strings, undefined, null — let backend treat as nullable
    if (value === undefined || value === null || value === '') return

    if (key === 'photos' && Array.isArray(value)) {
      // Files must be appended as raw File objects
      value.forEach((file) => formData.append('photos[]', file))
    } else if (typeof value === 'boolean') {
      // Laravel boolean rule accepts "1"/"0" — NOT "true"/"false"
      formData.append(key, value ? '1' : '0')
    } else if (Array.isArray(value)) {
      value.forEach((v) => formData.append(`${key}[]`, String(v)))
    } else {
      formData.append(key, String(value))
    }
  })

  const { data } = await apiClient.post<ApiResponse<Item>>(ITEMS.LOST, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })

  return data.data!
}

/**
 * Register a found item.
 */
export async function createFoundItem(itemData: StoreFoundItemData): Promise<Item> {
  const formData = new FormData()

  Object.entries(itemData).forEach(([key, value]) => {
    // Skip empty strings, undefined, null — let backend treat as nullable
    if (value === undefined || value === null || value === '') return

    if (key === 'photos' && Array.isArray(value)) {
      value.forEach((file) => formData.append('photos[]', file))
    } else if (typeof value === 'boolean') {
      // Laravel boolean rule accepts "1"/"0" — NOT "true"/"false"
      formData.append(key, value ? '1' : '0')
    } else if (Array.isArray(value)) {
      value.forEach((v) => formData.append(`${key}[]`, String(v)))
    } else {
      formData.append(key, String(value))
    }
  })

  const { data } = await apiClient.post<ApiResponse<Item>>(ITEMS.FOUND, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })

  return data.data!
}

/**
 * Update item details.
 */
export async function updateItem(id: number, itemData: UpdateItemData): Promise<Item> {
  const { data } = await apiClient.put<ApiResponse<Item>>(ITEMS.UPDATE(id), itemData)
  return data.data!
}

/**
 * Delete item.
 */
export async function deleteItem(id: number): Promise<void> {
  await apiClient.delete(ITEMS.DELETE(id))
}

/**
 * Update item status.
 */
export async function updateItemStatus(id: number, statusData: UpdateItemStatusData): Promise<Item> {
  const { data } = await apiClient.patch<ApiResponse<Item>>(ITEMS.UPDATE_STATUS(id), statusData)
  return data.data!
}

// ==========================================
// Item Photos Management
// ==========================================

/**
 * Add photos to an item.
 */
export async function addItemPhotos(id: number, photos: File[]): Promise<ItemPhoto[]> {
  const formData = new FormData()
  
  photos.forEach((photo) => {
    formData.append('photos[]', photo)
  })

  const { data } = await apiClient.post<ApiResponse<ItemPhoto[]>>(
    ITEMS.ADD_PHOTOS(id),
    formData,
    {
      headers: { 'Content-Type': 'multipart/form-data' },
    }
  )

  return data.data!
}

/**
 * Delete item photo.
 */
export async function deleteItemPhoto(itemId: number, photoId: number): Promise<void> {
  await apiClient.delete(ITEMS.DELETE_PHOTO(itemId, photoId))
}

/**
 * Track item by reference code (public).
 */
export async function trackItem(referenceCode: string): Promise<TrackItemResult> {
  const { data } = await apiClient.get<any>(
    PUBLIC.TRACK(referenceCode)
  )
  return (data?.data ?? data) as TrackItemResult
}
