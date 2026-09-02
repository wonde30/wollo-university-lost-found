/*
  Custody event types constants and derived types.
  Authoritative single source of truth for physical custody tracking.
 */

export const CUSTODY_EVENT_TYPES = {
  INTAKE: 'intake',
  TRANSFER: 'transfer',
  RETURN: 'return',
  INSPECTION: 'inspection',
} as const

export type CustodyEventType = typeof CUSTODY_EVENT_TYPES[keyof typeof CUSTODY_EVENT_TYPES]
