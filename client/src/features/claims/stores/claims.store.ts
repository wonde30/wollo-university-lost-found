/**
 * Claims store using Pinia.
 * Single source of truth for claims lists so ReviewClaimsPage and
 * MyClaimsPage share state and never duplicate concurrent fetches.
 */

import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type {
  Claim,
  ClaimListParams,
  StoreClaimData,
  ReviewClaimData,
} from '../types/claim.types'
import type { PaginationMeta } from '@/types/common.types'
import * as claimsApi from '../api/claims.api'

export const useClaimsStore = defineStore('claims', () => {
  // State
  const claims = ref<Claim[]>([])
  const currentClaim = ref<Claim | null>(null)
  const loaded = ref(false)
  const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  // Loading States
  const loading = ref(false)
  const fetching = ref(false)
  const submitting = ref(false)
  const reviewing = ref(false)
  const error = ref<string | null>(null)

  // Track the last filter params so callers can refresh with the same params
  let _lastParams: ClaimListParams = {}

  // Inflight guard — prevents duplicate concurrent requests
  let _fetchPromise: Promise<void> | null = null

  // Getters
  const pendingClaims = computed(() => claims.value.filter(c => c.status === 'pending'))
  const hasClaims = computed(() => claims.value.length > 0)

  // Actions
  async function fetchClaims(params: ClaimListParams = {}, force = false): Promise<void> {
    if (!force && loaded.value && claims.value.length > 0 && Object.keys(params).length === 0) {
      return
    }

    if (_fetchPromise) return _fetchPromise

    _fetchPromise = _doFetch(params).finally(() => {
      _fetchPromise = null
    })

    return _fetchPromise
  }

  async function _doFetch(params: ClaimListParams): Promise<void> {
    fetching.value = true
    if (claims.value.length === 0) {
      loading.value = true
    }
    error.value = null
    _lastParams = params
    try {
      const res = await claimsApi.getClaims(params, {
        page: params.page,
        per_page: params.per_page,
      })
      claims.value = res.data
      pagination.value = res.meta
      loaded.value = true
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load claims'
    } finally {
      fetching.value = false
      loading.value = false
    }
  }

  /** Re-fetch using the last used params (e.g. after a review action). */
  async function refresh(): Promise<void> {
    return fetchClaims(_lastParams, true)
  }

  async function fetchClaim(id: number): Promise<Claim | null> {
    fetching.value = true
    loading.value = true
    error.value = null
    try {
      const claim = await claimsApi.getClaim(id)
      currentClaim.value = claim
      return claim
    } catch (err: any) {
      error.value = err.message ?? 'Failed to load claim'
      return null
    } finally {
      fetching.value = false
      loading.value = false
    }
  }

  async function createClaim(data: StoreClaimData): Promise<Claim> {
    submitting.value = true
    loading.value = true
    error.value = null
    try {
      const claim = await claimsApi.createClaim(data)
      claims.value.unshift(claim)
      return claim
    } catch (err: any) {
      error.value = err.message ?? 'Failed to submit claim'
      throw err
    } finally {
      submitting.value = false
      loading.value = false
    }
  }

  const submitClaim = createClaim // Alias

  async function reviewClaim(id: number, data: ReviewClaimData): Promise<Claim> {
    reviewing.value = true
    loading.value = true
    error.value = null
    try {
      const updated = await claimsApi.reviewClaim(id, data)
      _patchInList(updated)
      if (currentClaim.value?.id === id) currentClaim.value = updated
      return updated
    } catch (err: any) {
      error.value = err.message ?? 'Failed to review claim'
      throw err
    } finally {
      reviewing.value = false
      loading.value = false
    }
  }

  async function reverseClaim(id: number, reviewNote?: string): Promise<Claim> {
    reviewing.value = true
    loading.value = true
    error.value = null
    try {
      const updated = await claimsApi.reverseClaim(id, reviewNote)
      _patchInList(updated)
      if (currentClaim.value?.id === id) currentClaim.value = updated
      return updated
    } catch (err: any) {
      error.value = err.message ?? 'Failed to reverse claim'
      throw err
    } finally {
      reviewing.value = false
      loading.value = false
    }
  }

  function clearCurrentClaim(): void {
    currentClaim.value = null
  }

  function _patchInList(updated: Claim): void {
    const index = claims.value.findIndex(c => c.id === updated.id)
    if (index !== -1) {
      claims.value[index] = updated
    }
  }

  return {
    // State
    claims,
    currentClaim,
    loaded,
    pagination,
    error,

    // Loading States
    loading,
    fetching,
    submitting,
    reviewing,

    // Getters
    pendingClaims,
    hasClaims,

    // Actions
    fetchClaims,
    refresh,
    fetchClaim,
    createClaim,
    submitClaim,
    reviewClaim,
    reverseClaim,
    clearCurrentClaim,
  }
})
