import { apiClient } from '@/lib/http/client'
import { ADMIN } from '@/lib/api/endpoints'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'
import type { ApiResponse } from '@/lib/api/response'

export interface GeneratedReport {
  id: number
  requested_by: number
  report_type: string
  format: 'csv' | 'pdf'
  status: 'pending' | 'generating' | 'ready' | 'failed'
  file_path: string | null
  ready_at: string | null
  expires_at: string | null
  created_at: string
  requester?: {
    id: number
    full_name: string
    email: string
  }
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

  const blob = new Blob([response.data], { type: 'application/octet-stream' })
  const url = window.URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.setAttribute('download', filename)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
}
