/**
 * Claims feature types based on Laravel backend contract.
 */

import type { Item } from '@/features/items/types/item.types'
import type { User } from '@/features/auth/types/auth.types'
import type { ReturnRecord } from '@/features/returns/types/return.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

/**
 * Claim status values matching backend ClaimStatusEnum exactly.
 */
export type ClaimStatus =
  | 'pending'       // Awaiting staff review
  | 'under_review'  // Currently under staff review
  | 'approved'      // Claim approved by staff
  | 'rejected'      // Claim rejected by staff
  | 'reversed'      // Previously approved, then reversed
  | 'cancelled'     // Cancelled by claimant or system
  | 'withdrawn'     // Withdrawn by claimant
  | string          // Forward-compatible catch-all

export interface ClaimEvidence {
  id: number
  claim_id: number
  uploaded_by?: number
  evidence_type?: string
  path?: string
  url?: string
  file_path?: string
  file_url?: string
  original_name?: string
  mime_type?: string
  file_type?: string
  size_bytes?: number
  file_size?: number
  description?: string | null
  uploaded_at?: string
  created_at: string
}

export interface ClaimStatusHistory {
  id: number
  claim_id: number
  changed_by?: number | null
  from_status?: string | null
  to_status?: string
  previous_status?: string | null
  new_status?: string
  changed_by_role?: string | null
  was_auto_rejected?: boolean
  reason?: string | null
  note?: string | null
  notes?: string | null
  ip_address?: string | null
  changed_by_user?: User
  user?: User
  created_at: string
}

export interface Claim {
  id: number
  item_id: number
  claimant_id: number
  user_id?: number
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
  return_record?: ReturnRecord
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
  search?: string
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
