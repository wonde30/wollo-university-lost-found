/**
 * Admin types for administration functionality.
 * Based on Laravel backend admin controllers and resources.
 */

import type { User, UserRole } from '@/features/auth/types/auth.types'

// ==========================================
// Dashboard Statistics
// ==========================================

export interface DashboardStatistics {
  total_items: number
  total_lost_items: number
  total_found_items: number
  items_in_custody: number
  items_returned: number
  pending_claims: number
  approved_claims: number
  rejected_claims: number
  active_users: number
  total_categories: number
  total_locations: number
  recent_items: any[]
  recent_claims: any[]
  items_by_category: CategoryStats[]
  items_by_status: StatusStats[]
  claims_by_status: ClaimStatusStats[]
  monthly_trend: MonthlyTrendStats[]
}

export interface CategoryStats {
  category_id: number
  category_name: string
  count: number
}

export interface StatusStats {
  status: string
  count: number
}

export interface ClaimStatusStats {
  status: string
  count: number
}

export interface MonthlyTrendStats {
  month: string
  lost_count: number
  found_count: number
  returned_count: number
}

// ==========================================
// Campus Management
// ==========================================

export interface CreateCampusData {
  name: string
  code: string
  address?: string
  description?: string
  is_active?: boolean
}

export interface UpdateCampusData {
  name?: string
  code?: string
  address?: string
  description?: string
  is_active?: boolean
}

// ==========================================
// Department Management
// ==========================================

export interface CreateDepartmentData {
  campus_id: number
  name: string
  code: string
  description?: string
  is_active?: boolean
}

export interface UpdateDepartmentData {
  campus_id?: number
  name?: string
  code?: string
  description?: string
  is_active?: boolean
}

// ==========================================
// Category Management
// ==========================================

export interface CreateCategoryData {
  name: string
  name_am?: string
  icon_slug?: string
  sort_order?: number
  is_active?: boolean
}

export interface UpdateCategoryData {
  name?: string
  name_am?: string
  icon_slug?: string
  sort_order?: number
  is_active?: boolean
}

// ==========================================
// Location Management
// ==========================================

export interface CreateLocationData {
  campus_id: number
  name: string
  code: string
  building?: string
  floor?: string
  room_number?: string
  coordinates?: string
  is_active?: boolean
}

export interface UpdateLocationData {
  campus_id?: number
  name?: string
  code?: string
  building?: string
  floor?: string
  room_number?: string
  coordinates?: string
  is_active?: boolean
}

// ==========================================
// User Management
// ==========================================

export interface UserManagement {
  id: number
  full_name: string
  name: string
  university_id: string
  email: string
  phone: string | null
  role: UserRole
  language: string
  is_active: boolean
  profile_photo: string | null
  profile_photo_url?: string | null
  created_at?: string
  updated_at?: string
  items_count?: number
  claims_count?: number
  last_login_at?: string
}

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

// ==========================================
// Announcements
// ==========================================

export type AnnouncementPriority = 'low' | 'normal' | 'high' | 'urgent'
export type AnnouncementStatus = 'draft' | 'scheduled' | 'published' | 'expired'

export interface Announcement {
  id: number
  title: string
  content: string
  priority: AnnouncementPriority
  status: AnnouncementStatus
  published_at: string | null
  expires_at: string | null
  target_roles: UserRole[]
  created_by: number
  author?: User
  created_at: string
  updated_at: string
}

export interface CreateAnnouncementData {
  title: string
  content: string
  priority: AnnouncementPriority
  status: AnnouncementStatus
  published_at?: string
  expires_at?: string
  target_roles?: UserRole[]
}

// ==========================================
// System Settings
// ==========================================

export interface SystemSetting {
  key: string
  value: any
  type: 'string' | 'number' | 'boolean' | 'json'
  group: string
  description: string | null
  is_public: boolean
  updated_at: string
}

export interface UpdateSettingData {
  value: any
}

// ==========================================
// Reports
// ==========================================

export type ReportType = 
  | 'items_summary'
  | 'claims_summary'
  | 'user_activity'
  | 'category_performance'
  | 'location_performance'
  | 'custody_audit'
  | 'return_log'

export type ReportFormat = 'pdf' | 'excel' | 'csv'

export interface Report {
  id: number
  type: ReportType
  title: string
  description: string | null
  filters: Record<string, any>
  generated_by: number
  file_path: string | null
  file_url: string | null
  status: 'pending' | 'processing' | 'completed' | 'failed'
  error_message: string | null
  generated_at: string | null
  created_at: string
  updated_at: string
}

export interface GenerateReportData {
  type: ReportType
  format?: ReportFormat
  date_from?: string
  date_to?: string
  filters?: Record<string, any>
}

// ==========================================
// Audit Logs
// ==========================================

export type AuditAction = 
  | 'create'
  | 'update'
  | 'delete'
  | 'login'
  | 'logout'
  | 'view'
  | 'export'

export interface AuditLog {
  id: number
  user_id: number | null
  action: AuditAction
  auditable_type: string
  auditable_id: number | null
  old_values: Record<string, any> | null
  new_values: Record<string, any> | null
  ip_address: string | null
  user_agent: string | null
  tags: string[] | null
  user?: User
  created_at: string
}

export interface AuditLogFilters {
  user_id?: number
  action?: AuditAction
  auditable_type?: string
  date_from?: string
  date_to?: string
  search?: string
  sort_by?: string
  sort_order?: 'asc' | 'desc'
}
