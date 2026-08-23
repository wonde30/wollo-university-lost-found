/**
 * Claims feature types based on Laravel backend contract.
 */

import type { Item } from '@/features/items/types/item.types'
import type { User } from '@/features/auth/types/auth.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

export type ClaimStatus = 'pending' | 'under_review' | 'approved' | 'rejected' | 'withdrawn'

export interface ClaimEvidence {
  id: number
  claim_id: number
  file_path: string
  file_url: string
  file_type: string
  file_size: number
  description: string | null
  created_at: string
}

export interface ClaimStatusHistory {
  id: number
  claim_id: number
  previous_status: string | null
  new_status: string
  reason: string | null
  changed_by?: User
  created_at: string
}

export interface Claim {
  id: number
  item_id: number
  claimant_id: number
  user_id: number
  explanation: string
  status: ClaimStatus
  reviewed_by: number | null
  review_note: string | null
  reviewed_at: string | null
  auto_rejected: boolean
  item?: Item
  claimant?: User
  user?: User
  reviewer?: User
  evidence?: ClaimEvidence[]
  status_histories?: ClaimStatusHistory[]
  created_at: string
  updated_at: string
}

// ==========================================
// Request Payloads
// ==========================================

export interface StoreClaimData {
  item_id: number
  explanation: string
  evidence?: File[]
}

export interface ReviewClaimData {
  status: 'approved' | 'rejected'
  review_note?: string
}

export interface ClaimListParams extends PaginationParams {
  status?: ClaimStatus
  item_id?: number
}

// ==========================================
// Response Types
// ==========================================

export type ClaimListResponse = PaginatedResponse<Claim>

export interface ClaimDetailResponse {
  data: Claim
}

export interface ClaimCreateResponse {
  message: string
  data: Claim
}

export interface ClaimReviewResponse {
  message: string
  data: Claim
}
