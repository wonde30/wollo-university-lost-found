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
  LOST: 'lost',
  FOUND_UNCLAIMED: 'found_unclaimed',
  FOUND_CLAIMED: 'found_claimed',
  IN_CUSTODY: 'in_custody',
  MATCHED: 'matched',
  CLAIMED: 'claimed',
  RETURNED: 'returned',
  PENDING_VERIFICATION: 'pending_verification',
  PENDING_SURRENDER: 'pending_surrender',
  WITHDRAWN: 'withdrawn',
  CANCELLED: 'cancelled',
  EXPIRED: 'expired',
  DISPOSED: 'disposed',
} as const

export type ItemStatus = typeof ITEM_STATUS[keyof typeof ITEM_STATUS]
