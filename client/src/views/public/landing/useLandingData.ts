/**
 * Landing page data composable.
 *
 * Orchestrates all API calls for the public landing page in a single place.
 * Uses Promise.allSettled so that one failing section does NOT block the rest.
 */

import { ref, onMounted } from 'vue'
import type { Item } from '@/features/items/types/item.types'
import type { Category } from '@/types/common.types'
import type { PublicStatistics } from '@/features/lookups/types/landing.types'
import * as publicApi from '@/features/lookups/api/public.api'

export function useLandingData() {
  // ── Statistics ──────────────────────────────────────────────
  const statistics = ref<PublicStatistics | null>(null)
  const statsLoading = ref(true)
  const statsError = ref(false)

  // ── Categories ─────────────────────────────────────────────
  const categories = ref<Category[]>([])
  const categoriesLoading = ref(true)
  const categoriesError = ref(false)

  // ── Recent Found Items ─────────────────────────────────────
  const recentItems = ref<Item[]>([])
  const itemsLoading = ref(true)
  const itemsError = ref(false)

  async function fetchStatistics(): Promise<void> {
    statsLoading.value = true
    statsError.value = false
    try {
      statistics.value = await publicApi.getPublicStatistics()
    } catch {
      statsError.value = true
    } finally {
      statsLoading.value = false
    }
  }

  async function fetchCategories(): Promise<void> {
    categoriesLoading.value = true
    categoriesError.value = false
    try {
      categories.value = await publicApi.getPublicCategories()
    } catch {
      categoriesError.value = true
    } finally {
      categoriesLoading.value = false
    }
  }

  async function fetchRecentItems(): Promise<void> {
    itemsLoading.value = true
    itemsError.value = false
    try {
      const response = await publicApi.getPublicItems({ type: 'found', per_page: 6 })
      recentItems.value = response.data
    } catch {
      itemsError.value = true
    } finally {
      itemsLoading.value = false
    }
  }

  /**
   * Fetch all landing data concurrently.
   * Each section is independent — one failure does not block others.
   */
  async function fetchAll(): Promise<void> {
    await Promise.allSettled([
      fetchStatistics(),
      fetchCategories(),
      fetchRecentItems(),
    ])
  }

  onMounted(fetchAll)

  return {
    // Statistics
    statistics,
    statsLoading,
    statsError,
    retryStats: fetchStatistics,

    // Categories
    categories,
    categoriesLoading,
    categoriesError,
    retryCategories: fetchCategories,

    // Recent Items
    recentItems,
    itemsLoading,
    itemsError,
    retryItems: fetchRecentItems,
  }
}
