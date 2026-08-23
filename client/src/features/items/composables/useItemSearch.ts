import { ref } from 'vue'
import type { TrackItemResult } from '../types/item.types'
import * as itemsApi from '../api/items.api'

export function useItemSearch() {
  const trackingResult = ref<TrackItemResult | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function trackByReference(referenceCode: string): Promise<TrackItemResult | null> {
    if (!referenceCode) return null
    loading.value = true
    error.value = null
    try {
      const res = await itemsApi.trackItem(referenceCode.trim())
      trackingResult.value = res
      return res
    } catch (err: any) {
      error.value = err.message || 'No item found with this reference code.'
      trackingResult.value = null
      return null
    } finally {
      loading.value = false
    }
  }

  return {
    trackingResult,
    loading,
    error,
    trackByReference,
  }
}
