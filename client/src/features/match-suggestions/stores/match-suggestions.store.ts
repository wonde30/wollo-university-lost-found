import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
  MatchSuggestion,
  MatchSuggestionListParams,
} from '../types/match-suggestion.types'
import type { PaginationMeta } from '@/types/common.types'
import * as matchApi from '../api/match-suggestions.api'

export const useMatchSuggestionsStore = defineStore('matchSuggestions', () => {
  const suggestions = ref<MatchSuggestion[]>([])
  const loading = ref(false)
  const error = ref<Error | null>(null)
  const pagination = ref<PaginationMeta | null>(null)

  let _fetchPromise: Promise<void> | null = null

  async function fetchSuggestions(params?: MatchSuggestionListParams): Promise<void> {
    if (_fetchPromise) return _fetchPromise

    if (suggestions.value.length === 0) {
      loading.value = true
    }
    error.value = null

    _fetchPromise = (async () => {
      try {
        const response = await matchApi.getMatchSuggestions(params)
        suggestions.value = response.data
        pagination.value = response.meta
      } catch (err) {
        error.value = err as Error
        throw err
      } finally {
        loading.value = false
        _fetchPromise = null
      }
    })()

    return _fetchPromise
  }

  async function reviewSuggestion(
    id: number,
    status: 'confirmed' | 'dismissed'
  ): Promise<MatchSuggestion> {
    loading.value = true
    error.value = null
    try {
      const updated = await matchApi.updateMatchSuggestion(id, status)
      const idx = suggestions.value.findIndex(s => s.id === id)
      if (idx !== -1) {
        suggestions.value[idx] = updated
      }
      return updated
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  return {
    suggestions,
    loading,
    error,
    pagination,
    fetchSuggestions,
    reviewSuggestion,
  }
})
