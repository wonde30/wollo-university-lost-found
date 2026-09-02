import { apiClient } from '@/lib/http/client'
import type { Role, RoleListResponse, CreateRolePayload, UpdateRolePayload, MessageResponse } from '../types/admin.types'
import type { PaginationParams } from '@/types/common.types'

export interface RoleFilterParams extends PaginationParams {
  search?: string
  is_active?: boolean | string
  is_system?: boolean | string
  all?: boolean | string
}

export async function getRoles(params: RoleFilterParams = {}): Promise<RoleListResponse> {
  const response = await apiClient.get<RoleListResponse>('/api/v1/admin/roles', { params })
  return response.data
}

export async function getAllActiveRoles(): Promise<Role[]> {
  const response = await apiClient.get<{ data: Role[] }>('/api/v1/admin/roles', { params: { all: 'true' } })
  return response.data.data
}

export async function getRoleById(id: number): Promise<Role> {
  const response = await apiClient.get<{ data: Role }>(`/api/v1/admin/roles/${id}`)
  return response.data.data
}

export async function createRole(payload: CreateRolePayload): Promise<Role> {
  const response = await apiClient.post<{ data: Role }>('/api/v1/admin/roles', payload)
  return response.data.data
}

export async function updateRole(id: number, payload: UpdateRolePayload): Promise<Role> {
  const response = await apiClient.put<{ data: Role }>(`/api/v1/admin/roles/${id}`, payload)
  return response.data.data
}

export async function deleteRole(id: number): Promise<MessageResponse> {
  const response = await apiClient.delete<MessageResponse>(`/api/v1/admin/roles/${id}`)
  return response.data
}

export async function syncRolePermissions(id: number, permissionIds: number[]): Promise<Role> {
  const response = await apiClient.post<{ data: Role }>(`/api/v1/admin/roles/${id}/permissions`, {
    permission_ids: permissionIds,
  })
  return response.data.data
}

export async function toggleRoleActive(id: number, currentActive: boolean): Promise<Role> {
  return updateRole(id, { is_active: !currentActive })
}

