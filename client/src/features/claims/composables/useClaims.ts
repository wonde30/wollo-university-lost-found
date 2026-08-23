/**
 * Claims composable wrapping the Pinia claims store.
 * Provides role-aware claim retrieval and management actions.
 */

import { computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useClaimsStore } from '../stores/claims.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import type { ClaimListParams } from '../types/claim.types'

export function useClaims() {
  const store = useClaimsStore()
  const authStore = useAuthStore()

  const {
    claims,
    currentClaim,
    loaded,
    loading,
    fetching,
    submitting,
    reviewing,
    error,
    pagination,
    pendingClaims,
    hasClaims,
  } = storeToRefs(store)

  const isStaffOrAdmin = computed(() => authStore.isStaff || authStore.isAdmin)

  async function fetchRoleAwareClaims(params: ClaimListParams = {}, force = false): Promise<void> {
    // If student, the backend /claims endpoint returns the student's own claims
    // If staff/admin, it returns all claims
    return store.fetchClaims(params, force)
  }

  return {
    // Reactive State
    claims,
    currentClaim,
    loaded,
    loading,
    fetching,
    submitting,
    reviewing,
    error,
    pagination,
    pendingClaims,
    hasClaims,
    isStaffOrAdmin,

    // Actions
    fetchClaims: fetchRoleAwareClaims,
    loadClaims: fetchRoleAwareClaims, // Alias
    refresh: store.refresh,
    fetchClaim: store.fetchClaim,
    createClaim: store.createClaim,
    submitClaim: store.submitClaim,
    reviewClaim: store.reviewClaim,
    reverseClaim: store.reverseClaim,
    clearCurrentClaim: store.clearCurrentClaim,
  }
}
