import { apiClient } from '@/lib/http/client'
import type {
  PermissionGroup,
  PermissionGroupListResponse,
  CreatePermissionGroupPayload,
  UpdatePermissionGroupPayload,
  MessageResponse,
} from '../types/admin.types'
import type { PaginationParams } from '@/types/common.types'

export interface PermissionGroupFilterParams extends PaginationParams {
  search?: string
  is_active?: boolean | string
  all?: boolean | string
}

export async function getPermissionGroups(params: PermissionGroupFilterParams = {}): Promise<PermissionGroupListResponse> {
  const response = await apiClient.get<PermissionGroupListResponse>('/api/v1/admin/permission-groups', { params })
  return response.data
}

export async function getAllActivePermissionGroups(): Promise<PermissionGroup[]> {
  const response = await apiClient.get<{ data: PermissionGroup[] }>('/api/v1/admin/permission-groups', { params: { all: 'true' } })
  return response.data.data
}

export async function getPermissionGroupById(id: number): Promise<PermissionGroup> {
  const response = await apiClient.get<{ data: PermissionGroup }>(`/api/v1/admin/permission-groups/${id}`)
  return response.data.data
}

export async function createPermissionGroup(payload: CreatePermissionGroupPayload): Promise<PermissionGroup> {
  const response = await apiClient.post<{ data: PermissionGroup }>('/api/v1/admin/permission-groups', payload)
  return response.data.data
}

export async function updatePermissionGroup(id: number, payload: UpdatePermissionGroupPayload): Promise<PermissionGroup> {
  const response = await apiClient.put<{ data: PermissionGroup }>(`/api/v1/admin/permission-groups/${id}`, payload)
  return response.data.data
}

export async function deletePermissionGroup(id: number): Promise<MessageResponse> {
  const response = await apiClient.delete<MessageResponse>(`/api/v1/admin/permission-groups/${id}`)
  return response.data
}

export async function togglePermissionGroupActive(id: number): Promise<PermissionGroup> {
  const response = await apiClient.patch<{ data: PermissionGroup }>(`/api/v1/admin/permission-groups/${id}/toggle-active`)
  return response.data.data
}
