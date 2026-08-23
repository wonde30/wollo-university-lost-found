/**
 * Admin feature types based on Laravel backend contract.
 * Admin-only functionality.
 */

import type { User, UserRole } from '@/features/auth/types/auth.types'
import type { Campus, Category, Location, Department, StorageLocation } from '@/types/common.types'
import type { PaginatedResponse, PaginationParams } from '@/types/common.types'

// ==========================================
// Dashboard Statistics
// ==========================================

export interface DashboardStatistics {
  summary: {
    total_items: number
    lost_items: number
    found_items: number
    in_storage: number
    returned_items: number
    pending_claims: number
    total_users: number
    recovery_rate_percentage: number
  }
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
}

export interface RecentReturn {
  id: number
  claim_id: number | null
  item_id: number
  returned_to: number
  handed_over_by: number
  return_date: string
  created_at: string
}

// ==========================================
// User Management
// ==========================================

export interface UpdateUserData {
  full_name?: string
  email?: string
  phone?: string
  university_id?: string
  is_active?: boolean
}

export interface UpdateUserRoleData {
  role: UserRole
}

export interface UserListParams extends PaginationParams {
  role?: UserRole
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
  group: string
  description: string | null
  is_public: boolean
  created_at: string
  updated_at: string
}

export interface UpdateSystemSettingData {
  value: string | number | boolean
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
  created_at: string
  updated_at: string
}

export interface StoreAnnouncementData {
  title: string
  body: string
  type: 'info' | 'warning' | 'urgent' | 'maintenance'
  audience: 'all' | 'students' | 'staff' | 'admins'
  is_active?: boolean
  starts_at?: string
  ends_at?: string
}

export interface AnnouncementListParams extends PaginationParams {
  is_active?: boolean
  type?: string
  audience?: string
}

// ==========================================
// Audit Logs
// ==========================================

export interface AuditLog {
  id: number
  actor_id: number
  actor_role: string
  action: string
  auditable_type: string
  auditable_id: number | null
  old_values: Record<string, unknown> | null
  new_values: Record<string, unknown> | null
  ip_address: string | null
  user_agent: string | null
  actor?: User
  user?: User
  created_at: string
}

export interface AuditLogListParams extends PaginationParams {
  actor_id?: number
  action?: string
  auditable_type?: string
  start_date?: string
  end_date?: string
}

// ==========================================
// Reports
// ==========================================

export interface Report {
  id: number
  requested_by: number
  report_type: string
  filters: Record<string, unknown> | null
  format: 'csv' | 'pdf' | 'excel'
  status: 'pending' | 'processing' | 'completed' | 'failed'
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
  created_at: string
  updated_at: string
}

export interface GenerateReportData {
  report_type: 'items' | 'claims' | 'returns' | 'users' | 'audit_logs'
  format: 'csv' | 'pdf' | 'excel'
  filters?: Record<string, unknown>
  start_date?: string
  end_date?: string
}

export interface ReportListParams extends PaginationParams {
  report_type?: string
  status?: string
}

// ==========================================
// Response Types
// ==========================================

export type UserListResponse = PaginatedResponse<User>
export type CampusListResponse = ResourceResponse<Campus[]>
export type DepartmentListResponse = ResourceResponse<Department[]>
export type CategoryListResponse = ResourceResponse<Category[]>
export type LocationListResponse = ResourceResponse<Location[]>
export type StorageLocationListResponse = ResourceResponse<StorageLocation[]>
export type AnnouncementListResponse = PaginatedResponse<Announcement>
export type AuditLogListResponse = PaginatedResponse<AuditLog>
export type ReportListResponse = PaginatedResponse<Report>

export interface ResourceResponse<T> {
  data: T
}

export interface MessageResponse {
  message: string
}
