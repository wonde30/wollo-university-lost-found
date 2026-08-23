/**
 * Admin API client for administrative functions.
 * Admin-only endpoints.
 */

import { apiClient } from '@/lib/http/client'
import { ADMIN } from '@/lib/api/endpoints'
import { buildPaginationQuery } from '@/lib/api/pagination'
import type { PaginationParams } from '@/lib/api/pagination'
import type { User } from '@/features/auth/types/auth.types'
import type { Campus, Category, Location, Department, StorageLocation } from '@/types/common.types'
import type {
  DashboardStatistics,
  UserListParams,
  UserListResponse,
  UpdateUserData,
  UpdateUserRoleData,
  SystemSetting,
  UpdateSystemSettingData,
  Announcement,
  StoreAnnouncementData,
  AnnouncementListParams,
  AnnouncementListResponse,
  AuditLogListParams,
  AuditLogListResponse,
  Report,
  GenerateReportData,
  ReportListParams,
  ReportListResponse,
  CampusListResponse,
  DepartmentListResponse,
  CategoryListResponse,
  LocationListResponse,
  StorageLocationListResponse,
  ResourceResponse,
} from '../types/admin.types'

// ==========================================
// Dashboard
// ==========================================

/**
 * Get dashboard statistics and recent activity.
 */
export async function getDashboardStatistics(): Promise<DashboardStatistics> {
  const { data } = await apiClient.get<DashboardStatistics>(ADMIN.DASHBOARD_STATISTICS)
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

// ==========================================
// Campuses (apiResource)
// ==========================================

export async function getCampuses(params?: PaginationParams): Promise<CampusListResponse> {
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
  const { data } = await apiClient.patch<ResourceResponse<Campus>>(`${ADMIN.CAMPUSES}/${id}/restore`)
  return data.data
}

// ==========================================
// Departments (apiResource)
// ==========================================

export async function getDepartments(params?: PaginationParams): Promise<DepartmentListResponse> {
  const query = params ? buildPaginationQuery(params) : ''
  const { data } = await apiClient.get<DepartmentListResponse>(ADMIN.DEPARTMENTS + query)
  return data
}

export async function getDepartment(id: number): Promise<Department> {
  const { data } = await apiClient.get<ResourceResponse<Department>>(ADMIN.DEPARTMENT(id))
  return data.data
}

export async function createDepartment(deptData: Partial<Department>): Promise<Department> {
  const { data } = await apiClient.post<ResourceResponse<Department>>(ADMIN.DEPARTMENTS, deptData)
  return data.data
}

export async function updateDepartment(id: number, deptData: Partial<Department>): Promise<Department> {
  const { data } = await apiClient.put<ResourceResponse<Department>>(ADMIN.DEPARTMENT(id), deptData)
  return data.data
}

export async function deleteDepartment(id: number): Promise<void> {
  await apiClient.delete(ADMIN.DEPARTMENT(id))
}

// ==========================================
// Categories (apiResource)
// ==========================================

export async function getCategories(params?: PaginationParams): Promise<CategoryListResponse> {
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

export async function getLocations(params?: PaginationParams): Promise<LocationListResponse> {
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

export async function getAdminStorageLocations(params?: PaginationParams): Promise<StorageLocationListResponse> {
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
// Announcements (apiResource except update)
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

export async function deleteAnnouncement(id: number): Promise<void> {
  await apiClient.delete(ADMIN.ANNOUNCEMENT(id))
}

// ==========================================
// System Settings
// ==========================================

export async function getSystemSettings(): Promise<SystemSetting[]> {
  const { data } = await apiClient.get<ResourceResponse<SystemSetting[]>>(ADMIN.SETTINGS)
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
