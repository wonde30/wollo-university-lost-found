/**
 * Application route definitions.
 * Using Page.vue files where they exist.
 */

import type { RouteRecordRaw } from 'vue-router'
import { ROUTE_NAMES } from './route-names'
import { ROUTE_PATHS } from './route-paths'

export const routes: RouteRecordRaw[] = [
  // ==========================================
  // PUBLIC ROUTES (no auth required)
  // ==========================================
  {
    path: ROUTE_PATHS.HOME,
    name: ROUTE_NAMES.HOME,
    component: () => import('@/views/public/HomePage.vue'),
    meta: {
      title: 'Home - Wollo Lost & Found',
    },
  },
  {
    path: ROUTE_PATHS.BROWSE,
    name: ROUTE_NAMES.BROWSE,
    component: () => import('@/views/public/BrowsePage.vue'),
    meta: {
      title: 'Browse Items',
    },
  },
  {
    path: ROUTE_PATHS.ITEM_DETAIL,
    name: ROUTE_NAMES.ITEM_DETAIL,
    component: () => import('@/views/public/ItemDetailPage.vue'),
    meta: {
      title: 'Item Details',
    },
  },
  {
    path: ROUTE_PATHS.TRACK_ITEM,
    alias: '/track',
    name: ROUTE_NAMES.TRACK_ITEM,
    component: () => import('@/views/public/TrackItemPage.vue'),
    meta: {
      title: 'Track Item',
    },
  },

  // ==========================================
  // AUTH ROUTES (guest only - redirect if authenticated)
  // ==========================================
  {
    path: ROUTE_PATHS.LOGIN,
    alias: '/login',
    name: ROUTE_NAMES.LOGIN,
    component: () => import('@/views/auth/LoginPage.vue'),
    meta: {
      requiresGuest: true,
      title: 'Login',
    },
  },
  {
    path: ROUTE_PATHS.REGISTER,
    alias: '/register',
    name: ROUTE_NAMES.REGISTER,
    component: () => import('@/views/auth/RegisterPage.vue'),
    meta: {
      requiresGuest: true,
      title: 'Register',
    },
  },
  {
    path: ROUTE_PATHS.FORGOT_PASSWORD,
    alias: '/forgot-password',
    name: ROUTE_NAMES.FORGOT_PASSWORD,
    component: () => import('@/views/auth/ForgotPasswordPage.vue'),
    meta: {
      requiresGuest: true,
      title: 'Forgot Password',
    },
  },
  {
    path: ROUTE_PATHS.RESET_PASSWORD,
    alias: '/reset-password',
    name: ROUTE_NAMES.RESET_PASSWORD,
    component: () => import('@/views/auth/ResetPasswordPage.vue'),
    meta: {
      requiresGuest: true,
      title: 'Reset Password',
    },
  },
  {
    path: ROUTE_PATHS.VERIFY_OTP,
    alias: '/verify-otp',
    name: ROUTE_NAMES.VERIFY_OTP,
    component: () => import('@/views/auth/VerifyOtpPage.vue'),
    meta: {
      title: 'Verify OTP',
    },
  },

  // ==========================================
  // STUDENT ROUTES (requires auth + student role)
  // ==========================================
  {
    path: ROUTE_PATHS.STUDENT_DASHBOARD,
    name: ROUTE_NAMES.STUDENT_DASHBOARD,
    component: () => import('@/views/student/DashboardPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'Student Dashboard',
    },
  },
  {
    path: ROUTE_PATHS.STUDENT_MY_ITEMS,
    name: ROUTE_NAMES.STUDENT_MY_ITEMS,
    component: () => import('@/views/student/MyItemsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'My Items',
    },
  },
  {
    path: ROUTE_PATHS.STUDENT_MY_CLAIMS,
    name: ROUTE_NAMES.STUDENT_MY_CLAIMS,
    component: () => import('@/views/student/MyClaimsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'My Claims',
    },
  },
  {
    path: ROUTE_PATHS.STUDENT_REPORT_LOST,
    name: ROUTE_NAMES.STUDENT_REPORT_LOST,
    component: () => import('@/views/student/ReportLostPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'Report Lost Item',
    },
  },
  {
    path: ROUTE_PATHS.STUDENT_REPORT_FOUND,
    name: ROUTE_NAMES.STUDENT_REPORT_FOUND,
    component: () => import('@/views/student/ReportFoundPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'Report Found Item',
    },
  },
  {
    path: ROUTE_PATHS.STUDENT_SUBMIT_CLAIM,
    name: ROUTE_NAMES.STUDENT_SUBMIT_CLAIM,
    component: () => import('@/views/student/SubmitClaimPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['student'],
      title: 'Submit Claim',
    },
  },

  // ==========================================
  // STAFF ROUTES (requires auth + staff role)
  // ==========================================
  {
    path: ROUTE_PATHS.STAFF_DASHBOARD,
    name: ROUTE_NAMES.STAFF_DASHBOARD,
    component: () => import('@/views/staff/DashboardPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['staff', 'admin'],
      title: 'Staff Dashboard',
    },
  },
  {
    path: ROUTE_PATHS.STAFF_REVIEW_CLAIMS,
    alias: '/staff/claims',
    name: ROUTE_NAMES.STAFF_REVIEW_CLAIMS,
    component: () => import('@/views/staff/ReviewClaimsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['staff', 'admin'],
      title: 'Review Claims',
    },
  },
  {
    path: ROUTE_PATHS.STAFF_MANAGE_CUSTODY,
    alias: '/staff/custody',
    name: ROUTE_NAMES.STAFF_MANAGE_CUSTODY,
    component: () => import('@/views/staff/ManageCustodyPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['staff', 'admin'],
      title: 'Manage Custody',
    },
  },
  {
    path: ROUTE_PATHS.STAFF_PROCESS_RETURN,
    alias: '/staff/returns',
    name: ROUTE_NAMES.STAFF_PROCESS_RETURN,
    component: () => import('@/views/staff/ProcessReturnPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['staff', 'admin'],
      title: 'Process Return',
    },
  },

  // ==========================================
  // ADMIN ROUTES (requires auth + admin role)
  // ==========================================
  {
    path: ROUTE_PATHS.ADMIN_DASHBOARD,
    name: ROUTE_NAMES.ADMIN_DASHBOARD,
    component: () => import('@/views/admin/DashboardPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Admin Dashboard',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_USERS,
    name: ROUTE_NAMES.ADMIN_USERS,
    component: () => import('@/views/admin/UsersPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'User Management',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_USER_DETAIL,
    name: ROUTE_NAMES.ADMIN_USER_DETAIL,
    component: () => import('@/views/admin/UserDetailPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'User Details',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_CAMPUSES,
    name: ROUTE_NAMES.ADMIN_CAMPUSES,
    component: () => import('@/views/admin/CampusesPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Campus Management',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_CATEGORIES,
    name: ROUTE_NAMES.ADMIN_CATEGORIES,
    component: () => import('@/views/admin/CategoriesPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Category Management',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_LOCATIONS,
    name: ROUTE_NAMES.ADMIN_LOCATIONS,
    component: () => import('@/views/admin/LocationsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Location Management',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_REPORTS,
    name: ROUTE_NAMES.ADMIN_REPORTS,
    component: () => import('@/views/admin/ReportsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Reports',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_SETTINGS,
    name: ROUTE_NAMES.ADMIN_SETTINGS,
    component: () => import('@/views/admin/SettingsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'System Settings',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_AUDIT_LOGS,
    name: ROUTE_NAMES.ADMIN_AUDIT_LOGS,
    component: () => import('@/views/admin/AuditLogsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Audit Logs',
    },
  },
  {
    path: ROUTE_PATHS.ADMIN_PERMISSIONS,
    name: ROUTE_NAMES.ADMIN_PERMISSIONS,
    component: () => import('@/views/admin/PermissionsPage.vue'),
    meta: {
      requiresAuth: true,
      roles: ['admin'],
      title: 'Role Permissions Matrix',
    },
  },

  // ==========================================
  // PROFILE ROUTE (requires auth, any role)
  // ==========================================
  {
    path: ROUTE_PATHS.PROFILE,
    name: ROUTE_NAMES.PROFILE,
    component: () => import('@/views/profile/ProfilePage.vue'),
    meta: {
      requiresAuth: true,
      title: 'Profile',
    },
  },

  // ==========================================
  // ERROR ROUTES
  // ==========================================
  {
    path: ROUTE_PATHS.UNAUTHORIZED,
    name: ROUTE_NAMES.UNAUTHORIZED,
    component: () => import('@/views/errors/UnauthorizedPage.vue'),
    meta: {
      title: '401 - Unauthorized',
    },
  },
  {
    path: ROUTE_PATHS.FORBIDDEN,
    name: ROUTE_NAMES.FORBIDDEN,
    component: () => import('@/views/errors/ForbiddenPage.vue'),
    meta: {
      title: '403 - Forbidden',
    },
  },
  {
    path: ROUTE_PATHS.NOT_FOUND,
    name: ROUTE_NAMES.NOT_FOUND,
    component: () => import('@/views/errors/NotFoundPage.vue'),
    meta: {
      title: '404 - Not Found',
    },
  },
  {
    path: ROUTE_PATHS.SERVER_ERROR,
    name: ROUTE_NAMES.SERVER_ERROR,
    component: () => import('@/views/errors/ServerErrorPage.vue'),
    meta: {
      title: '500 - Server Error',
    },
  },

  // Catch-all wildcard to 404
  {
    path: '/:pathMatch(.*)*',
    component: () => import('@/views/errors/NotFoundPage.vue'),
    meta: {
      title: '404 - Not Found',
    },
  },
]
