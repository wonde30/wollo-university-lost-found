/**
 * Claim status constants and derived types.
 * Authoritative single source of truth for claim decisions.
 */

export const CLAIM_STATUS = {
  PENDING: 'pending',
  APPROVED: 'approved',
  REJECTED: 'rejected',
  REVERSED: 'reversed',
} as const

export type ClaimStatus = typeof CLAIM_STATUS[keyof typeof CLAIM_STATUS]
