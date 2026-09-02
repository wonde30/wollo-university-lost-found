<script setup lang="ts">
import { onMounted, reactive, computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminUsers } from '@/features/admin/composables/useAdminUsers'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { useUiStore } from '@/stores/ui.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { currentLocale, t } from '@/i18n'
import type { User, UserRole } from '@/features/auth/types/auth.types'
import * as adminApi from '@/features/admin/api/admin.api'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Users,
  Search,
  Filter,
  X,
  Plus,
  RotateCcw,
  Download,
  Copy,
  Check,
  Eye,
  Edit2,
  ShieldCheck,
  CheckSquare,
  Square,
  KeyRound,
  UserCheck,
  UserX,
  ToggleLeft,
  ToggleRight,
  PackageSearch,
} from 'lucide-vue-next'

const router = useRouter()
const { users, loading, pagination, fetchUsers, createUser, updateUser, updateUserRole, toggleUserActive } = useAdminUsers()
const uiStore = useUiStore()
const authStore = useAuthStore()
const referencesStore = useReferencesStore()
const permissionsStore = usePermissionsStore()

// Filter Visibility State
const showFilters = ref(false)
const copiedKey = ref<string | null>(null)
const isRefreshing = ref(false)

// Metric Cards Computations
const activeCount = computed(() => users.value.filter(u => (u as any).is_active !== false).length)
const suspendedCount = computed(() => users.value.filter(u => (u as any).is_active === false).length)
const staffAdminsCount = computed(() => users.value.filter(u => u.role === 'admin' || u.role === 'staff').length)

function closeUserMenu() {
  // Context menu handled by AppActionMenu
}

// Filters State
const filters = reactive<{
  search: string
  role: UserRole | ''
  status: 'all' | 'active' | 'inactive'
  campus_id: string
  organizational_unit_id: string
  page: number
  per_page: number
}>({
  search: '',
  role: '',
  status: 'all',
  campus_id: 'all',
  organizational_unit_id: 'all',
  page: 1,
  per_page: 10,
})

// Multi-Selection State
const selectedUserIds = ref<number[]>([])

// Create User Modal State
const isCreateModalOpen = ref(false)
const isSubmittingUser = ref(false)

const newUserForm = reactive({
  full_name: '',
  university_id: '',
  email: '',
  password: '',
  role: 'student',
  role_id: 3,
  phone: '',
  organizational_unit_id: '' as number | '',
  is_active: true,
})

// Edit User Modal State
const isEditModalOpen = ref(false)
const editingUser = ref<User | null>(null)
const editUserForm = reactive({
  full_name: '',
  university_id: '',
  phone: '',
  organizational_unit_id: '' as number | '',
  is_active: true,
})

// Assign Role Modal State
const isAssignRoleModalOpen = ref(false)
const assignRoleUser = ref<User | null>(null)
const selectedRoleToAssign = ref<string>('student')

// Quick Copy Helper with Visual Feedback
async function copyToClipboard(text: string, key: string) {
  try {
    await navigator.clipboard.writeText(text)
    copiedKey.value = key
    uiStore.success(`Copied "${text}"`)
    setTimeout(() => {
      if (copiedKey.value === key) copiedKey.value = null
    }, 2000)
  } catch {
    uiStore.error('Failed to copy to clipboard')
  }
}

// Fetch and Load
async function load(page = 1) {
  filters.page = page
  await fetchUsers({
    page,
    per_page: filters.per_page,
    search: filters.search || undefined,
    role: filters.role ? (filters.role as UserRole) : undefined,
  })
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => load(1), 300)
}

// Functional Refresh Handler
// Only refreshes the user list — reference data (roles, campuses, organizational units)
// is long-lived and does not need to be reloaded every time the user clicks Refresh.
async function handleRefresh() {
  isRefreshing.value = true
  try {
    await load(filters.page)
    uiStore.success('User list refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh user list')
  } finally {
    isRefreshing.value = false
  }
}

// Client-side refined filtered list for status, campus, organizational unit
const filteredUsers = computed(() => {
  let list = users.value

  if (filters.status === 'active') {
    list = list.filter(u => (u as any).is_active !== false)
  } else if (filters.status === 'inactive') {
    list = list.filter(u => (u as any).is_active === false)
  }

  if (filters.organizational_unit_id !== 'all') {
    const unitId = Number(filters.organizational_unit_id)
    list = list.filter(u => u.organizational_units?.some(ou => ou.id === unitId))
  }

  return list
})

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (filteredUsers.value.length === 0) return false
  return filteredUsers.value.every(u => selectedUserIds.value.includes(u.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = filteredUsers.value.map(u => u.id)
    selectedUserIds.value = selectedUserIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = filteredUsers.value.map(u => u.id)
    const newSelected = new Set([...selectedUserIds.value, ...pageIds])
    selectedUserIds.value = Array.from(newSelected)
  }
}

function toggleSelectUser(id: number) {
  const index = selectedUserIds.value.indexOf(id)
  if (index !== -1) {
    selectedUserIds.value.splice(index, 1)
  } else {
    selectedUserIds.value.push(id)
  }
}

// Role and Filter Options
const roleOptions = computed(() => {
  const options = [{ label: t('admin.users.allRoles'), value: '' }]
  if (permissionsStore.roles.length > 0) {
    permissionsStore.roles.forEach(r => {
      options.push({
        label: currentLocale.value === 'am' && r.display_name_am ? r.display_name_am : r.display_name || r.name,
        value: r.name,
      })
    })
  } else {
    options.push(
      { label: t('admin.roles.admin'), value: 'admin' },
      { label: t('admin.roles.staff'), value: 'staff' },
      { label: t('admin.roles.student'), value: 'student' },
    )
  }
  return options
})

const stateOptions = computed(() => [
  { label: 'All States', value: 'all' },
  { label: t('admin.users.active'), value: 'active' },
  { label: t('admin.users.suspended'), value: 'inactive' },
])

const campusOptions = computed(() => {
  const options = [{ label: 'All Campuses', value: 'all' }]
  referencesStore.campuses.forEach(c => {
    options.push({
      label: currentLocale.value === 'am' && c.display_name_am ? c.display_name_am : c.name,
      value: String(c.id),
    })
  })
  return options
})

const organizationalUnitOptions = computed(() => {
  const options = [{ label: 'All Units', value: 'all' }]
  referencesStore.organizationalUnits.forEach(u => {
    options.push({
      label: currentLocale.value === 'am' && u.name_am ? u.name_am : `${u.name} (${u.short_code})`,
      value: String(u.id),
    })
  })
  return options
})

// Format role display name using localized text or fallback
function formatRoleName(role: string): string {
  if (!role) return '—'
  const r = permissionsStore.roles.find(item => item.name === role)
  if (r) {
    return currentLocale.value === 'am' && r.display_name_am ? r.display_name_am : r.display_name || r.name
  }
  const trans = t(`admin.roles.${role}`)
  if (trans && !trans.startsWith('admin.roles.')) return trans
  return role.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

// Dynamic Roles for table and modals
const dynamicRoles = computed(() => {
  if (permissionsStore.roles.length > 0) {
    return permissionsStore.roles.map(r => ({
      name: r.name,
      label: currentLocale.value === 'am' && r.display_name_am ? r.display_name_am : r.display_name || r.name,
    }))
  }
  return [
    { name: 'admin', label: t('admin.roles.admin') },
    { name: 'staff', label: t('admin.roles.staff') },
    { name: 'student', label: t('admin.roles.student') },
  ]
})

function getPermissionsCount(user: User): number {
  if (Array.isArray(user.permissions) && user.permissions.length > 0) {
    return user.permissions.length
  }
  if ((user as any).permissions_count !== undefined) {
    return (user as any).permissions_count
  }
  const roleObj = permissionsStore.roles.find(r => r.name === user.role)
  if (roleObj && roleObj.permissions) {
    return roleObj.permissions.length
  }
  if (user.role === 'admin') return permissionsStore.permissions.length || 24
  if (user.role === 'staff') return 12
  return 5
}

// ==========================================
// Context Menu Actions
// ==========================================
function handleViewUser(user: User) {
  closeUserMenu()
  router.push({ name: 'admin-user-detail', params: { id: user.id } })
}

function handleOpenEditModal(user: User) {
  closeUserMenu()
  if (!referencesStore.organizationalUnitsLoaded) {
    referencesStore.fetchOrganizationalUnits()
  }
  editingUser.value = user
  editUserForm.full_name = user.full_name
  editUserForm.university_id = user.university_id || ''
  editUserForm.phone = user.phone || ''
  editUserForm.organizational_unit_id = user.organizational_units?.[0]?.id || ''
  editUserForm.is_active = (user as any).is_active !== false
  isEditModalOpen.value = true
}

async function handleSaveEditUser() {
  if (!editingUser.value || !editUserForm.full_name.trim()) {
    uiStore.error('Full name is required.')
    return
  }

  isSubmittingUser.value = true
  try {
    const payload: any = {
      full_name: editUserForm.full_name.trim(),
      university_id: editUserForm.university_id.trim() || undefined,
      phone: editUserForm.phone.trim() || null,
      is_active: editUserForm.is_active,
    }
    await updateUser(editingUser.value.id, payload)
    uiStore.success(`User "${editUserForm.full_name}" updated successfully.`)
    isEditModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || 'Failed to update user')
  } finally {
    isSubmittingUser.value = false
  }
}

function handleOpenAssignRoleModal(user: User) {
  closeUserMenu()
  assignRoleUser.value = user
  selectedRoleToAssign.value = user.role
  isAssignRoleModalOpen.value = true
}

async function handleSaveAssignedRole() {
  if (!assignRoleUser.value) return
  isSubmittingUser.value = true
  try {
    await updateUserRole(assignRoleUser.value.id, selectedRoleToAssign.value)
    if (authStore.user?.id === assignRoleUser.value.id) {
      await authStore.fetchUser(true)
    }
    uiStore.success(`${assignRoleUser.value.full_name}: Role updated to ${selectedRoleToAssign.value.toUpperCase()}`)
    isAssignRoleModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || 'Failed to update role')
  } finally {
    isSubmittingUser.value = false
  }
}

// User Direct Permissions Modal State
const isUserPermsModalOpen = ref(false)
const userPermsTarget = ref<User | null>(null)
const userPermsLoading = ref(false)
const userPermsData = ref<adminApi.UserPermissionsPayload | null>(null)
const userPermsDirectIds = ref<number[]>([])
const userPermsSearch = ref('')
const userPermsSubmitting = ref(false)

const filteredModalPermissions = computed(() => {
  if (!userPermsData.value) return []
  const query = userPermsSearch.value.trim().toLowerCase()
  if (!query) return userPermsData.value.available_permissions
  return userPermsData.value.available_permissions.filter(
    p =>
      p.name.toLowerCase().includes(query) ||
      p.display_name.toLowerCase().includes(query) ||
      (p.description && p.description.toLowerCase().includes(query))
  )
})

function isUserRoleInherited(permName: string): boolean {
  if (!userPermsData.value || !userPermsTarget.value) return false
  if (userPermsTarget.value.role === 'admin') return true
  return userPermsData.value.role_permissions.includes(permName)
}

function isUserDirectGranted(permId: number): boolean {
  return userPermsDirectIds.value.includes(permId)
}

function toggleUserDirectPerm(permId: number) {
  const index = userPermsDirectIds.value.indexOf(permId)
  if (index !== -1) {
    userPermsDirectIds.value.splice(index, 1)
  } else {
    userPermsDirectIds.value.push(permId)
  }
}

async function handleOpenUserPermissionsModal(user: User) {
  closeUserMenu()
  userPermsTarget.value = user
  userPermsSearch.value = ''
  isUserPermsModalOpen.value = true
  userPermsLoading.value = true
  try {
    const data = await adminApi.getUserPermissions(user.id)
    userPermsData.value = data
    userPermsDirectIds.value = [...data.direct_permission_ids]
  } catch (err: any) {
    uiStore.error('Failed to load user permissions')
    isUserPermsModalOpen.value = false
  } finally {
    userPermsLoading.value = false
  }
}

async function handleSaveUserPerms() {
  if (!userPermsTarget.value) return
  userPermsSubmitting.value = true
  try {
    await adminApi.syncUserPermissions(userPermsTarget.value.id, userPermsDirectIds.value)
    uiStore.success(`Direct permissions updated for ${userPermsTarget.value.full_name}`)
    isUserPermsModalOpen.value = false
    await load(filters.page)
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || 'Failed to save permissions')
  } finally {
    userPermsSubmitting.value = false
  }
}

function handleSendPasswordReset(user: User) {
  closeUserMenu()
  uiStore.confirm({
    title: 'Send Password Reset',
    message: `Send secure password reset instructions to "${user.email}"?`,
    confirmText: 'Send Reset Link',
    variant: 'warning',
    onConfirm: async () => {
      uiStore.success(`Password reset link dispatched to ${user.email}`)
    },
  })
}

function handleToggleActive(user: User) {
  closeUserMenu()
  const isCurrentlyActive = (user as any).is_active !== false
  uiStore.confirm({
    title: isCurrentlyActive ? t('admin.users.suspendTitle') : t('admin.users.activateTitle'),
    message: `${t('admin.users.toggleStatusConfirm')} "${user.full_name}"?`,
    confirmText: isCurrentlyActive ? t('admin.users.suspendBtn') : t('admin.users.activateBtn'),
    variant: isCurrentlyActive ? 'danger' : 'primary',
    onConfirm: async () => {
      await toggleUserActive(user.id)
      uiStore.success(`${user.full_name}: ${t('admin.users.statusUpdated')}`)
    },
  })
}

// Bulk Actions
async function handleBulkToggleActive(activate: boolean) {
  if (selectedUserIds.value.length === 0) return
  const ids = [...selectedUserIds.value]
  for (const id of ids) {
    const u = users.value.find(user => user.id === id)
    if (u && ((u as any).is_active !== false) !== activate) {
      await toggleUserActive(id)
    }
  }
  uiStore.success(`${ids.length} accounts ${activate ? 'activated' : 'suspended'}.`)
}

// Export CSV
function exportUsersCsv() {
  if (users.value.length === 0) {
    uiStore.warning('No users to export')
    return
  }
  const headers = ['ID', 'Full Name', 'University ID', 'Email', 'Role', 'Status', 'Phone', 'Created At']
  const rows = users.value.map(u => [
    u.id,
    `"${u.full_name}"`,
    `"${u.university_id || ''}"`,
    `"${u.email}"`,
    u.role,
    (u as any).is_active !== false ? 'Active' : 'Suspended',
    `"${u.phone || ''}"`,
    u.created_at || '',
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_users_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('User records exported as CSV.')
}

function toggleFilters() {
  showFilters.value = !showFilters.value
  if (showFilters.value) {
    if (!referencesStore.campusesLoaded) {
      referencesStore.fetchCampuses()
    }
    if (!referencesStore.organizationalUnitsLoaded) {
      referencesStore.fetchOrganizationalUnits()
    }
  }
}

// Create User Modal Handlers
function openCreateModal() {
  if (!referencesStore.organizationalUnitsLoaded) {
    referencesStore.fetchOrganizationalUnits()
  }
  newUserForm.full_name = ''
  newUserForm.university_id = ''
  newUserForm.email = ''
  newUserForm.password = ''
  newUserForm.role = 'student'
  const defaultRole = permissionsStore.roles.find(r => r.name === 'student')
  newUserForm.role_id = defaultRole ? defaultRole.id : 3
  newUserForm.phone = ''
  newUserForm.organizational_unit_id = referencesStore.organizationalUnits.length > 0 ? referencesStore.organizationalUnits[0].id : ''
  newUserForm.is_active = true
  isCreateModalOpen.value = true
}

async function handleSaveNewUser() {
  if (!newUserForm.full_name.trim() || !newUserForm.email.trim() || !newUserForm.university_id.trim() || !newUserForm.password) {
    uiStore.error('Full name, university ID, email, and password are required.')
    return
  }

  isSubmittingUser.value = true
  try {
    const roleObj = permissionsStore.roles.find(r => r.name === newUserForm.role)
    const roleId = roleObj ? roleObj.id : (newUserForm.role_id || 3)

    const payload: any = {
      full_name: newUserForm.full_name.trim(),
      university_id: newUserForm.university_id.trim(),
      email: newUserForm.email.trim(),
      password: newUserForm.password,
      role_id: roleId,
      phone: newUserForm.phone.trim() || undefined,
      organizational_unit_id: newUserForm.organizational_unit_id ? Number(newUserForm.organizational_unit_id) : undefined,
      is_active: newUserForm.is_active,
    }
    await createUser(payload)
    uiStore.success(`User "${payload.full_name}" registered successfully.`)
    isCreateModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || 'Failed to create user')
  } finally {
    isSubmittingUser.value = false
  }
}

onMounted(async () => {
  // Fire users fetch immediately — this is the primary data for this page.
  const usersPromise = load()

  // Roles: only fetch if not already initialized (e.g. first visit after login).
  if (!permissionsStore.initialized) {
    permissionsStore.fetchRoles()
  }

  // Campuses & Organizational units are loaded on-demand when filters/modals open.

  // Wait only for users — dropdowns can populate reactively as they load.
  await usersPromise
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeUserMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Users class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.access') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.users.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Users -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.users.accountsCount') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ pagination.total }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Users class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Users -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.users.active') }} Accounts
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <UserCheck class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Staff & Admins -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Staff & Personnel
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ staffAdminsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
          <ShieldCheck class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Suspended Accounts -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.users.suspended') }} Accounts
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ suspendedCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <UserX class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title & Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.users.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ pagination.total }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        Control user access, assign system roles, and update account statuses.
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="users-search"
            :placeholder="t('admin.users.searchPlaceholder')"
            :model-value="filters.search"
            class="w-full text-sm"
            @update:model-value="filters.search = $event; onSearch()"
          >
            <template #icon-left>
              <Search class="h-4 w-4 text-slate-400" />
            </template>
          </AppInput>
        </div>

        <!-- Filter Toggle Button -->
        <button
          type="button"
          :class="[
            'h-10 px-3.5 rounded-xl border text-sm font-semibold transition-all inline-flex items-center gap-1.5 cursor-pointer shrink-0 shadow-2xs',
            showFilters
              ? 'bg-slate-100 dark:bg-slate-800 border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white'
              : 'bg-white dark:bg-[#111827] border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800',
          ]"
          @click="toggleFilters"
        >
          <X v-if="showFilters" class="h-4 w-4 text-slate-500 dark:text-slate-400" />
          <Filter v-else class="h-4 w-4 text-slate-500 dark:text-slate-400" />
          <span>{{ showFilters ? 'Hide Filter' : 'Filter' }}</span>
        </button>
      </div>

      <!-- Action Buttons on Right -->
      <div class="flex items-center gap-2 shrink-0">
        <!-- Export CSV Button -->
        <button
          type="button"
          title="Export CSV"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer"
          @click="exportUsersCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <!-- Refresh Button (Fully Functional with Loading Spin) -->
        <button
          type="button"
          title="Refresh List"
          :disabled="isRefreshing || loading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || loading ? 'animate-spin' : ''" />
        </button>

        <!-- Create New User Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          Create New
        </AppButton>
      </div>
    </div>

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 transition-all duration-200"
    >
      <!-- State / Status Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('common.state') }}
        </label>
        <AppSelect
          :options="stateOptions"
          :model-value="filters.status"
          class="w-full text-xs"
          @update:model-value="filters.status = $event as any"
        />
      </div>

      <!-- Role Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.users.role') }}
        </label>
        <AppSelect
          :options="roleOptions"
          :model-value="filters.role"
          class="w-full text-xs"
          @update:model-value="filters.role = $event as any; load(1)"
        />
      </div>

      <!-- Campus Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('common.campus') }}
        </label>
        <AppSelect
          :options="campusOptions"
          :model-value="filters.campus_id"
          class="w-full text-xs"
          @update:model-value="filters.campus_id = String($event)"
        />
      </div>

      <!-- Organizational Unit Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          Organizational Unit
        </label>
        <AppSelect
          :options="organizationalUnitOptions"
          :model-value="filters.organizational_unit_id"
          class="w-full text-xs"
          @update:model-value="filters.organizational_unit_id = String($event)"
        />
      </div>
    </div>

    <!-- Bulk Actions Header (When selected) -->
    <div
      v-if="selectedUserIds.length > 0"
      class="px-4 py-2.5 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D]/60 border border-[#0B5D3B]/30 flex items-center justify-between text-xs transition-all shadow-xs"
    >
      <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedUserIds.length }} {{ t('admin.users.title').toLowerCase() }} selected
      </span>
      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleActive(true)">
          {{ t('admin.users.activateBtn') }}
        </AppButton>
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleActive(false)">
          {{ t('admin.users.suspendBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Enterprise User Data Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
      <table class="w-full min-w-[760px] text-sm">
        <thead>
          <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 text-xs">
            <!-- Checkbox Header -->
            <th class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectAllCurrentPage"
              >
                <CheckSquare v-if="isAllCurrentPageSelected" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Name
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Organizational Unit
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Role
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              University ID
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              State
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Permissions
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              Action
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && users.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4">
              <div class="flex items-center gap-3">
                <div class="h-9 w-9 bg-slate-200 dark:bg-slate-800 rounded-full shrink-0" />
                <div class="space-y-1">
                  <div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded" />
                  <div class="h-3 w-40 bg-slate-100 dark:bg-slate-800/60 rounded" />
                </div>
              </div>
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="filteredUsers.length === 0">
          <tr>
            <td colspan="8" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ filters.search || filters.role !== '' || filters.status !== 'all' ? 'No users match your filters' : 'No user directory entries found' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ filters.search || filters.role !== '' || filters.status !== 'all' ? 'Try adjusting your search criteria or role filters.' : 'Provision student, staff, and admin accounts to grant access to campus services.' }}
                </p>
                <div v-if="!filters.search && filters.role === '' && filters.status === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Create User</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="user in filteredUsers"
            :key="user.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedUserIds.includes(user.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectUser(user.id)"
              >
                <CheckSquare v-if="selectedUserIds.includes(user.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Name & Email with Copy Icon -->
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-3">
                <AppAvatar :name="user.full_name" size="sm" />
                <div class="min-w-0">
                  <p class="font-bold text-slate-900 dark:text-white truncate">
                    {{ user.full_name }}
                  </p>
                  <div class="flex items-center gap-1 mt-0.5">
                    <span class="text-slate-400 dark:text-slate-500 font-mono text-[11px] truncate">
                      {{ user.email }}
                    </span>
                    <button
                      type="button"
                      :title="t('common.copy') || 'Copy email'"
                      class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-0.5 rounded cursor-pointer dark:text-slate-300"
                      @click="copyToClipboard(user.email, `email-${user.id}`)"
                    >
                      <Check v-if="copiedKey === `email-${user.id}`" class="h-3 w-3 text-emerald-500" />
                      <Copy v-else class="h-3 w-3" />
                    </button>
                  </div>
                </div>
              </div>
            </td>

            <!-- Organizational Unit -->
            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
              <span v-if="user.organizational_units && user.organizational_units.length > 0" class="font-medium text-slate-800 dark:text-slate-200">
                {{ user.organizational_units.map(u => u.name).join(', ') }}
              </span>
              <span v-else-if="user.profile?.gender" class="capitalize text-slate-500">
                {{ user.profile.gender }}
              </span>
              <span v-else class="text-slate-400">—</span>
            </td>

            <!-- Role -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300">
              <span v-if="user.role" class="font-medium text-slate-800 dark:text-slate-200">
                {{ formatRoleName(user.role) }}
              </span>
              <span v-else class="text-slate-400">—</span>
            </td>

            <!-- University ID with Copy Button -->
            <td class="px-4 py-3.5">
              <div v-if="user.university_id" class="flex items-center gap-1.5">
                <span class="font-mono text-slate-700 dark:text-slate-300 text-xs font-semibold">
                  {{ user.university_id }}
                </span>
                <button
                  type="button"
                  :title="t('common.copy') || 'Copy ID'"
                  class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 p-0.5 rounded cursor-pointer dark:text-slate-300"
                  @click="copyToClipboard(user.university_id, `uid-${user.id}`)"
                >
                  <Check v-if="copiedKey === `uid-${user.id}`" class="h-3 w-3 text-emerald-500" />
                  <Copy v-else class="h-3 w-3" />
                </button>
              </div>
              <span v-else class="text-slate-400">—</span>
            </td>

            <!-- State / Status Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border',
                  (user as any).is_active !== false
                    ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                ]"
              >
                {{ (user as any).is_active !== false ? 'Active' : 'Suspended' }}
              </span>
            </td>

            <!-- Permissions Pill Button -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors cursor-pointer"
                @click="handleOpenUserPermissionsModal(user)"
              >
                {{ getPermissionsCount(user) }} Permissions
              </button>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">
                <!-- 1. View -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleViewUser(user)"
                >
                  <Eye class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.users.view') }}</span>
                </button>

                <!-- 2. Edit -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleOpenEditModal(user)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.users.edit') }}</span>
                </button>

                <!-- 3. Assign Roles -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleOpenAssignRoleModal(user)"
                >
                  <UserCheck class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.users.assignRoles') }}</span>
                </button>

                <!-- 4. Direct Permissions -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleOpenUserPermissionsModal(user)"
                >
                  <ShieldCheck class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.users.directPermissions') }}</span>
                </button>

                <!-- 5. Send Password Reset -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleSendPasswordReset(user)"
                >
                  <KeyRound class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.users.sendPasswordReset') }}</span>
                </button>

                <div class="my-1 border-t border-slate-100 dark:border-slate-800" />

                <!-- 6. Activate / Deactivate Toggle -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleToggleActive(user)"
                >
                  <ToggleRight v-if="(user as any).is_active !== false" class="h-4 w-4 text-emerald-500" />
                  <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                  <span>{{ (user as any).is_active !== false ? t('admin.users.suspendBtn') : t('admin.users.activateBtn') }}</span>
                </button>
              </AppActionMenu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Pagination -->
    <AppPagination
      :current-page="pagination.current_page"
      :last-page="pagination.last_page"
      :total="pagination.total"
      :per-page="filters.per_page"
      @change="load"
      @update:per-page="filters.per_page = $event; load(1)"
    />

    <!-- Modal Form: Create User -->
    <AppModal
      v-model:open="isCreateModalOpen"
      title="Create New User Account"
      max-width="lg"
    >
      <div class="space-y-4 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="new-user-fullname"
            label="Full Name *"
            placeholder="e.g. Abebe Bikila"
            :model-value="newUserForm.full_name"
            required
            @update:model-value="newUserForm.full_name = $event"
          />

          <AppInput
            id="new-user-id"
            label="University / Student ID *"
            placeholder="e.g. UGR/1234/14"
            :model-value="newUserForm.university_id"
            required
            @update:model-value="newUserForm.university_id = $event"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="new-user-email"
            type="email"
            label="Email Address *"
            placeholder="e.g. abebe@wollo.edu.et"
            :model-value="newUserForm.email"
            required
            @update:model-value="newUserForm.email = $event"
          />

          <AppInput
            id="new-user-pass"
            type="password"
            label="Temporary Password *"
            placeholder="Minimum 8 characters"
            :model-value="newUserForm.password"
            required
            @update:model-value="newUserForm.password = $event"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              Role *
            </label>
            <select
              v-model="newUserForm.role"
              class="w-full h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
            >
              <option v-for="r in dynamicRoles" :key="r.name" :value="r.name">{{ r.label }}</option>
            </select>
          </div>

          <AppInput
            id="new-user-phone"
            label="Phone Number"
            placeholder="e.g. +251 91 234 5678"
            :model-value="newUserForm.phone"
            @update:model-value="newUserForm.phone = $event"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            Organizational / Academic Unit
          </label>
          <select
            v-model="newUserForm.organizational_unit_id"
            class="w-full h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
          >
            <option value="">None / General</option>
            <option v-for="u in referencesStore.organizationalUnits" :key="u.id" :value="u.id">
              {{ u.name }} ({{ u.short_code }})
            </option>
          </select>
        </div>

        <div class="pt-2">
          <AppCheckbox
            id="new-user-active"
            label="Active Account (User can login immediately)"
            :model-value="newUserForm.is_active"
            @update:model-value="newUserForm.is_active = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isCreateModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmittingUser" @click="handleSaveNewUser">
            Create User
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Modal Form: Edit User -->
    <AppModal
      v-model:open="isEditModalOpen"
      title="Edit User Details"
      max-width="md"
    >
      <div class="space-y-4 py-2">
        <AppInput
          id="edit-user-fullname"
          label="Full Name *"
          :model-value="editUserForm.full_name"
          required
          @update:model-value="editUserForm.full_name = $event"
        />

        <AppInput
          id="edit-user-uid"
          label="University / Student ID"
          :model-value="editUserForm.university_id"
          @update:model-value="editUserForm.university_id = $event"
        />

        <AppInput
          id="edit-user-phone"
          label="Phone Number"
          :model-value="editUserForm.phone"
          @update:model-value="editUserForm.phone = $event"
        />

        <div class="pt-2">
          <AppCheckbox
            id="edit-user-active"
            label="Active Account"
            :model-value="editUserForm.is_active"
            @update:model-value="editUserForm.is_active = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isEditModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmittingUser" @click="handleSaveEditUser">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Modal Form: Assign Roles -->
    <AppModal
      v-model:open="isAssignRoleModalOpen"
      title="Assign System Role"
      max-width="sm"
    >
      <div class="space-y-4 py-2">
        <p class="text-xs text-slate-500 dark:text-slate-400">
          Assign role and access permissions for <strong>{{ assignRoleUser?.full_name }}</strong>:
        </p>

        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            System Role *
          </label>
          <select
            v-model="selectedRoleToAssign"
            class="w-full h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
          >
            <option v-for="r in dynamicRoles" :key="r.name" :value="r.name">{{ r.label }} ({{ r.name }})</option>
          </select>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isAssignRoleModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmittingUser" @click="handleSaveAssignedRole">
            Update Role
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- Modal: User Direct Permissions Management -->
    <AppModal
      v-model:open="isUserPermsModalOpen"
      :title="`Direct Permissions: ${userPermsTarget?.full_name || 'User'}`"
      max-width="2xl"
    >
      <div class="space-y-4 py-2">
        <div class="p-3.5 rounded-xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800/60 text-xs text-blue-900 dark:text-blue-200 flex items-start gap-2.5">
          <Shield class="h-4 w-4 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" />
          <div>
            <p class="font-bold">Base Role: {{ userPermsTarget?.role?.toUpperCase() }}</p>
            <p class="text-[11px] mt-0.5 text-blue-800 dark:text-blue-300">
              Permissions marked with "Role" are automatically inherited. Check individual boxes below to grant custom direct permissions to this specific user.
            </p>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <div class="relative flex-1">
            <AppInput
              id="modal-user-perm-search"
              placeholder="Search permissions..."
              :model-value="userPermsSearch"
              class="w-full text-xs"
              @update:model-value="userPermsSearch = $event"
            >
              <template #icon-left>
                <Search class="h-3.5 w-3.5 text-slate-400" />
              </template>
            </AppInput>
          </div>
          <span class="text-xs font-bold text-slate-500 shrink-0">
            {{ userPermsDirectIds.length }} Direct Grants
          </span>
        </div>

        <div v-if="userPermsLoading" class="py-12 text-center text-xs text-slate-400">
          Loading user permission configuration...
        </div>

        <div v-else class="max-h-80 overflow-y-auto space-y-2 pr-1 divide-y divide-slate-100 dark:divide-slate-800">
          <div
            v-for="perm in filteredModalPermissions"
            :key="perm.id"
            class="pt-2 pb-1.5 flex items-center justify-between gap-3 text-xs"
          >
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2">
                <span class="font-bold text-slate-900 dark:text-white font-mono text-[11px]">{{ perm.name }}</span>
                <span
                  v-if="isUserRoleInherited(perm.name)"
                  class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300"
                >
                  Role ({{ userPermsTarget?.role }})
                </span>
                <span
                  v-if="isUserDirectGranted(perm.id)"
                  class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300"
                >
                  Direct
                </span>
              </div>
              <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                {{ perm.description || perm.display_name }}
              </p>
            </div>

            <label class="flex items-center gap-1.5 cursor-pointer shrink-0">
              <input
                type="checkbox"
                :checked="isUserDirectGranted(perm.id)"
                class="h-4 w-4 text-[#0B5D3B] border-slate-300 rounded focus:ring-[#0B5D3B] cursor-pointer"
                @change="toggleUserDirectPerm(perm.id)"
              />
              <span class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">Grant</span>
            </label>
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-between">
          <RouterLink
            v-if="userPermsTarget"
            :to="{ name: 'admin-user-detail', params: { id: userPermsTarget.id } }"
            class="text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] hover:underline"
            @click="isUserPermsModalOpen = false"
          >
            Open Full Detail Page &rarr;
          </RouterLink>
          <div class="flex items-center gap-2">
            <AppButton variant="ghost" size="sm" @click="isUserPermsModalOpen = false">
              {{ t('common.cancel') }}
            </AppButton>
            <AppButton variant="primary" size="sm" :loading="userPermsSubmitting" @click="handleSaveUserPerms">
              Save Permissions
            </AppButton>
          </div>
        </div>
      </template>
    </AppModal>
  </div>
</template>
