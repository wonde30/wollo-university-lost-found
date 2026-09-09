/**
 * Admin API client for administrative functions.
 * Admin-only endpoints.
 */

import { apiClient } from '@/lib/http/client'
import { ADMIN, PUBLIC } from '@/lib/api/endpoints'
import { buildPaginationQuery } from '@/lib/api/pagination'
import type { PaginationParams } from '@/lib/api/pagination'
import type { User } from '@/features/auth/types/auth.types'
import type {
  Campus,
  Category,
  Location,
  OrganizationalUnit,
  OrganizationalUnitType,
  StorageLocation,
} from '@/types/common.types'
import type {
  DashboardStatistics,
  UserListParams,
  UserListResponse,
  StoreUserData,
  UpdateUserData,
  UpdateUserRoleData,
  SystemSetting,
  UpdateSystemSettingData,
  Announcement,
  StoreAnnouncementData,
  UpdateAnnouncementData,
  AnnouncementListParams,
  AnnouncementListResponse,
  AuditLogListParams,
  AuditLogListResponse,
  Report,
  GenerateReportData,
  ReportListParams,
  ReportListResponse,
  CampusListResponse,
  OrganizationalUnitListResponse,
  OrganizationalUnitTypeListResponse,
  CategoryListResponse,
  LocationListResponse,
  StorageLocationListResponse,
  ResourceResponse,
} from '../types/admin.types'

// ==========================================
// Dashboard
// ==========================================

/**
 * Get dashboard statistics, sparklines, and multi-dimensional analytics.
 */
export async function getDashboardStatistics(params?: {
  period?: string
  date_from?: string
  date_to?: string
  force?: boolean
}): Promise<DashboardStatistics> {
  const query = params ? buildPaginationQuery(params as any) : ''
  const { data } = await apiClient.get<DashboardStatistics>(ADMIN.DASHBOARD_STATISTICS + query)
  return data
}

// ==========================================
// User Management
// ==========================================

/**
 * Get paginated list of users.
 */
export async function getUsers(params?: UserListParams): Promise<UserListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<UserListResponse>(ADMIN.USERS + query)
  return data
}

/**
 * Get single user details.
 */
export async function getUser(id: number): Promise<User> {
  const { data } = await apiClient.get<ResourceResponse<User>>(ADMIN.USER(id))
  return data.data
}

/**
 * Update user details.
 */
export async function updateUser(id: number, userData: UpdateUserData): Promise<User> {
  const { data } = await apiClient.put<ResourceResponse<User>>(ADMIN.USER(id), userData)
  return data.data
}

/**
 * Update user role.
 */
export async function updateUserRole(id: number, roleData: UpdateUserRoleData): Promise<User> {
  const { data } = await apiClient.patch<ResourceResponse<User>>(ADMIN.USER_UPDATE_ROLE(id), roleData)
  return data.data
}

/**
 * Toggle user active status.
 */
export async function toggleUserActive(id: number): Promise<User> {
  const { data } = await apiClient.patch<ResourceResponse<User>>(ADMIN.USER_TOGGLE_ACTIVE(id))
  return data.data
}

/**
 * Create new user account.
 */
export async function createUser(userData: StoreUserData): Promise<User> {
  const { data } = await apiClient.post<ResourceResponse<User>>(ADMIN.USERS, userData)
  return data.data
}

export interface UserPermissionsPayload {
  user_id: number
  role: string
  role_permissions: string[]
  direct_permissions: string[]
  direct_permission_ids: number[]
  effective_permissions: string[]
  available_permissions: Array<{
    id: number
    name: string
    display_name: string
    display_name_am?: string
    description?: string
    category: string
    is_active: boolean
  }>
}

/**
 * Get user permission details (role inherited + direct).
 */
export async function getUserPermissions(userId: number): Promise<UserPermissionsPayload> {
  const { data } = await apiClient.get<{ data: UserPermissionsPayload }>(ADMIN.USER_PERMISSIONS(userId))
  return data.data
}

/**
 * Sync custom direct permissions for a user.
 */
export async function syncUserPermissions(
  userId: number,
  permissionIds: number[]
): Promise<{ user: User; direct_permissions: string[]; effective_permissions: string[] }> {
  const { data } = await apiClient.post<{
    message: string
    data: { user: User; direct_permissions: string[]; effective_permissions: string[] }
  }>(ADMIN.USER_PERMISSIONS(userId), { permission_ids: permissionIds })
  return data.data
}

// ==========================================
// Campuses (apiResource)
// ==========================================

export async function getCampuses(params?: PaginationParams & { all?: boolean; search?: string; is_active?: boolean }): Promise<CampusListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<CampusListResponse>(ADMIN.CAMPUSES + query)
  return data
}

export async function getCampus(id: number): Promise<Campus> {
  const { data } = await apiClient.get<ResourceResponse<Campus>>(ADMIN.CAMPUS(id))
  return data.data
}

export async function createCampus(campusData: Partial<Campus>): Promise<Campus> {
  const { data } = await apiClient.post<ResourceResponse<Campus>>(ADMIN.CAMPUSES, campusData)
  return data.data
}

export async function updateCampus(id: number, campusData: Partial<Campus>): Promise<Campus> {
  const { data } = await apiClient.put<ResourceResponse<Campus>>(ADMIN.CAMPUS(id), campusData)
  return data.data
}

export async function deleteCampus(id: number): Promise<void> {
  await apiClient.delete(ADMIN.CAMPUS(id))
}

export async function restoreCampus(id: number): Promise<Campus> {
  const { data } = await apiClient.patch<ResourceResponse<Campus>>(ADMIN.CAMPUS_RESTORE(id))
  return data.data
}

// ==========================================
// Organizational Units (apiResource)
// ==========================================

export async function getOrganizationalUnits(
  params?: PaginationParams & { all?: boolean; campus_id?: number; type_id?: number; parent_id?: number; search?: string; is_active?: boolean }
): Promise<OrganizationalUnitListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<OrganizationalUnitListResponse>(ADMIN.ORGANIZATIONAL_UNITS + query)
  return data
}

export async function getOrganizationalUnit(id: number): Promise<OrganizationalUnit> {
  const { data } = await apiClient.get<ResourceResponse<OrganizationalUnit>>(ADMIN.ORGANIZATIONAL_UNIT(id))
  return data.data
}

export async function createOrganizationalUnit(unitData: Partial<OrganizationalUnit>): Promise<OrganizationalUnit> {
  const { data } = await apiClient.post<ResourceResponse<OrganizationalUnit>>(ADMIN.ORGANIZATIONAL_UNITS, unitData)
  return data.data
}

export async function updateOrganizationalUnit(id: number, unitData: Partial<OrganizationalUnit>): Promise<OrganizationalUnit> {
  const { data } = await apiClient.put<ResourceResponse<OrganizationalUnit>>(ADMIN.ORGANIZATIONAL_UNIT(id), unitData)
  return data.data
}

export async function deleteOrganizationalUnit(id: number): Promise<void> {
  await apiClient.delete(ADMIN.ORGANIZATIONAL_UNIT(id))
}

// ==========================================
// Organizational Unit Types (apiResource)
// ==========================================

export async function getOrganizationalUnitTypes(
  params?: PaginationParams & { all?: boolean; search?: string; is_root?: boolean; is_active?: boolean }
): Promise<OrganizationalUnitTypeListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<OrganizationalUnitTypeListResponse>(ADMIN.ORGANIZATIONAL_UNIT_TYPES + query)
  return data
}

export async function getOrganizationalUnitType(id: number): Promise<OrganizationalUnitType> {
  const { data } = await apiClient.get<ResourceResponse<OrganizationalUnitType>>(ADMIN.ORGANIZATIONAL_UNIT_TYPE(id))
  return data.data
}

export async function createOrganizationalUnitType(typeData: Partial<OrganizationalUnitType>): Promise<OrganizationalUnitType> {
  const { data } = await apiClient.post<ResourceResponse<OrganizationalUnitType>>(ADMIN.ORGANIZATIONAL_UNIT_TYPES, typeData)
  return data.data
}

export async function updateOrganizationalUnitType(id: number, typeData: Partial<OrganizationalUnitType>): Promise<OrganizationalUnitType> {
  const { data } = await apiClient.put<ResourceResponse<OrganizationalUnitType>>(ADMIN.ORGANIZATIONAL_UNIT_TYPE(id), typeData)
  return data.data
}

export async function deleteOrganizationalUnitType(id: number): Promise<void> {
  await apiClient.delete(ADMIN.ORGANIZATIONAL_UNIT_TYPE(id))
}

// ==========================================
// Categories (apiResource)
// ==========================================

export async function getCategories(params?: PaginationParams & { all?: boolean; search?: string; is_active?: boolean }): Promise<CategoryListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<CategoryListResponse>(ADMIN.CATEGORIES + query)
  return data
}

export async function getCategory(id: number): Promise<Category> {
  const { data } = await apiClient.get<ResourceResponse<Category>>(ADMIN.CATEGORY(id))
  return data.data
}

export async function createCategory(categoryData: Partial<Category>): Promise<Category> {
  const { data } = await apiClient.post<ResourceResponse<Category>>(ADMIN.CATEGORIES, categoryData)
  return data.data
}

export async function updateCategory(id: number, categoryData: Partial<Category>): Promise<Category> {
  const { data } = await apiClient.put<ResourceResponse<Category>>(ADMIN.CATEGORY(id), categoryData)
  return data.data
}

export async function deleteCategory(id: number): Promise<void> {
  await apiClient.delete(ADMIN.CATEGORY(id))
}

// ==========================================
// Locations (apiResource)
// ==========================================

export async function getLocations(params?: PaginationParams & { all?: boolean; campus_id?: number; search?: string; is_active?: boolean }): Promise<LocationListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<LocationListResponse>(ADMIN.LOCATIONS + query)
  return data
}

export async function getLocation(id: number): Promise<Location> {
  const { data } = await apiClient.get<ResourceResponse<Location>>(ADMIN.LOCATION(id))
  return data.data
}

export async function createLocation(locationData: Partial<Location>): Promise<Location> {
  const { data } = await apiClient.post<ResourceResponse<Location>>(ADMIN.LOCATIONS, locationData)
  return data.data
}

export async function updateLocation(id: number, locationData: Partial<Location>): Promise<Location> {
  const { data } = await apiClient.put<ResourceResponse<Location>>(ADMIN.LOCATION(id), locationData)
  return data.data
}

export async function deleteLocation(id: number): Promise<void> {
  await apiClient.delete(ADMIN.LOCATION(id))
}

// ==========================================
// Storage Locations (apiResource)
// ==========================================

export async function getAdminStorageLocations(params?: PaginationParams & { all?: boolean; campus_id?: number; status?: string; search?: string }): Promise<StorageLocationListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<StorageLocationListResponse>(ADMIN.STORAGE_LOCATIONS + query)
  return data
}

export async function getAdminStorageLocation(id: number): Promise<StorageLocation> {
  const { data } = await apiClient.get<ResourceResponse<StorageLocation>>(ADMIN.STORAGE_LOCATION(id))
  return data.data
}

export async function createAdminStorageLocation(locationData: Partial<StorageLocation>): Promise<StorageLocation> {
  const { data } = await apiClient.post<ResourceResponse<StorageLocation>>(ADMIN.STORAGE_LOCATIONS, locationData)
  return data.data
}

export async function updateAdminStorageLocation(id: number, locationData: Partial<StorageLocation>): Promise<StorageLocation> {
  const { data } = await apiClient.put<ResourceResponse<StorageLocation>>(ADMIN.STORAGE_LOCATION(id), locationData)
  return data.data
}

export async function deleteAdminStorageLocation(id: number): Promise<void> {
  await apiClient.delete(ADMIN.STORAGE_LOCATION(id))
}

// ==========================================
// Announcements
// ==========================================

export async function getAnnouncements(params?: AnnouncementListParams): Promise<AnnouncementListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<AnnouncementListResponse>(ADMIN.ANNOUNCEMENTS + query)
  return data
}

export async function getAnnouncement(id: number): Promise<Announcement> {
  const { data } = await apiClient.get<ResourceResponse<Announcement>>(ADMIN.ANNOUNCEMENT(id))
  return data.data
}

export async function createAnnouncement(announcementData: StoreAnnouncementData): Promise<Announcement> {
  const { data } = await apiClient.post<ResourceResponse<Announcement>>(ADMIN.ANNOUNCEMENTS, announcementData)
  return data.data
}

export async function updateAnnouncement(id: number, announcementData: UpdateAnnouncementData): Promise<Announcement> {
  const { data } = await apiClient.put<ResourceResponse<Announcement>>(ADMIN.ANNOUNCEMENT(id), announcementData)
  return data.data
}

export async function toggleAnnouncementActive(id: number): Promise<Announcement> {
  const { data } = await apiClient.patch<ResourceResponse<Announcement>>(ADMIN.ANNOUNCEMENT_TOGGLE_ACTIVE(id))
  return data.data
}

export async function deleteAnnouncement(id: number): Promise<void> {
  await apiClient.delete(ADMIN.ANNOUNCEMENT(id))
}

export async function bulkToggleAnnouncements(ids: number[], isActive: boolean): Promise<void> {
  await apiClient.post(ADMIN.ANNOUNCEMENTS_BULK_TOGGLE, { ids, is_active: isActive })
}

export async function bulkDeleteAnnouncements(ids: number[]): Promise<void> {
  await apiClient.post(ADMIN.ANNOUNCEMENTS_BULK_DELETE, { ids })
}

export async function getActiveAnnouncements(): Promise<Announcement[]> {
  try {
    const { data } = await apiClient.get<ResourceResponse<Announcement[]>>(ADMIN.ANNOUNCEMENTS_ACTIVE)
    return data.data
  } catch {
    const { data } = await apiClient.get<ResourceResponse<Announcement[]>>(PUBLIC.ANNOUNCEMENTS)
    return data.data
  }
}

// ==========================================
// System Settings
// ==========================================

export async function getSystemSettings(params?: { all?: boolean; search?: string; is_public?: boolean }): Promise<SystemSetting[]> {
  const query = params ? buildPaginationQuery({ all: 'true', ...params }) : '?all=true'
  const { data } = await apiClient.get<ResourceResponse<SystemSetting[]>>(ADMIN.SETTINGS + query)
  return data.data
}

export async function updateSystemSetting(key: string, settingData: UpdateSystemSettingData): Promise<SystemSetting> {
  const { data } = await apiClient.put<ResourceResponse<SystemSetting>>(ADMIN.SETTING(key), settingData)
  return data.data
}

// ==========================================
// Reports
// ==========================================

export async function getReports(params?: ReportListParams): Promise<ReportListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<ReportListResponse>(ADMIN.REPORTS + query)
  return data
}

export async function generateReport(reportData: GenerateReportData): Promise<Report> {
  const { data } = await apiClient.post<ResourceResponse<Report>>(ADMIN.REPORTS_GENERATE, reportData)
  return data.data
}

// ==========================================
// Audit Logs
// ==========================================

export async function getAuditLogs(params?: AuditLogListParams): Promise<AuditLogListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<AuditLogListResponse>(ADMIN.AUDIT_LOGS + query)
  return data
}

/**
 * Export audit logs as CSV.
 * Returns blob for download.
 */
export async function exportAuditLogs(params?: AuditLogListParams): Promise<Blob> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get(ADMIN.AUDIT_LOGS_EXPORT + query, {
    responseType: 'blob',
  })
  return data
}
