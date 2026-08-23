import { required, minLength } from '@/validation/common.validation'
import type { StoreClaimData, ReviewClaimData } from '../types/claim.types'

export function validateStoreClaimForm(data: StoreClaimData): Record<string, string> {
  const errors: Record<string, string> = {}

  const itemReq = required(data.item_id, 'Item')
  if (itemReq) errors.item_id = itemReq

  const expReq = required(data.explanation, 'Ownership description')
  if (expReq) errors.explanation = expReq
  else {
    const expMin = minLength(50, data.explanation, 'Ownership description')
    if (expMin) errors.explanation = expMin
  }

  return errors
}

export function validateReviewClaimForm(data: ReviewClaimData): Record<string, string> {
  const errors: Record<string, string> = {}

  const statusReq = required(data.status, 'Review decision')
  if (statusReq) errors.status = statusReq

  return errors
}
