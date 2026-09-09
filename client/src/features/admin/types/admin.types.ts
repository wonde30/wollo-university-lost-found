/**
 * Admin feature types based on Laravel backend contract.
 * Admin-only functionality.
 */

import type { User } from '@/features/auth/types/auth.types'
import type { Campus, Category, Location, OrganizationalUnit, OrganizationalUnitType, StorageLocation } from '@/types/common.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

// ==========================================
// Dashboard Statistics
// ==========================================

export interface DashboardCategoryStat {
  id: number
  name: string
  total: number
}

export interface DashboardSparklines {
  total_items: number[]
  lost_items: number[]
  found_items: number[]
  returned_items: number[]
  in_storage: number[]
}

export interface DashboardTimelineData {
  labels: string[]
  lost: number[]
  found: number[]
  returned: number[]
}

export interface DashboardCategoryBreakdown {
  id: number
  name: string
  icon?: string | null
  total_items: number
  found_items: number
  lost_items: number
  returned_items: number
  percentage: number
}

export interface DashboardCampusBreakdown {
  id: number
  name: string
  code: string
  total_items: number
  found_items: number
  lost_items: number
  returned_items: number
  recovery_rate: number
}

export interface DashboardStatusFunnelStage {
  stage: 'reported' | 'in_custody' | 'claimed' | 'returned' | string
  label: string
  count: number
  percentage: number
}

export interface DashboardLifecycleSegment {
  id: string
  label: string
  count: number
  color: string
}

export interface DashboardAnalytics {
  period: string
  date_from: string
  date_to: string
  timeline: DashboardTimelineData
  by_category: DashboardCategoryBreakdown[]
  by_campus: DashboardCampusBreakdown[]
  status_funnel: DashboardStatusFunnelStage[]
  lifecycle_distribution?: DashboardLifecycleSegment[]
}

export interface DashboardStatistics {
  summary: {
    total_items: number
    lost_items: number
    active_lost: number
    found_items: number
    found_unclaimed: number
    in_storage: number
    returned_items: number
    claimed_items?: number
    returned_this_month?: number
    pending_claims: number
    pending_matches: number
    expiring_items: number
    unconfirmed_returns: number
    total_users: number
    recovery_rate_percentage: number
    avg_resolution_days: number | null
    top_3_categories: DashboardCategoryStat[]
    search_fail_rate_percentage: number
  }
  sparklines?: DashboardSparklines
  analytics?: DashboardAnalytics
  recent_activity: {
    recent_items: RecentItem[]
    recent_claims: RecentClaim[]
    recent_returns: RecentReturn[]
  }
}

export interface RecentItem {
  id: number
  reference_code: string
  title: string
  type: string
  status: string
  created_at: string
}

export interface RecentClaim {
  id: number
  item_id: number
  claimant_id: number
  status: string
  created_at: string
  item?: {
    id: number
    title: string
    reference_code: string
  }
}

export interface RecentReturn {
  id: number
  claim_id: number | null
  item_id: number
  returned_to: number
  handed_over_by: number
  return_date: string
  created_at: string
  item?: {
    id: number
    title: string
    reference_code: string
  }
}

// ==========================================
// User Management
// ==========================================

export interface StoreUserData {
  full_name: string
  university_id: string
  email: string
  password: string
  role_id: number
  phone?: string | null
  is_active?: boolean
  organizational_unit_id?: number | null
}

export interface UpdateUserData {
  full_name?: string
  name?: string
  university_id?: string
  email?: string
  phone?: string | null
  role?: string
  language?: 'en' | 'am'
  is_active?: boolean
}

export interface UpdateUserRoleData {
  role?: string
  role_id?: number
}

export interface UserListParams extends PaginationParams {
  role?: string
  is_active?: boolean
  search?: string
}

// ==========================================
// System Settings
// ==========================================

export interface SystemSetting {
  id: number
  key: string
  value: string | number | boolean | null
  type: string
  display_name?: string | null
  description: string | null
  is_public: boolean
  is_editable?: boolean
  created_at?: string
  updated_at?: string
}


export interface UpdateSystemSettingData {
  value: string | number | boolean | null
}

// ==========================================
// Announcements
// ==========================================

export interface Announcement {
  id: number
  created_by: number
  title: string
  body: string
  type: string
  audience: string
  is_active: boolean
  starts_at: string | null
  ends_at: string | null
  creator?: User
  created_by_user?: User
  created_at?: string
  updated_at?: string
}

export interface StoreAnnouncementData {
  title: string
  body: string
  type: 'info' | 'warning' | 'urgent' | 'maintenance' | 'success' | string
  audience: 'all' | 'students' | 'staff' | 'admin' | string
  is_active?: boolean
  starts_at?: string | null
  ends_at?: string | null
}

export interface UpdateAnnouncementData {
  title?: string
  body?: string
  type?: 'info' | 'warning' | 'urgent' | 'maintenance' | 'success' | string
  audience?: 'all' | 'students' | 'staff' | 'admin' | string
  is_active?: boolean
  starts_at?: string | null
  ends_at?: string | null
}

export interface AnnouncementListParams extends PaginationParams {
  is_active?: boolean
  type?: string
  audience?: string
  target_role?: string
  search?: string
  all?: boolean
}

// ==========================================
// Audit Logs
// ==========================================

export interface AuditLog {
  id: number
  actor_id: number | null
  actor_role: string | null
  action: string
  auditable_type: string
  auditable_id: number | null
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  ip_address: string | null
  user_agent: string | null
  actor?: User
  user?: User
  created_at?: string
}

export interface AuditLogListParams extends PaginationParams {
  actor_id?: number
  user_id?: number
  action?: string
  actor_role?: string
  date_from?: string
  date_to?: string
  search?: string
}

// ==========================================
// Reports
// ==========================================

export interface Report {
  id: number
  requested_by: number
  report_type: string
  filters: Record<string, unknown> | null
  format: 'csv' | 'pdf' | string
  status: 'queued' | 'processing' | 'ready' | 'failed' | string
  file_url: string | null
  file_size_bytes: number | null
  row_count: number | null
  error_message: string | null
  ready_at: string | null
  downloaded_at: string | null
  download_count: number
  expires_at: string | null
  requested_by_user?: User
  generated_by?: User
  created_at?: string
  updated_at?: string
}

export interface GenerateReportData {
  report_type: 'items' | 'claims' | 'returns' | 'users' | 'audit_logs' | string
  format?: 'csv' | 'pdf'
  campus_id?: number
  category_id?: number
  status?: string
  date_from?: string
  date_to?: string
  [key: string]: unknown
}

export interface ReportListParams extends PaginationParams {
  report_type?: string
  format?: string
  status?: string
  search?: string
}

// ==========================================
// Roles & Permissions (Dynamic RBAC)
// ==========================================

export interface PermissionGroup {
  id: number
  name: string
  display_name: string
  display_name_am?: string | null
  description?: string | null
  description_am?: string | null
  is_system: boolean
  is_active: boolean
  permissions_count?: number
  permissions?: Permission[]
  active_permissions?: Permission[]
  created_at?: string
  updated_at?: string
}

export interface Permission {
  id: number
  permission_group_id?: number | null
  name: string
  key: string
  display_name: string
  label: string
  display_name_am?: string | null
  description?: string | null
  description_am?: string | null
  category: 'items' | 'claims' | 'custody' | 'admin' | string
  is_system: boolean
  is_active: boolean
  permission_group?: PermissionGroup | null
  created_at?: string
  updated_at?: string
}

export interface Role {
  id: number
  name: string
  slug: string
  display_name: string
  display_name_am?: string | null
  description?: string | null
  description_am?: string | null
  is_system: boolean
  is_active: boolean
  users_count?: number
  permissions?: Permission[]
  permission_ids?: number[]
  permission_keys?: string[]
  created_at?: string
  updated_at?: string
}

export interface CreatePermissionGroupPayload {
  name: string
  display_name: string
  display_name_am?: string
  description?: string
  description_am?: string
  is_active?: boolean
}

export interface UpdatePermissionGroupPayload {
  name?: string
  display_name?: string
  display_name_am?: string
  description?: string
  description_am?: string
  is_active?: boolean
}

export interface CreateRolePayload {
  name: string
  display_name: string
  display_name_am?: string
  description?: string
  description_am?: string
  is_active?: boolean
  permission_ids?: number[]
}

export interface UpdateRolePayload {
  name?: string
  display_name?: string
  display_name_am?: string
  description?: string
  description_am?: string
  is_active?: boolean
  permission_ids?: number[]
}

export interface CreatePermissionPayload {
  permission_group_id?: number | null
  name: string
  display_name: string
  display_name_am?: string
  description?: string
  description_am?: string
  category: 'items' | 'claims' | 'custody' | 'admin' | string
  is_active?: boolean
}

export interface UpdatePermissionPayload {
  permission_group_id?: number | null
  name?: string
  display_name?: string
  display_name_am?: string
  description?: string
  description_am?: string
  category?: 'items' | 'claims' | 'custody' | 'admin' | string
  is_active?: boolean
}

// ==========================================
// Response Types
// ==========================================

export type UserListResponse = PaginatedResponse<User>
export type RoleListResponse = PaginatedResponse<Role>
export type PermissionListResponse = PaginatedResponse<Permission>
export type PermissionGroupListResponse = PaginatedResponse<PermissionGroup>
export type CampusListResponse = PaginatedResponse<Campus> | ResourceResponse<Campus[]>
export type OrganizationalUnitListResponse = PaginatedResponse<OrganizationalUnit> | ResourceResponse<OrganizationalUnit[]>
export type OrganizationalUnitTypeListResponse = PaginatedResponse<OrganizationalUnitType> | ResourceResponse<OrganizationalUnitType[]>
export type CategoryListResponse = PaginatedResponse<Category> | ResourceResponse<Category[]>
export type LocationListResponse = PaginatedResponse<Location> | ResourceResponse<Location[]>
export type StorageLocationListResponse = PaginatedResponse<StorageLocation> | ResourceResponse<StorageLocation[]>
export type AnnouncementListResponse = PaginatedResponse<Announcement>
export type AuditLogListResponse = PaginatedResponse<AuditLog>
export type ReportListResponse = PaginatedResponse<Report>

export interface ResourceResponse<T> {
  data: T
}

export interface MessageResponse {
  message: string
}
