/**
 * Returns API client for managing physical item returns.
 * Staff and admin only - handles return processing and documentation.
 */

import { apiClient } from '@/lib/http/client'
import { RETURNS } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/lib/api/pagination'
import type { ApiResponse } from '@/lib/api/response'
import type {
  ReturnRecord,
  ReturnListParams,
  StoreReturnData,
} from '../types/return.types'

// ==========================================
// Returns Management (Staff/Admin Only)
// ==========================================

/**
 * Get paginated list of return records.
 * Staff/Admin only.
 */
export async function getReturns(
  filters?: ReturnListParams,
  pagination?: PaginationParams
): Promise<PaginatedResponse<ReturnRecord>> {
  const params = {
    ...filters,
    ...pagination,
  }

  const { data } = await apiClient.get<PaginatedResponse<ReturnRecord>>(RETURNS.INDEX, { params })
  return data
}

/**
 * Get single return record details.
 * Staff/Admin only.
 */
export async function getReturn(id: number): Promise<ReturnRecord> {
  const { data } = await apiClient.get<ApiResponse<ReturnRecord>>(RETURNS.SHOW(id))
  return data.data!
}

/**
 * Create a new return record.
 * Staff/Admin only.
 */
export async function createReturn(returnData: StoreReturnData): Promise<ReturnRecord> {
  const formData = new FormData()

  formData.append('item_id', String(returnData.item_id))
  formData.append('returned_to', String(returnData.returned_to))
  formData.append('return_date', returnData.return_date)

  if (returnData.claim_id != null) {
    formData.append('claim_id', String(returnData.claim_id))
  }
  if (returnData.storage_location_id != null) {
    formData.append('storage_location_id', String(returnData.storage_location_id))
  }
  if (returnData.return_time) {
    formData.append('return_time', returnData.return_time)
  }
  if (returnData.condition_on_return) {
    formData.append('condition_on_return', returnData.condition_on_return)
  }
  if (returnData.notes) {
    formData.append('notes', returnData.notes)
  }
  if (returnData.documents?.length) {
    returnData.documents.forEach(file => formData.append('documents[]', file))
  }

  const { data } = await apiClient.post<ApiResponse<ReturnRecord>>(RETURNS.CREATE, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  })
  return data.data!
}
