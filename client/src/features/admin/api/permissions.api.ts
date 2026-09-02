import { apiClient } from '@/lib/http/client'
import type {
  Permission,
  PermissionListResponse,
  CreatePermissionPayload,
  UpdatePermissionPayload,
  MessageResponse,
} from '../types/admin.types'
import type { PaginationParams } from '@/types/common.types'

export interface PermissionFilterParams extends PaginationParams {
  search?: string
  category?: string
  permission_group_id?: number | string
  is_active?: boolean | string
  all?: boolean | string
}

export async function getPermissions(params: PermissionFilterParams = {}): Promise<PermissionListResponse> {
  const response = await apiClient.get<PermissionListResponse>('/api/v1/admin/permissions', { params })
  return response.data
}

export async function getAllPermissions(): Promise<Permission[]> {
  const response = await apiClient.get<{ data: Permission[] }>('/api/v1/admin/permissions', { params: { all: 'true' } })
  return response.data.data
}

export async function getPermissionById(id: number): Promise<Permission> {
  const response = await apiClient.get<{ data: Permission }>(`/api/v1/admin/permissions/${id}`)
  return response.data.data
}

export async function createPermission(payload: CreatePermissionPayload): Promise<Permission> {
  const response = await apiClient.post<{ data: Permission }>('/api/v1/admin/permissions', payload)
  return response.data.data
}

export async function updatePermission(id: number, payload: UpdatePermissionPayload): Promise<Permission> {
  const response = await apiClient.put<{ data: Permission }>(`/api/v1/admin/permissions/${id}`, payload)
  return response.data.data
}

export async function deletePermission(id: number): Promise<MessageResponse> {
  const response = await apiClient.delete<MessageResponse>(`/api/v1/admin/permissions/${id}`)
  return response.data
}

export async function togglePermissionActive(id: number): Promise<Permission> {
  const response = await apiClient.patch<{ data: Permission }>(`/api/v1/admin/permissions/${id}/toggle-active`)
  return response.data.data
}
