/**
 * Item types and status constants and derived types.
 * Authoritative single source of truth for item lifecycle states.
 */

export const ITEM_TYPES = {
  LOST: 'lost',
  FOUND: 'found',
} as const

export type ItemType = typeof ITEM_TYPES[keyof typeof ITEM_TYPES]

export const ITEM_STATUS = {
  REPORTED: 'reported',
  VERIFIED: 'verified',
  IN_CUSTODY: 'in_custody',
  CLAIM_PENDING: 'claim_pending',
  CLAIMED: 'claimed',
  RETURNED: 'returned',
  CLOSED: 'closed',
  EXPIRED: 'expired',
} as const

export type ItemStatus = typeof ITEM_STATUS[keyof typeof ITEM_STATUS]
