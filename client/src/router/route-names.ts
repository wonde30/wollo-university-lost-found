/**
 * Route name constants.
 * Centralized route names for type-safe navigation.
 */

export const ROUTE_NAMES = {
  // Public
  HOME: 'home',
  BROWSE: 'browse',
  ITEM_DETAIL: 'item-detail',
  TRACK_ITEM: 'track-item',
  CONFIRM_RETURN: 'confirm-return',

  // Auth
  LOGIN: 'login',
  REGISTER: 'register',
  FORGOT_PASSWORD: 'forgot-password',
  RESET_PASSWORD: 'reset-password',
  VERIFY_OTP: 'verify-otp',

  // Student
  STUDENT_DASHBOARD: 'student-dashboard',
  STUDENT_MY_ITEMS: 'student-my-items',
  STUDENT_MY_CLAIMS: 'student-my-claims',
  STUDENT_REPORT_LOST: 'student-report-lost',
  STUDENT_REPORT_FOUND: 'student-report-found',
  STUDENT_SUBMIT_CLAIM: 'student-submit-claim',

  // Staff
  STAFF_DASHBOARD: 'staff-dashboard',
  STAFF_ITEMS: 'staff-items',
  STAFF_REVIEW_CLAIMS: 'staff-review-claims',
  STAFF_MANAGE_CUSTODY: 'staff-manage-custody',
  STAFF_PROCESS_RETURN: 'staff-process-return',
  STAFF_MATCH_SUGGESTIONS: 'staff-match-suggestions',

  // Admin
  ADMIN_DASHBOARD: 'admin-dashboard',
  ADMIN_ITEMS: 'admin-items',
  ADMIN_USERS: 'admin-users',
  ADMIN_USER_DETAIL: 'admin-user-detail',
  ADMIN_CAMPUSES: 'admin-campuses',
  ADMIN_ORGANIZATIONAL_UNITS: 'admin-organizational-units',
  ADMIN_CATEGORIES: 'admin-categories',

  ADMIN_LOCATIONS: 'admin-locations',
  ADMIN_STORAGE_LOCATIONS: 'admin-storage-locations',
  ADMIN_REPORTS: 'admin-reports',
  ADMIN_SETTINGS: 'admin-settings',
  ADMIN_AUDIT_LOGS: 'admin-audit-logs',
  ADMIN_ANNOUNCEMENTS: 'admin-announcements',
  ADMIN_ROLES: 'admin-roles',
  ADMIN_PERMISSIONS: 'admin-permissions',

  // Profile
  PROFILE: 'profile',

  // Errors
  UNAUTHORIZED: 'unauthorized',
  FORBIDDEN: 'forbidden',
  NOT_FOUND: 'not-found',
  SERVER_ERROR: 'server-error',
} as const

export type RouteName = typeof ROUTE_NAMES[keyof typeof ROUTE_NAMES]
