<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { useUiStore } from '@/stores/ui.store'
import { currentLocale, t } from '@/i18n'
import { getExportFilename } from '@/stores/settings.store'
import type { Role, CreateRolePayload, UpdateRolePayload } from '@/features/admin/types/admin.types'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Shield,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Edit2,
  Trash2,
  Users,
  CheckCircle2,
  Lock,
  CheckSquare,
  Square,
  ShieldCheck,
  ToggleLeft,
  ToggleRight,
  ArrowRight,
  PackageSearch,
} from 'lucide-vue-next'

const router = useRouter()
const permissionsStore = usePermissionsStore()
const authStore = useAuthStore()
const uiStore = useUiStore()

// State
const showFilters = ref(false)
const isRefreshing = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')
const selectedStatus = ref<'all' | 'active' | 'inactive'>('all')
const selectedType = ref<'all' | 'system' | 'custom'>('all')

// Multi-Selection State
const selectedRoleIds = ref<number[]>([])

function closeRoleMenu() {
  // Context menu handled by AppActionMenu
}

// Modals State
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingRoleId = ref<number | null>(null)
const isSubmitting = ref(false)

const form = reactive({
  name: '',
  display_name: '',
  display_name_am: '',
  description: '',
  description_am: '',
  is_active: true,
  is_system: false,
  permission_ids: [] as number[],
})

// Metrics Computation
const totalRoles = computed(() => permissionsStore.roles.length)
const activeRoles = computed(() => permissionsStore.roles.filter(r => r.is_active).length)
const totalAssignedUsers = computed(() => permissionsStore.roles.reduce((sum, r) => sum + (r.users_count || 0), 0))
const unassignedRoles = computed(() => permissionsStore.roles.filter(r => (r.users_count || 0) === 0).length)

// Reactive Filtered List
const filteredRoles = computed(() => {
  let list = permissionsStore.roles

  if (selectedStatus.value !== 'all') {
    const isActive = selectedStatus.value === 'active'
    list = list.filter(r => r.is_active === isActive)
  }

  if (selectedType.value !== 'all') {
    const isSystem = selectedType.value === 'system'
    list = list.filter(r => r.is_system === isSystem)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(r =>
      r.name.toLowerCase().includes(query) ||
      r.display_name.toLowerCase().includes(query) ||
      (r.display_name_am && r.display_name_am.includes(query)) ||
      (r.description && r.description.toLowerCase().includes(query))
    )
  }

  return list
})

const paginatedRoles = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredRoles.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredRoles.value.length / perPage.value) || 1)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedRoles.value.length === 0) return false
  return paginatedRoles.value.every(r => selectedRoleIds.value.includes(r.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedRoles.value.map(r => r.id)
    selectedRoleIds.value = selectedRoleIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedRoles.value.map(r => r.id)
    const newSelected = new Set([...selectedRoleIds.value, ...pageIds])
    selectedRoleIds.value = Array.from(newSelected)
  }
}

function toggleSelectRole(id: number) {
  const index = selectedRoleIds.value.indexOf(id)
  if (index !== -1) {
    selectedRoleIds.value.splice(index, 1)
  } else {
    selectedRoleIds.value.push(id)
  }
}

// Grouped Permissions inside Modal
const groupedPermissionsList = computed(() => {
  if (permissionsStore.permissionGroups.length === 0) {
    return [
      {
        id: 0,
        name: 'all',
        display_name: t('admin.permissions.title'),
        display_name_am: null,
        description: null,
        permissions: permissionsStore.permissions,
      },
    ]
  }

  const list = permissionsStore.permissionGroups.map(group => {
    const perms = permissionsStore.permissions.filter(p => p.permission_group_id === group.id)
    return {
      id: group.id,
      name: group.name,
      display_name: group.display_name,
      display_name_am: group.display_name_am,
      description: group.description,
      permissions: perms,
    }
  }).filter(g => g.permissions.length > 0)

  const ungrouped = permissionsStore.permissions.filter(p => !p.permission_group_id)
  if (ungrouped.length > 0) {
    list.push({
      id: -1,
      name: 'ungrouped',
      display_name: t('admin.permissionGroups.noGroup'),
      display_name_am: null,
      description: null,
      permissions: ungrouped,
    })
  }

  return list
})

function toggleGroupPermissions(groupId: number) {
  const targetGroup = groupedPermissionsList.value.find(g => g.id === groupId)
  if (!targetGroup) return
  const groupPermIds = targetGroup.permissions.map(p => p.id)
  const allSelected = groupPermIds.every(id => form.permission_ids.includes(id))

  if (allSelected) {
    form.permission_ids = form.permission_ids.filter(id => !groupPermIds.includes(id))
  } else {
    const newIds = new Set([...form.permission_ids, ...groupPermIds])
    form.permission_ids = Array.from(newIds)
  }
}

function isGroupAllSelected(groupId: number): boolean {
  const targetGroup = groupedPermissionsList.value.find(g => g.id === groupId)
  if (!targetGroup || targetGroup.permissions.length === 0) return false
  return targetGroup.permissions.every(p => form.permission_ids.includes(p.id))
}

// Refresh Handler
async function handleRefresh() {
  isRefreshing.value = true
  try {
    await permissionsStore.fetchDbPermissions(true)
    uiStore.success('Roles refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh roles')
  } finally {
    isRefreshing.value = false
  }
}

// Filter Options
const statusOptions = computed(() => [
  { label: 'All Status', value: 'all' },
  { label: t('admin.roles.activeRoles'), value: 'active' },
  { label: t('admin.users.suspended'), value: 'inactive' },
])

const typeOptions = computed(() => [
  { label: 'All Types', value: 'all' },
  { label: t('admin.roles.systemRoles'), value: 'system' },
  { label: t('admin.roles.custom'), value: 'custom' },
])

// Modal Handlers
function openCreateModal() {
  modalMode.value = 'create'
  editingRoleId.value = null
  form.name = ''
  form.display_name = ''
  form.display_name_am = ''
  form.description = ''
  form.description_am = ''
  form.is_active = true
  form.is_system = false
  form.permission_ids = []
  isModalOpen.value = true
}

function openEditModal(role: Role) {
  closeRoleMenu()
  modalMode.value = 'edit'
  editingRoleId.value = role.id
  form.name = role.name
  form.display_name = role.display_name
  form.display_name_am = role.display_name_am || ''
  form.description = role.description || ''
  form.description_am = role.description_am || ''
  form.is_active = role.is_active
  form.is_system = role.is_system
  form.permission_ids = role.permissions ? role.permissions.map(p => p.id) : (role.permission_ids || [])
  isModalOpen.value = true
}

async function handleSaveRole() {
  if (!form.display_name.trim()) {
    uiStore.warning(t('validation.required', { field: t('admin.roles.displayName') }))
    return
  }

  isSubmitting.value = true
  try {
    if (modalMode.value === 'create') {
      const payload: CreateRolePayload = {
        name: form.name.trim() || form.display_name.toLowerCase().replace(/\s+/g, '_'),
        display_name: form.display_name.trim(),
        display_name_am: form.display_name_am.trim() || undefined,
        description: form.description.trim() || undefined,
        description_am: form.description_am.trim() || undefined,
        is_active: form.is_active,
        permission_ids: form.permission_ids,
      }
      await permissionsStore.addRole(payload)
      uiStore.success(t('admin.roles.createdSuccess'))
    } else if (editingRoleId.value) {
      const payload: UpdateRolePayload = {
        display_name: form.display_name.trim(),
        display_name_am: form.display_name_am.trim() || undefined,
        description: form.description.trim() || undefined,
        description_am: form.description_am.trim() || undefined,
        is_active: form.is_active,
        permission_ids: form.permission_ids,
      }
      await permissionsStore.updateRole(editingRoleId.value, payload)
      uiStore.success(t('admin.roles.updatedSuccess'))
    }

    if (authStore.user) {
      await authStore.fetchUser(true)
    }

    isModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || t('common.errorOccurred'))
  } finally {
    isSubmitting.value = false
  }
}

function handleToggleStatus(role: Role) {
  closeRoleMenu()
  const newStatus = !role.is_active
  uiStore.confirm({
    title: newStatus ? t('admin.users.activateTitle') : t('admin.users.suspendTitle'),
    message: `${t('admin.users.toggleStatusConfirm')} "${role.display_name}"?`,
    confirmText: newStatus ? t('admin.users.activateBtn') : t('admin.users.suspendBtn'),
    variant: newStatus ? 'primary' : 'danger',
    onConfirm: async () => {
      await permissionsStore.toggleRoleActive(role.id)
      uiStore.success(t('admin.roles.statusUpdated', { name: role.display_name, status: newStatus ? t('admin.users.active') : t('admin.users.suspended') }))
    },
  })
}

function handleDeleteRole(role: Role) {
  closeRoleMenu()
  if (role.is_system) {
    uiStore.warning(t('admin.roles.systemRoleProtected'))
    return
  }

  uiStore.confirm({
    title: t('admin.roles.deleteConfirmTitle'),
    message: `${t('admin.roles.deleteConfirmMessage')} "${role.display_name}"?`,
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      await permissionsStore.removeRole(role.id)
      uiStore.success(t('admin.roles.deletedSuccess'))
    },
  })
}

// Bulk Actions
async function handleBulkToggleStatus(activate: boolean) {
  if (selectedRoleIds.value.length === 0) return
  const ids = [...selectedRoleIds.value]
  for (const id of ids) {
    const r = permissionsStore.roles.find(role => role.id === id)
    if (r && r.is_active !== activate) {
      await permissionsStore.toggleRoleActive(id)
    }
  }
  uiStore.success(`${ids.length} roles ${activate ? 'activated' : 'deactivated'}.`)
}

// Export CSV
function exportRolesCsv() {
  if (permissionsStore.roles.length === 0) {
    uiStore.warning('No roles to export')
    return
  }
  const headers = ['ID', 'Slug/Name', 'Display Name', 'Description', 'Type', 'Status', 'Users Count', 'Permissions Count']
  const rows = permissionsStore.roles.map(r => [
    r.id,
    `"${r.name}"`,
    `"${r.display_name}"`,
    `"${r.description || ''}"`,
    r.is_system ? 'System' : 'Custom',
    r.is_active ? 'Active' : 'Inactive',
    r.users_count || 0,
    r.permissions?.length || 0,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', getExportFilename('roles'))
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Roles exported as CSV.')
}

onMounted(() => {
  permissionsStore.fetchDbPermissions(true)
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeRoleMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Shield class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.access') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.roles.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid (Matching Reference Screenshot) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: All Roles -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.roles.totalRoles') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalRoles }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Shield class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Roles -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.roles.activeRoles') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeRoles }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Assigned Users -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.roles.assignedUsers') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalAssignedUsers }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Users class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Unassigned Roles -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Unassigned Roles
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ unassignedRoles }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Lock class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.roles.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalRoles }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.roles.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="roles-search"
            :placeholder="t('admin.roles.searchPlaceholder')"
            :model-value="searchQuery"
            class="w-full text-sm"
            @update:model-value="searchQuery = $event; currentPage = 1"
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
          @click="showFilters = !showFilters"
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
          @click="exportRolesCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <!-- Refresh Button -->
        <button
          type="button"
          title="Refresh List"
          :disabled="isRefreshing"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing ? 'animate-spin' : ''" />
        </button>

        <!-- Create New Role Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.roles.addRole') }}
        </AppButton>
      </div>

    </div>

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 gap-3 transition-all duration-200"
    >
      <!-- Status Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('common.status') }}
        </label>
        <AppSelect
          :options="statusOptions"
          :model-value="selectedStatus"
          class="w-full text-xs"
          @update:model-value="selectedStatus = $event as any; currentPage = 1"
        />
      </div>

      <!-- Type Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.roles.roleType') }}
        </label>
        <AppSelect
          :options="typeOptions"
          :model-value="selectedType"
          class="w-full text-xs"
          @update:model-value="selectedType = $event as any; currentPage = 1"
        />
      </div>
    </div>

    <!-- Bulk Actions Header (When selected) -->
    <div
      v-if="selectedRoleIds.length > 0"
      class="px-4 py-2.5 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D]/60 border border-[#0B5D3B]/30 flex items-center justify-between text-xs transition-all shadow-xs"
    >
      <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedRoleIds.length }} roles selected
      </span>
      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(true)">
          {{ t('admin.users.activateBtn') }}
        </AppButton>
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(false)">
          {{ t('admin.users.suspendBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Enterprise Roles Data Table -->
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
              {{ t('admin.roles.roleName') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.description') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.roles.roleType') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.permissions.title') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.roles.assignedUsers') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="permissionsStore.loading && permissionsStore.roles.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4 space-y-1.5">
              <div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
              <div class="h-3 w-44 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="paginatedRoles.length === 0">
          <tr>
            <td colspan="8" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedStatus !== 'all' ? 'No roles match your search filters' : 'No roles created yet' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedStatus !== 'all' ? 'Try adjusting your search query or status filter.' : 'Define custom system roles with granular RBAC permissions.' }}
                </p>
                <div v-if="!searchQuery && selectedStatus === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Create Role</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="role in paginatedRoles"
            :key="role.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedRoleIds.includes(role.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectRole(role.id)"
              >
                <CheckSquare v-if="selectedRoleIds.includes(role.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Role Name (Clean Text) -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ currentLocale === 'am' && role.display_name_am ? role.display_name_am : role.display_name }}
                </p>
                <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                  {{ role.name }}
                </p>
              </div>
            </td>

            <!-- Description (Clean Text) -->
            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300 max-w-xs truncate">
              {{ (currentLocale === 'am' && role.description_am) ? role.description_am : (role.description || '—') }}
            </td>

            <!-- Role Type -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border',
                  role.is_system
                    ? 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/60 dark:border-blue-800'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700',
                ]"
              >
                {{ role.is_system ? t('admin.roles.system') : t('admin.roles.custom') }}
              </span>
            </td>

            <!-- Permissions Count Pill (Clickable) -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors cursor-pointer"
                @click="openEditModal(role)"
              >
                <span>{{ role.permissions?.length || 0 }} Permissions</span>
                <ArrowRight class="h-3 w-3" />
              </button>
            </td>

            <!-- Assigned Users -->
            <td class="px-4 py-3.5">
              <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-slate-300">
                <Users class="h-3.5 w-3.5 text-slate-400" />
                {{ role.users_count || 0 }}
              </span>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="cursor-pointer"
                @click="handleToggleStatus(role)"
              >
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition-colors',
                    role.is_active
                      ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60 hover:bg-emerald-100'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200',
                  ]"
                >
                  {{ role.is_active ? t('admin.users.active') : t('admin.users.suspended') }}
                </span>
              </button>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Edit Role -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openEditModal(role)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <!-- 2. View Permissions Matrix -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); closeRoleMenu(); router.push({ name: 'admin-permissions' })"
                >
                  <ShieldCheck class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.permissions.matrixView') }}</span>
                </button>

                <!-- 3. Activate / Deactivate Toggle -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleToggleStatus(role)"
                >
                  <ToggleRight v-if="role.is_active" class="h-4 w-4 text-emerald-500" />
                  <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                  <span>{{ role.is_active ? t('admin.users.suspendBtn') : t('admin.users.activateBtn') }}</span>
                </button>

                <!-- 4. Delete Role (if custom) -->
                <template v-if="!role.is_system">
                  <div class="my-1 border-t border-slate-100 dark:border-slate-800" />
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); handleDeleteRole(role)"
                  >
                    <Trash2 class="h-4 w-4 text-rose-500" />
                    <span>{{ t('common.delete') }}</span>
                  </button>
                </template>
              </AppActionMenu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Pagination -->
    <AppPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      :total="filteredRoles.length"
      :per-page="perPage"
      @update:current-page="currentPage = $event"
      @update:per-page="perPage = $event; currentPage = 1"
    />

    <!-- Modal Form: Create / Edit Role -->
    <AppModal
      v-model:open="isModalOpen"
      :title="modalMode === 'create' ? t('admin.roles.addRoleTitle') : t('admin.roles.editRoleTitle')"
      max-width="2xl"
    >
      <div class="space-y-4 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="role-display-name"
            :label="t('admin.roles.displayName') + ' *'"
            :placeholder="t('admin.roles.displayNamePlaceholder')"
            :model-value="form.display_name"
            required
            @update:model-value="form.display_name = $event"
          />

          <AppInput
            id="role-name"
            :label="t('admin.roles.roleKey') + ' *'"
            :placeholder="t('admin.roles.roleKeyPlaceholder')"
            :model-value="form.name"
            :disabled="modalMode === 'edit'"
            required
            @update:model-value="form.name = $event"
          />
        </div>

        <AppInput
          id="role-display-name-am"
          :label="t('admin.roles.displayNameAm')"
          :placeholder="t('admin.roles.displayNameAmPlaceholder')"
          :model-value="form.display_name_am"
          @update:model-value="form.display_name_am = $event"
        />

        <AppTextarea
          id="role-description"
          :label="t('common.description')"
          :placeholder="t('admin.roles.descPlaceholder')"
          :model-value="form.description"
          :rows="2"
          @update:model-value="form.description = $event"
        />

        <!-- Grouped Permissions Selector -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">
            {{ t('admin.roles.assignPermissions') }} ({{ form.permission_ids.length }} {{ t('admin.roles.selected') }})
          </label>

          <div class="max-h-64 overflow-y-auto space-y-3 p-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 text-xs">
            <div
              v-for="group in groupedPermissionsList"
              :key="group.id"
              class="p-3 rounded-lg bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80"
            >
              <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-2 mb-2">
                <span class="font-bold text-slate-800 dark:text-slate-200">
                  {{ (currentLocale === 'am' && group.display_name_am) ? group.display_name_am : group.display_name }}
                </span>
                <button
                  type="button"
                  class="text-[11px] font-semibold text-[#0B5D3B] dark:text-[#75bd97] hover:underline cursor-pointer"
                  @click="toggleGroupPermissions(group.id)"
                >
                  {{ isGroupAllSelected(group.id) ? 'Deselect Group' : 'Select All Group' }}
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <label
                  v-for="perm in group.permissions"
                  :key="perm.id"
                  class="flex items-start gap-2 text-[11px] text-slate-700 dark:text-slate-300 cursor-pointer"
                >
                  <input
                    type="checkbox"
                    :value="perm.id"
                    v-model="form.permission_ids"
                    class="mt-0.5 rounded text-[#0B5D3B] focus:ring-[#0B5D3B]"
                  />
                  <span>{{ (currentLocale === 'am' && perm.display_name_am) ? perm.display_name_am : (perm.display_name || perm.name) }}</span>
                </label>
              </div>
            </div>
          </div>
        </div>

        <div class="pt-2">
          <AppCheckbox
            id="role-active"
            :label="t('admin.roles.isActiveRole')"
            :model-value="form.is_active"
            @update:model-value="form.is_active = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmitting" @click="handleSaveRole">
            {{ modalMode === 'create' ? t('admin.roles.createRole') : t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
