/**
 * Route path constants.
 * Centralized route paths for consistent navigation.
 */

export const ROUTE_PATHS = {
  // Public
  HOME: '/',
  BROWSE: '/browse',
  ITEM_DETAIL: '/items/:id',
  TRACK_ITEM: '/track/:reference?',

  // Auth
  LOGIN: '/auth/login',
  REGISTER: '/auth/register',
  FORGOT_PASSWORD: '/auth/forgot-password',
  RESET_PASSWORD: '/auth/reset-password',
  VERIFY_OTP: '/auth/verify-otp',

  // Student
  STUDENT_DASHBOARD: '/student/dashboard',
  STUDENT_MY_ITEMS: '/student/my-items',
  STUDENT_MY_CLAIMS: '/student/my-claims',
  STUDENT_REPORT_LOST: '/student/report-lost',
  STUDENT_REPORT_FOUND: '/student/report-found',
  STUDENT_SUBMIT_CLAIM: '/student/claim/:id',

  // Staff
  STAFF_DASHBOARD: '/staff/dashboard',
  STAFF_REVIEW_CLAIMS: '/staff/review-claims',
  STAFF_MANAGE_CUSTODY: '/staff/manage-custody',
  STAFF_PROCESS_RETURN: '/staff/process-return',

  // Admin
  ADMIN_DASHBOARD: '/admin/dashboard',
  ADMIN_USERS: '/admin/users',
  ADMIN_USER_DETAIL: '/admin/users/:id',
  ADMIN_CAMPUSES: '/admin/campuses',
  ADMIN_CATEGORIES: '/admin/categories',
  ADMIN_LOCATIONS: '/admin/locations',
  ADMIN_REPORTS: '/admin/reports',
  ADMIN_SETTINGS: '/admin/settings',
  ADMIN_AUDIT_LOGS: '/admin/audit-logs',
  ADMIN_PERMISSIONS: '/admin/permissions',

  // Profile
  PROFILE: '/profile',

  // Errors
  UNAUTHORIZED: '/401',
  FORBIDDEN: '/403',
  NOT_FOUND: '/404',
  SERVER_ERROR: '/500',
} as const
