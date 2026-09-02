/**
 * Pinia store for public item browsing.
 *
 * Implements stale-while-revalidate (SWR):
 *  - First visit: normal loading spinner until items arrive.
 *  - Return visit (same or different filters): shows cached items INSTANTLY,
 *    then silently re-fetches in the background and updates when done.
 *
 * This eliminates the "pending for ~1 second" flash every time you navigate
 * back to the Browse page.
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Item, ItemListParams } from '@/features/items/types/item.types'
import type { PaginatedResponse } from '@/lib/api/pagination'
import * as publicApi from '../api/public.api'

type Meta = PaginatedResponse<Item>['meta']

export const usePublicItemsStore = defineStore('publicItems', () => {
  // ── Cached results (survive navigation) ──────────────────────────
  const items = ref<Item[]>([])
  const pagination = ref<Meta | null>(null)

  // ── Loading state ─────────────────────────────────────────────────
  // `loading`    → true only when items is EMPTY (first visit / no cache)
  // `refreshing` → true when silently re-fetching in background (has cache)
  const loading = ref(false)
  const refreshing = ref(false)
  const error = ref<string | null>(null)

  // In-flight request guard — prevents duplicate simultaneous fetches
  let _fetchPromise: Promise<void> | null = null

  // ──────────────────────────────────────────────────────────────────
  // Core fetch action
  // ──────────────────────────────────────────────────────────────────
  async function fetchItems(params: ItemListParams = {}): Promise<void> {
    // If already fetching the same thing, reuse the in-flight promise
    if (_fetchPromise) return _fetchPromise

    if (items.value.length === 0) {
      // No cache — show the full loading spinner
      loading.value = true
    } else {
      // Has cache — show stale data immediately, refresh silently
      refreshing.value = true
    }
    error.value = null

    _fetchPromise = (async () => {
      try {
        const response = await publicApi.getPublicItems(params)
        items.value = response.data
        pagination.value = response.meta
      } catch (err: any) {
        error.value = err.message ?? 'Failed to load items'
      } finally {
        loading.value = false
        refreshing.value = false
        _fetchPromise = null
      }
    })()

    return _fetchPromise
  }

  function reset(): void {
    items.value = []
    pagination.value = null
    error.value = null
  }

  return {
    items,
    pagination,
    loading,
    refreshing,
    error,
    fetchItems,
    reset,
  }
})
