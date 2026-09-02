/**
 * API endpoint constants based on server/routes/api.php
 * 
 * Base: /api/v1
 */

const API_V1 = '/api/v1'

// ==================================================
// AUTH (Guest & Authenticated)
// ==================================================
export const AUTH = {
  REGISTER: `${API_V1}/auth/register`,
  LOGIN: `${API_V1}/auth/login`,
  LOGOUT: `${API_V1}/auth/logout`,
  ME: `${API_V1}/auth/me`,
  VERIFY_EMAIL: `${API_V1}/auth/verify-email`,
  RESEND_VERIFICATION: `${API_V1}/auth/resend-verification`,
  FORGOT_PASSWORD: `${API_V1}/auth/forgot-password`,
  VERIFY_PASSWORD_RESET: `${API_V1}/auth/verify-password-reset`,
  RESET_PASSWORD: `${API_V1}/auth/reset-password`,
  PASSWORD: `${API_V1}/auth/password`,
} as const

// ==================================================
// PUBLIC
// ==================================================
export const PUBLIC = {
  ITEMS: `${API_V1}/public/items`,
  ITEM_DETAIL: (id: number) => `${API_V1}/public/items/${id}`,
  CATEGORIES: `${API_V1}/public/categories`,
  LOCATIONS: `${API_V1}/public/locations`,
  ANNOUNCEMENTS: `${API_V1}/public/announcements`,
  TRACK: (referenceCode: string) => `${API_V1}/public/track/${referenceCode}`,
} as const

// ==================================================
// ITEMS (Authenticated)
// ==================================================
export const ITEMS = {
  INDEX: `${API_V1}/items`,
  CHECK_DUPLICATE: `${API_V1}/items/check-duplicate`,
  LOST: `${API_V1}/items/lost`,
  FOUND: `${API_V1}/items/found`,
  SHOW: (id: number) => `${API_V1}/items/${id}`,
  UPDATE: (id: number) => `${API_V1}/items/${id}`,
  DELETE: (id: number) => `${API_V1}/items/${id}`,
  UPDATE_STATUS: (id: number) => `${API_V1}/items/${id}/status`,
  WITHDRAW: (id: number) => `${API_V1}/items/${id}/withdraw`,
  ADD_PHOTOS: (id: number) => `${API_V1}/items/${id}/photos`,
  DELETE_PHOTO: (id: number, photoId: number) => `${API_V1}/items/${id}/photos/${photoId}`,
} as const

// ==================================================
// CLAIMS (Authenticated)
// ==================================================
export const CLAIMS = {
  INDEX: `${API_V1}/claims`,
  CREATE: `${API_V1}/claims`,
  SHOW: (id: number) => `${API_V1}/claims/${id}`,
  REVIEW: (id: number) => `${API_V1}/claims/${id}/review`,
  REVERSE: (id: number) => `${API_V1}/claims/${id}/reverse`,
} as const

// ==================================================
// NOTIFICATIONS (Authenticated)
// ==================================================
export const NOTIFICATIONS = {
  STREAM: `${API_V1}/notifications/stream`,
  INDEX: `${API_V1}/notifications`,
  MARK_AS_READ: (id: string | number) => `${API_V1}/notifications/${id}/read`,
  MARK_ALL_AS_READ: `${API_V1}/notifications/read-all`,
  PREFERENCES: `${API_V1}/notifications/preferences`,
  UPDATE_PREFERENCES: `${API_V1}/notifications/preferences`,
} as const

// ==================================================
// CUSTODY (role:staff,admin)
// ==================================================
export const CUSTODY = {
  INDEX: `${API_V1}/custody`,
  CREATE: `${API_V1}/custody`,
  MOVE_ITEM: (itemId: number) => `${API_V1}/custody/items/${itemId}/move`,
  STORAGE_LOCATIONS: `${API_V1}/custody/storage-locations`,
  STORAGE_LOCATION: (id: number) => `${API_V1}/custody/storage-locations/${id}`,
} as const

// ==================================================
// RETURNS (role:staff,admin & public confirmation)
// ==================================================
export const RETURNS = {
  INDEX: `${API_V1}/returns`,
  CREATE: `${API_V1}/returns`,
  SHOW: (id: number) => `${API_V1}/returns/${id}`,
  CONFIRM: (id: number) => `${API_V1}/returns/${id}/confirm`,
  CONFIRM_TOKEN: (token: string) => `${API_V1}/returns/confirm-token/${token}`,
  EXPORT_CSV: `${API_V1}/returns/export/csv`,
} as const

// ==================================================
// MATCH SUGGESTIONS (role:staff,admin)
// ==================================================
export const MATCH_SUGGESTIONS = {
  INDEX: `${API_V1}/match-suggestions`,
  UPDATE: (id: number) => `${API_V1}/match-suggestions/${id}`,
} as const

// ==================================================
// ADMIN (role:admin)
// ==================================================
export const ADMIN = {
  DASHBOARD_STATISTICS: `${API_V1}/admin/dashboard/statistics`,
  
  CAMPUSES: `${API_V1}/admin/campuses`,
  CAMPUS: (id: number) => `${API_V1}/admin/campuses/${id}`,
  CAMPUS_RESTORE: (id: number) => `${API_V1}/admin/campuses/${id}/restore`,
  
  ORGANIZATIONAL_UNITS: `${API_V1}/admin/organizational-units`,
  ORGANIZATIONAL_UNIT: (id: number) => `${API_V1}/admin/organizational-units/${id}`,
  
  ORGANIZATIONAL_UNIT_TYPES: `${API_V1}/admin/organizational-unit-types`,
  ORGANIZATIONAL_UNIT_TYPE: (id: number) => `${API_V1}/admin/organizational-unit-types/${id}`,
  
  CATEGORIES: `${API_V1}/admin/categories`,
  CATEGORY: (id: number) => `${API_V1}/admin/categories/${id}`,
  
  LOCATIONS: `${API_V1}/admin/locations`,
  LOCATION: (id: number) => `${API_V1}/admin/locations/${id}`,
  
  STORAGE_LOCATIONS: `${API_V1}/admin/storage-locations`,
  STORAGE_LOCATION: (id: number) => `${API_V1}/admin/storage-locations/${id}`,
  
  USERS: `${API_V1}/admin/users`,
  USER: (id: number) => `${API_V1}/admin/users/${id}`,
  USER_UPDATE_ROLE: (id: number) => `${API_V1}/admin/users/${id}/role`,
  USER_TOGGLE_ACTIVE: (id: number) => `${API_V1}/admin/users/${id}/toggle-active`,
  USER_PERMISSIONS: (id: number) => `${API_V1}/admin/users/${id}/permissions`,

  ROLES: `${API_V1}/admin/roles`,
  ROLE: (id: number) => `${API_V1}/admin/roles/${id}`,
  ROLE_PERMISSIONS: (id: number) => `${API_V1}/admin/roles/${id}/permissions`,
  PERMISSIONS: `${API_V1}/admin/permissions`,
  PERMISSION: (id: number) => `${API_V1}/admin/permissions/${id}`,
  PERMISSION_TOGGLE_ACTIVE: (id: number) => `${API_V1}/admin/permissions/${id}/toggle-active`,
  PERMISSION_GROUPS: `${API_V1}/admin/permission-groups`,
  PERMISSION_GROUP: (id: number) => `${API_V1}/admin/permission-groups/${id}`,
  PERMISSION_GROUP_TOGGLE_ACTIVE: (id: number) => `${API_V1}/admin/permission-groups/${id}/toggle-active`,
  
  ANNOUNCEMENTS: `${API_V1}/admin/announcements`,
  ANNOUNCEMENT: (id: number) => `${API_V1}/admin/announcements/${id}`,
  ANNOUNCEMENT_TOGGLE_ACTIVE: (id: number) => `${API_V1}/admin/announcements/${id}/toggle-active`,
  ANNOUNCEMENTS_BULK_TOGGLE: `${API_V1}/admin/announcements/bulk-toggle`,
  ANNOUNCEMENTS_BULK_DELETE: `${API_V1}/admin/announcements/bulk-delete`,
  ANNOUNCEMENTS_ACTIVE: `${API_V1}/announcements/active`,
  
  SETTINGS: `${API_V1}/admin/settings`,
  SETTING: (key: string) => `${API_V1}/admin/settings/${key}`,
  
  REPORTS: `${API_V1}/admin/reports`,
  REPORTS_GENERATE: `${API_V1}/admin/reports/generate`,
  REPORTS_DOWNLOAD: (id: number) => `${API_V1}/admin/reports/${id}/download`,
  
  AUDIT_LOGS: `${API_V1}/admin/audit-logs`,
  AUDIT_LOGS_EXPORT: `${API_V1}/admin/audit-logs/export`,
} as const

// ==========================================
// PROFILE (Authenticated)
// ==========================================
export const PROFILE = {
  UPDATE: `${API_V1}/profile`,
  UPLOAD_AVATAR: `${API_V1}/profile/avatar`,
} as const
