import type { UserRole } from '@/constants'

export interface PermissionDefinition {
  key: string
  label: string
  description: string
  category: 'items' | 'claims' | 'custody' | 'admin'
  defaultRoles: UserRole[]
}

export const PERMISSION_DEFINITIONS: Record<string, PermissionDefinition> = {
  // Items Domain
  REPORT_LOST: {
    key: 'REPORT_LOST',
    label: 'Report Lost Property',
    description: 'Submit reports for missing personal items across campus.',
    category: 'items',
    defaultRoles: ['student', 'staff', 'admin'],
  },
  REPORT_FOUND: {
    key: 'REPORT_FOUND',
    label: 'Report Found Property',
    description: 'Register items found in classrooms, dormitories, or campus grounds.',
    category: 'items',
    defaultRoles: ['student', 'staff', 'admin'],
  },
  EDIT_OWN_ITEM: {
    key: 'EDIT_OWN_ITEM',
    label: 'Edit Own Item Reports',
    description: 'Modify details, descriptions, and photos of personally reported items.',
    category: 'items',
    defaultRoles: ['student', 'staff', 'admin'],
  },
  DELETE_OWN_ITEM: {
    key: 'DELETE_OWN_ITEM',
    label: 'Delete Own Item Reports',
    description: 'Withdraw or cancel personally reported property listings.',
    category: 'items',
    defaultRoles: ['student', 'staff', 'admin'],
  },
  MANAGE_ALL_ITEMS: {
    key: 'MANAGE_ALL_ITEMS',
    label: 'Manage All Campus Items',
    description: 'View, edit, and moderate item reports submitted by any user.',
    category: 'items',
    defaultRoles: ['staff', 'admin'],
  },
  CHANGE_ITEM_STATUS: {
    key: 'CHANGE_ITEM_STATUS',
    label: 'Change Item Lifecycle Status',
    description: 'Transition items through verified, in-custody, and closed states.',
    category: 'items',
    defaultRoles: ['staff', 'admin'],
  },

  // Claims Domain
  SUBMIT_CLAIM: {
    key: 'SUBMIT_CLAIM',
    label: 'Submit Ownership Claims',
    description: 'File ownership verification claims for registered found items.',
    category: 'claims',
    defaultRoles: ['student', 'staff', 'admin'],
  },
  REVIEW_CLAIMS: {
    key: 'REVIEW_CLAIMS',
    label: 'Review & Verify Claims',
    description: 'Examine ownership proof, approve valid claims, or reject invalid claims.',
    category: 'claims',
    defaultRoles: ['staff', 'admin'],
  },
  REVERSE_CLAIMS: {
    key: 'REVERSE_CLAIMS',
    label: 'Reverse Claim Decisions',
    description: 'Reopen or reverse claim approvals upon administrative dispute.',
    category: 'claims',
    defaultRoles: ['staff', 'admin'],
  },

  // Custody & Returns Domain
  MANAGE_CUSTODY: {
    key: 'MANAGE_CUSTODY',
    label: 'Manage Physical Custody',
    description: 'Log item intake, vault storage bin placement, and inventory tracking.',
    category: 'custody',
    defaultRoles: ['staff', 'admin'],
  },
  MOVE_ITEM_CUSTODY: {
    key: 'MOVE_ITEM_CUSTODY',
    label: 'Transfer Storage Location',
    description: 'Move items between security offices, departments, or campuses.',
    category: 'custody',
    defaultRoles: ['staff', 'admin'],
  },
  PROCESS_RETURNS: {
    key: 'PROCESS_RETURNS',
    label: 'Process Property Handover',
    description: 'Execute identity verification and sign off physical return to claimant.',
    category: 'custody',
    defaultRoles: ['staff', 'admin'],
  },

  // Administration Domain
  ACCESS_ADMIN_DASHBOARD: {
    key: 'ACCESS_ADMIN_DASHBOARD',
    label: 'Access Admin Portal',
    description: 'Access institutional dashboards, compliance metrics, and system hubs.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_USERS: {
    key: 'MANAGE_USERS',
    label: 'User Directory & Roles',
    description: 'View user accounts, elevate roles, and manage access privileges.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  SUSPEND_USERS: {
    key: 'SUSPEND_USERS',
    label: 'Suspend User Accounts',
    description: 'Disable or reactivate student and staff accounts across the system.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_CAMPUSES: {
    key: 'MANAGE_CAMPUSES',
    label: 'Campus Management',
    description: 'Configure Dessie, Kombolcha, and other institutional campuses.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_CATEGORIES: {
    key: 'MANAGE_CATEGORIES',
    label: 'Category Taxonomy',
    description: 'Add, update, and order property classifications and icons.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_LOCATIONS: {
    key: 'MANAGE_LOCATIONS',
    label: 'Building & Drop Points',
    description: 'Configure campus buildings, floors, rooms, and handover counters.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  VIEW_AUDIT_LOGS: {
    key: 'VIEW_AUDIT_LOGS',
    label: 'View Security Audit Logs',
    description: 'Inspect immutable records of actions, IP addresses, and events.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  GENERATE_REPORTS: {
    key: 'GENERATE_REPORTS',
    label: 'Generate Reports & Analytics',
    description: 'Export system-wide analytics, recovery statistics, and metrics.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_SETTINGS: {
    key: 'MANAGE_SETTINGS',
    label: 'System Settings',
    description: 'Configure operational parameters, retention policies, and thresholds.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
  MANAGE_PERMISSIONS: {
    key: 'MANAGE_PERMISSIONS',
    label: 'Manage Dynamic RBAC',
    description: 'Configure role permissions and access control matrix in real-time.',
    category: 'admin',
    defaultRoles: ['admin'],
  },
}

export type PermissionKey = keyof typeof PERMISSION_DEFINITIONS

// Default matrix mapping
export const DEFAULT_ROLE_PERMISSIONS: Record<PermissionKey, UserRole[]> = Object.fromEntries(
  Object.entries(PERMISSION_DEFINITIONS).map(([key, def]) => [key, [...def.defaultRoles]])
) as Record<PermissionKey, UserRole[]>
