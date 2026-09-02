/**
 * Composable for public item discovery.
 *
 * Thin wrapper around usePublicItemsStore.
 * Results survive navigation — return visits show cached data instantly
 * (stale-while-revalidate) instead of a full loading spinner every time.
 */

import { storeToRefs } from 'pinia'
import { ref } from 'vue'
import type { Item } from '@/features/items/types/item.types'
import { usePublicItemsStore } from '../stores/public-items.store'
import * as publicApi from '../api/public.api'

export function usePublicItems() {
  const store = usePublicItemsStore()
  const { items, pagination, loading, refreshing, error } = storeToRefs(store)

  // ── Single-item and tracking state (not shared — local to caller) ─
  const currentItem = ref<Item | null>(null)
  const trackingInfo = ref<any | null>(null)

  const fetchItems = store.fetchItems

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
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load item'
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
    } catch (err: any) {
      error.value = err.message ?? 'Failed to track item'
      throw err
    } finally {
      loading.value = false
    }
  }

  function clearTracking(): void {
    trackingInfo.value = null
  }

  return {
    // State
    items,
    currentItem,
    trackingInfo,
    loading,
    refreshing,
    error,
    pagination,

    // Actions
    fetchItems,
    loadPublicItems: fetchItems, // Alias for backward compatibility
    fetchItemDetail,
    trackItem,
    clearTracking,
  }
}
