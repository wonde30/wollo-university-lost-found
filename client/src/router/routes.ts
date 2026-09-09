/**
 * Application route definitions.
 *
 * PERFORMANCE: Dashboard routes use nested layout parents so that
 * DashboardLayout (sidebar, header, NotificationBell, SSE connection)
 * stays mounted across navigations instead of being destroyed & recreated.
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
      title: 'Home',
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
  {
    path: ROUTE_PATHS.CONFIRM_RETURN,
    name: ROUTE_NAMES.CONFIRM_RETURN,
    component: () => import('@/views/public/ConfirmReturnPage.vue'),
    meta: {
      title: 'Confirm Property Receipt',
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
  // TOP-LEVEL DASHBOARD SHORTCUTS & CONVENIENCE ALIASES (nested under DashboardLayout)
  // ==========================================
  {
    path: '/',
    component: () => import('@/layouts/DashboardLayout.vue'),
    children: [
      {
        path: 'report-lost',
        alias: ['items/report', 'items/report/lost', 'student/report-lost'],
        name: ROUTE_NAMES.STUDENT_REPORT_LOST,
        component: () => import('@/views/student/ReportLostPage.vue'),
        meta: { requiresAuth: true, title: 'Report Lost Item' },
      },
      {
        path: 'report-found',
        alias: ['items/report/found', 'student/report-found'],
        name: ROUTE_NAMES.STUDENT_REPORT_FOUND,
        component: () => import('@/views/student/ReportFoundPage.vue'),
        meta: { requiresAuth: true, title: 'Report Found Item' },
      },
      {
        path: 'my-items',
        alias: ['student/my-items', 'student/items'],
        name: ROUTE_NAMES.STUDENT_MY_ITEMS,
        component: () => import('@/views/student/MyItemsPage.vue'),
        meta: { requiresAuth: true, title: 'My Items' },
      },
      {
        path: 'my-claims',
        alias: ['student/my-claims', 'student/claims'],
        name: ROUTE_NAMES.STUDENT_MY_CLAIMS,
        component: () => import('@/views/student/MyClaimsPage.vue'),
        meta: { requiresAuth: true, title: 'My Claims' },
      },
      {
        path: 'claim/:id',
        alias: ['submit-claim', 'submit-claim/:id', 'student/claim/:id', 'student/submit-claim', 'student/submit-claim/:id'],
        name: ROUTE_NAMES.STUDENT_SUBMIT_CLAIM,
        component: () => import('@/views/student/SubmitClaimPage.vue'),
        meta: { requiresAuth: true, title: 'Submit Claim' },
      },
    ],
  },

  // ==========================================
  // STUDENT ROUTES (nested under shared DashboardLayout)
  // ==========================================
  {
    path: '/student',
    component: () => import('@/layouts/DashboardLayout.vue'),
    redirect: '/student/dashboard',
    children: [
      {
        path: 'dashboard',
        name: ROUTE_NAMES.STUDENT_DASHBOARD,
        component: () => import('@/views/student/DashboardPage.vue'),
        meta: { requiresAuth: true, capability: 'student', title: 'Student Dashboard' },
      },
    ],
  },

  // ==========================================
  // STAFF ROUTES (nested under shared DashboardLayout)
  // ==========================================
  {
    path: '/staff',
    component: () => import('@/layouts/DashboardLayout.vue'),
    redirect: '/staff/dashboard',
    children: [
      {
        path: 'dashboard',
        name: ROUTE_NAMES.STAFF_DASHBOARD,
        component: () => import('@/views/staff/DashboardPage.vue'),
        meta: { requiresAuth: true, capability: 'staff', title: 'Staff Dashboard' },
      },
      {
        path: 'items',
        alias: ['lost-items', 'found-items'],
        name: ROUTE_NAMES.STAFF_ITEMS,
        component: () => import('@/views/admin/ItemsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_ALL_ITEMS', title: 'Items Directory' },
      },
      {
        path: 'review-claims',
        alias: ['claims'],
        name: ROUTE_NAMES.STAFF_REVIEW_CLAIMS,
        component: () => import('@/views/staff/ReviewClaimsPage.vue'),
        meta: { requiresAuth: true, permission: 'REVIEW_CLAIMS', title: 'Review Claims' },
      },
      {
        path: 'manage-custody',
        alias: ['custody'],
        name: ROUTE_NAMES.STAFF_MANAGE_CUSTODY,
        component: () => import('@/views/staff/ManageCustodyPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_CUSTODY', title: 'Manage Custody' },
      },
      {
        path: 'process-return',
        alias: ['returns'],
        name: ROUTE_NAMES.STAFF_PROCESS_RETURN,
        component: () => import('@/views/staff/ProcessReturnPage.vue'),
        meta: { requiresAuth: true, permission: 'PROCESS_RETURNS', title: 'Process Return' },
      },
      {
        path: 'match-suggestions',
        alias: ['matches'],
        name: ROUTE_NAMES.STAFF_MATCH_SUGGESTIONS,
        component: () => import('@/views/staff/MatchSuggestionsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_ALL_ITEMS', title: 'Match Suggestions' },
      },
    ],
  },

  // ==========================================
  // ADMIN ROUTES (nested under shared DashboardLayout)
  // ==========================================
  {
    path: '/admin',
    component: () => import('@/layouts/DashboardLayout.vue'),
    redirect: '/admin/dashboard',
    children: [
      {
        path: 'dashboard',
        name: ROUTE_NAMES.ADMIN_DASHBOARD,
        component: () => import('@/views/admin/DashboardPage.vue'),
        meta: { requiresAuth: true, capability: 'admin', title: 'Admin Dashboard' },
      },
      {
        path: 'items',
        alias: ['lost-items', 'found-items'],
        name: ROUTE_NAMES.ADMIN_ITEMS,
        component: () => import('@/views/admin/ItemsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_ALL_ITEMS', title: 'Items Directory' },
      },
      {
        path: 'users',
        name: ROUTE_NAMES.ADMIN_USERS,
        component: () => import('@/views/admin/UsersPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_USERS', title: 'User Management' },
      },
      {
        path: 'users/:id',
        name: ROUTE_NAMES.ADMIN_USER_DETAIL,
        component: () => import('@/views/admin/UserDetailPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_USERS', title: 'User Details' },
      },
      {
        path: 'campuses',
        name: ROUTE_NAMES.ADMIN_CAMPUSES,
        component: () => import('@/views/admin/CampusesPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_CAMPUSES', title: 'Campus Management' },
      },
      {
        path: 'organizational-units',
        alias: ['units'],
        name: ROUTE_NAMES.ADMIN_ORGANIZATIONAL_UNITS,
        component: () => import('@/views/admin/OrganizationalUnitsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_CAMPUSES', title: 'Organizational Units' },
      },

      {
        path: 'categories',
        name: ROUTE_NAMES.ADMIN_CATEGORIES,
        component: () => import('@/views/admin/CategoriesPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_CATEGORIES', title: 'Category Management' },
      },
      {
        path: 'locations',
        name: ROUTE_NAMES.ADMIN_LOCATIONS,
        component: () => import('@/views/admin/LocationsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_LOCATIONS', title: 'Location Management' },
      },
      {
        path: 'storage-locations',
        name: ROUTE_NAMES.ADMIN_STORAGE_LOCATIONS,
        component: () => import('@/views/admin/StorageLocationsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_LOCATIONS', title: 'Storage Vault Management' },
      },
      {
        path: 'reports',
        name: ROUTE_NAMES.ADMIN_REPORTS,
        component: () => import('@/views/admin/ReportsPage.vue'),
        meta: { requiresAuth: true, permission: 'GENERATE_REPORTS', title: 'Reports' },
      },
      {
        path: 'settings',
        name: ROUTE_NAMES.ADMIN_SETTINGS,
        component: () => import('@/views/admin/SettingsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_SETTINGS', title: 'System Settings' },
      },
      {
        path: 'audit-logs',
        name: ROUTE_NAMES.ADMIN_AUDIT_LOGS,
        component: () => import('@/views/admin/AuditLogsPage.vue'),
        meta: { requiresAuth: true, permission: 'VIEW_AUDIT_LOGS', title: 'Audit Logs' },
      },
      {
        path: 'announcements',
        name: ROUTE_NAMES.ADMIN_ANNOUNCEMENTS,
        component: () => import('@/views/admin/AnnouncementsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_SETTINGS', title: 'System Announcements' },
      },
      {
        path: 'roles',
        name: ROUTE_NAMES.ADMIN_ROLES,
        component: () => import('@/views/admin/RolesPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_PERMISSIONS', title: 'Role Management' },
      },
      {
        path: 'permissions',
        name: ROUTE_NAMES.ADMIN_PERMISSIONS,
        component: () => import('@/views/admin/PermissionsPage.vue'),
        meta: { requiresAuth: true, permission: 'MANAGE_PERMISSIONS', title: 'Role Permissions Matrix' },
      },
    ],
  },

  // ==========================================
  // PROFILE ROUTE (requires auth, any role — nested under DashboardLayout)
  // ==========================================
  {
    path: '/profile',
    component: () => import('@/layouts/DashboardLayout.vue'),
    children: [
      {
        path: '',
        name: ROUTE_NAMES.PROFILE,
        component: () => import('@/views/profile/ProfilePage.vue'),
        meta: { requiresAuth: true, title: 'Profile' },
      },
    ],
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
