/**
 * Composable for public item discovery.
 * For guest/unauthenticated access to browse lost & found items.
 */

import { ref, type Ref } from 'vue'
import type { Item, ItemListParams } from '@/features/items/types/item.types'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import * as publicApi from '../api/public.api'

export function usePublicItems() {
  const items: Ref<Item[]> = ref([])
  const currentItem: Ref<Item | null> = ref(null)
  const trackingInfo: Ref<any | null> = ref(null)
  const loading = ref(false)
  const error: Ref<Error | null> = ref(null)
  const pagination: Ref<PaginatedResponse<Item>['meta'] | null> = ref(null)

  /**
   * Fetch paginated public items with filters.
   */
  async function fetchItems(
    filters?: ItemListParams,
    paginationParams?: PaginationParams
  ): Promise<void> {
    loading.value = true
    error.value = null
    try {
      const response = await publicApi.getPublicItems(filters, paginationParams)
      items.value = response.data
      pagination.value = response.meta
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Alias for fetchItems - for backward compatibility.
   */
  const loadPublicItems = fetchItems

  /**
   * Fetch single public item detail.
   */
  async function fetchItemDetail(id: number): Promise<Item> {
    loading.value = true
    error.value = null
    try {
      const item = await publicApi.getPublicItemDetail(id)
      currentItem.value = item
      return item
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Track item by reference code.
   */
  async function trackItem(referenceCode: string): Promise<any> {
    loading.value = true
    error.value = null
    try {
      const info = await publicApi.trackItem(referenceCode)
      trackingInfo.value = info
      return info
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Clear tracking info.
   */
  function clearTracking(): void {
    trackingInfo.value = null
  }

  return {
    // State
    items,
    currentItem,
    trackingInfo,
    loading,
    error,
    pagination,

    // Actions
    fetchItems,
    loadPublicItems, // Alias
    fetchItemDetail,
    trackItem,
    clearTracking,
  }
}
