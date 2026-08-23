/**
 * Claims API client for managing item claims.
 * Handles claim submission, review, and reversal.
 */

import { apiClient } from '@/lib/http/client'
import { CLAIMS } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import type { ApiResponse } from '@/lib/api/response'
import type {
  Claim,
  ClaimListParams,
  StoreClaimData,
  ReviewClaimData,
} from '../types/claim.types'

// ==========================================
// Claims Management (Authenticated)
// ==========================================

/**
 * Get paginated list of claims.
 */
export async function getClaims(
  filters?: ClaimListParams,
  pagination?: PaginationParams
): Promise<PaginatedResponse<Claim>> {
  const params = {
    ...filters,
    ...pagination,
  }
  
  const { data } = await apiClient.get<PaginatedResponse<Claim>>(CLAIMS.INDEX, { params })
  return data
}

/**
 * Get single claim details.
 */
export async function getClaim(id: number): Promise<Claim> {
  const { data } = await apiClient.get<ApiResponse<Claim>>(CLAIMS.SHOW(id))
  return data.data!
}

/**
 * Submit a new claim for an item.
 */
export async function createClaim(claimData: StoreClaimData): Promise<Claim> {
  const formData = new FormData()
  
  // Required fields
  formData.append('item_id', String(claimData.item_id))
  formData.append('explanation', claimData.explanation)
  
  // Evidence files
  if (claimData.evidence && Array.isArray(claimData.evidence)) {
    claimData.evidence.forEach((file) => {
      formData.append('evidence[]', file)
    })
  }
  
  const { data } = await apiClient.post<ApiResponse<Claim>>(CLAIMS.CREATE, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  
  return data.data!
}

// ==========================================
// Claim Review (Staff/Admin Only)
// ==========================================

/**
 * Review a claim (approve or reject).
 * Requires staff or admin role.
 */
export async function reviewClaim(id: number, reviewData: ReviewClaimData): Promise<Claim> {
  const { data } = await apiClient.post<ApiResponse<Claim>>(CLAIMS.REVIEW(id), reviewData)
  return data.data!
}

/**
 * Reverse a claim decision.
 * Requires staff or admin role.
 */
export async function reverseClaim(id: number, reviewNote?: string): Promise<Claim> {
  const { data } = await apiClient.post<ApiResponse<Claim>>(CLAIMS.REVERSE(id), {
    review_note: reviewNote,
  })
  return data.data!
}
