/**
 * Returns store using Pinia.
 * Single source of truth for return records — prevents fresh-state-on-mount
 * issues that plagued the old plain-composable approach.
 */

import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
  ReturnRecord,
  ReturnListParams,
  StoreReturnData,
} from '../types/return.types'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import * as returnsApi from '../api/returns.api'

export const useReturnsStore = defineStore('returns', () => {
  // ==========================================
  // State
  // ==========================================

  const returns = ref<ReturnRecord[]>([])
  const currentReturn = ref<ReturnRecord | null>(null)
  const loading = ref(false)
  const error = ref<Error | null>(null)
  const pagination = ref<PaginatedResponse<ReturnRecord>['meta'] | null>(null)

  // Inflight guard — prevents duplicate concurrent requests
  let _fetchPromise: Promise<void> | null = null

  // ==========================================
  // Actions
  // ==========================================

  /**
   * Fetch paginated list of returns.
   * Shows loading spinner only on first visit (stale-while-revalidate pattern).
   */
  async function fetchReturns(
    filters?: ReturnListParams,
    paginationParams?: PaginationParams
  ): Promise<void> {
    if (_fetchPromise) return _fetchPromise

    _fetchPromise = _doFetch(filters, paginationParams).finally(() => {
      _fetchPromise = null
    })

    return _fetchPromise
  }

  async function _doFetch(
    filters?: ReturnListParams,
    paginationParams?: PaginationParams
  ): Promise<void> {
    // Only show spinner when the list is empty (first load)
    if (returns.value.length === 0) {
      loading.value = true
    }
    error.value = null
    try {
      const response = await returnsApi.getReturns(filters, paginationParams)
      returns.value = response.data
      pagination.value = response.meta
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Fetch a single return record.
   */
  async function fetchReturn(id: number): Promise<ReturnRecord> {
    loading.value = true
    error.value = null
    try {
      const returnRecord = await returnsApi.getReturn(id)
      currentReturn.value = returnRecord
      return returnRecord
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Create / process a new return record.
   */
  async function createReturn(returnData: StoreReturnData): Promise<ReturnRecord> {
    loading.value = true
    error.value = null
    try {
      const returnRecord = await returnsApi.createReturn(returnData)
      currentReturn.value = returnRecord
      if (returns.value.length > 0) {
        returns.value.unshift(returnRecord)
      }
      return returnRecord
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Recipient confirms physical collection (FR-44).
   */
  async function confirmReturn(id: number): Promise<ReturnRecord> {
    loading.value = true
    error.value = null
    try {
      const updated = await returnsApi.confirmReturn(id)
      if (currentReturn.value?.id === id) {
        currentReturn.value = updated
      }
      const idx = returns.value.findIndex(r => r.id === id)
      if (idx !== -1) {
        returns.value[idx] = updated
      }
      return updated
    } catch (err) {
      error.value = err as Error
      throw err
    } finally {
      loading.value = false
    }
  }

  async function exportCsv(filters?: { date_from?: string; date_to?: string }): Promise<void> {
    return returnsApi.exportReturnsCsv(filters)
  }

  function clearCurrentReturn(): void {
    currentReturn.value = null
  }

  return {
    // State
    returns,
    currentReturn,
    loading,
    error,
    pagination,

    // Actions
    fetchReturns,
    fetchReturn,
    createReturn,
    confirmReturn,
    exportCsv,
    /** Alias kept for backward compatibility. */
    processReturn: createReturn,
    clearCurrentReturn,
  }
})
