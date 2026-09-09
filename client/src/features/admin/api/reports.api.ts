import { apiClient } from '@/lib/http/client'
import { ADMIN } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'
import type { ApiResponse } from '@/lib/api/response'

export interface GeneratedReport {
  id: number
  requested_by: number
  report_type: string
  format: 'csv' | 'pdf'
  status: 'pending' | 'processing' | 'generating' | 'ready' | 'failed'
  file_path: string | null
  file_size_bytes?: number | null
  row_count?: number
  error_message?: string | null
  ready_at: string | null
  expires_at: string | null
  created_at: string
  requester?: {
    id: number
    full_name: string
    email: string
  } | null
  requested_by_user?: {
    id: number
    full_name: string
    email: string
  } | null
  generated_by?: {
    id: number
    full_name: string
    email: string
  } | null
}

export interface GenerateReportPayload {
  report_type: string
  format?: 'csv' | 'pdf'
  filters?: {
    campus_id?: number
    category_id?: number
    status?: string
    date_from?: string
    date_to?: string
  }
}

export async function getReports(
  pagination?: PaginationParams
): Promise<PaginatedResponse<GeneratedReport>> {
  const { data } = await apiClient.get<PaginatedResponse<GeneratedReport>>(
    ADMIN.REPORTS,
    { params: pagination }
  )
  return data
}

export async function generateReport(
  payload: GenerateReportPayload
): Promise<GeneratedReport> {
  const { data } = await apiClient.post<ApiResponse<GeneratedReport>>(
    ADMIN.REPORTS_GENERATE,
    payload
  )
  return data.data!
}

export async function downloadReport(id: number, filename = 'report.csv'): Promise<void> {
  const response = await apiClient.get(ADMIN.REPORTS_DOWNLOAD(id), {
    responseType: 'blob',
  })

  // Detect if server returned JSON error disguised inside a blob
  const rawContentType = response.headers['content-type']
  const contentType = (typeof rawContentType === 'string' ? rawContentType : '').toLowerCase()
  if (contentType.includes('application/json')) {
    const text = await (response.data as Blob).text()
    try {
      const errorObj = JSON.parse(text)
      throw new Error(errorObj.message || 'Failed to download report.')
    } catch (e: any) {
      if (e instanceof Error && e.message !== 'Failed to download report.') {
        throw e
      }
      throw new Error('Failed to download report.')
    }
  }

  const mimeType = filename.endsWith('.pdf')
    ? 'application/pdf'
    : filename.endsWith('.csv')
      ? 'text/csv'
      : (contentType || 'application/octet-stream')

  const blob = response.data instanceof Blob
    ? new Blob([response.data], { type: mimeType })
    : new Blob([response.data], { type: mimeType })

  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}
