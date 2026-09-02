<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue'
import AppDataTable, { type TableColumn } from '@/components/ui/AppDataTable.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppBadge from '@/components/ui/AppBadge.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import { usePermissionsStore } from '@/permissions/stores/permissions.store'
import { useUiStore } from '@/stores/ui.store'
import { currentLocale, t } from '@/i18n'
import type {
  Permission,
  PermissionGroup,
  CreatePermissionPayload,
  UpdatePermissionPayload,
  CreatePermissionGroupPayload,
  UpdatePermissionGroupPayload,
} from '@/features/admin/types/admin.types'
import {
  ShieldCheck,
  Plus,
  RotateCcw,
  Search,
  CheckCircle2,
  Edit2,
  Trash2,
  Table as TableIcon,
  Lock,
  Layers,
  MoreVertical,
  CheckSquare,
  Square,
} from 'lucide-vue-next'

// ==========================================
// Stores: Centralized Store as Single Source of Truth
// ==========================================
const permissionsStore = usePermissionsStore()
const uiStore = useUiStore()

const activeTab = ref<'master-detail' | 'matrix' | 'table'>('master-detail')

// Selected Group ID for Master-Detail View
const selectedMasterGroupId = ref<number | null>(null)

// Search & Filter for Master Groups List
const groupListSearchQuery = ref('')

// Search & Filter for Permissions within Selected Group
const groupPermSearchQuery = ref('')
const groupPermStatusFilter = ref<'all' | 'active' | 'inactive' | 'system' | 'custom'>('all')
const groupPermPage = ref(1)
const groupPermPerPage = ref(10)

// Multi-Selection State
const selectedPermissionIds = ref<number[]>([])

// 3-Dots dropdown state for groups list
const activeGroupMenuId = ref<number | null>(null)

function toggleGroupMenu(groupId: number, event: Event) {
  event.stopPropagation()
  activeGroupMenuId.value = activeGroupMenuId.value === groupId ? null : groupId
}

function closeGroupMenu() {
  activeGroupMenuId.value = null
}

// Table Tab Filters & Pagination
const permCurrentPage = ref(1)
const permPerPage = ref(15)
const permSearchQuery = ref('')
const selectedCategory = ref<string>('all')
const selectedGroupId = ref<string>('all')

// ==========================================
// Computed Group Data & Counts
// ==========================================
const groups = computed(() => permissionsStore.permissionGroups)
const allPermissions = computed(() => permissionsStore.permissions)

// Map permissions count per group
const permissionCountMap = computed(() => {
  const map: Record<number, number> = {}
  for (const p of allPermissions.value) {
    if (p.permission_group_id) {
      map[p.permission_group_id] = (map[p.permission_group_id] || 0) + 1
    }
  }
  return map
})

function getGroupPermCount(group: PermissionGroup): number {
  return group.permissions_count ?? permissionCountMap.value[group.id] ?? 0
}

// Filtered Groups for Master Sidebar
const filteredMasterGroups = computed(() => {
  let list = groups.value
  const query = groupListSearchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      g =>
        g.name.toLowerCase().includes(query) ||
        g.display_name.toLowerCase().includes(query) ||
        (g.display_name_am && g.display_name_am.toLowerCase().includes(query)) ||
        (g.description && g.description.toLowerCase().includes(query))
    )
  }
  return list
})

// Current Selected Group
const currentGroup = computed<PermissionGroup | null>(() => {
  if (!selectedMasterGroupId.value && groups.value.length > 0) {
    return groups.value[0]
  }
  return groups.value.find(g => g.id === selectedMasterGroupId.value) || groups.value[0] || null
})

// Auto-select first group on load or change
watch(
  () => groups.value,
  newGroups => {
    if (newGroups.length > 0 && (!selectedMasterGroupId.value || !newGroups.some(g => g.id === selectedMasterGroupId.value))) {
      selectedMasterGroupId.value = newGroups[0].id
    }
  },
  { immediate: true }
)

// Permissions belonging to the current group
const currentGroupPermissions = computed(() => {
  if (!currentGroup.value) return []
  return allPermissions.value.filter(p => p.permission_group_id === currentGroup.value?.id)
})

// Filtered & Searched permissions in the right panel
const filteredGroupPermissions = computed(() => {
  let list = currentGroupPermissions.value

  if (groupPermStatusFilter.value === 'active') {
    list = list.filter(p => p.is_active)
  } else if (groupPermStatusFilter.value === 'inactive') {
    list = list.filter(p => !p.is_active)
  } else if (groupPermStatusFilter.value === 'system') {
    list = list.filter(p => p.is_system)
  } else if (groupPermStatusFilter.value === 'custom') {
    list = list.filter(p => !p.is_system)
  }

  const query = groupPermSearchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      p =>
        p.name.toLowerCase().includes(query) ||
        p.display_name.toLowerCase().includes(query) ||
        (p.display_name_am && p.display_name_am.toLowerCase().includes(query)) ||
        (p.description && p.description.toLowerCase().includes(query))
    )
  }

  return list
})

// Paginated group permissions
const paginatedGroupPermissions = computed(() => {
  const start = (groupPermPage.value - 1) * groupPermPerPage.value
  return filteredGroupPermissions.value.slice(start, start + groupPermPerPage.value)
})

const groupPermTotalPages = computed(() => Math.ceil(filteredGroupPermissions.value.length / groupPermPerPage.value) || 1)

// Multi-select helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedGroupPermissions.value.length === 0) return false
  return paginatedGroupPermissions.value.every(p => selectedPermissionIds.value.includes(p.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedGroupPermissions.value.map(p => p.id)
    selectedPermissionIds.value = selectedPermissionIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedGroupPermissions.value.map(p => p.id)
    const newSelected = new Set([...selectedPermissionIds.value, ...pageIds])
    selectedPermissionIds.value = Array.from(newSelected)
  }
}

function toggleSelectPermission(id: number) {
  const index = selectedPermissionIds.value.indexOf(id)
  if (index !== -1) {
    selectedPermissionIds.value.splice(index, 1)
  } else {
    selectedPermissionIds.value.push(id)
  }
}

function getInitials(name: string): string {
  if (!name) return 'PG'
  const words = name.trim().split(/\s+/)
  if (words.length >= 2) {
    return (words[0][0] + words[1][0]).toUpperCase()
  }
  return name.slice(0, 2).toUpperCase()
}

// ==========================================
// Table Tab View Computed & Helpers
// ==========================================
const categories = computed(() => [
  { id: 'all', label: t('admin.permissions.categoryAll') },
  { id: 'items', label: t('admin.permissions.categoryItems') },
  { id: 'claims', label: t('admin.permissions.categoryClaims') },
  { id: 'custody', label: t('admin.permissions.categoryCustody') },
  { id: 'admin', label: t('admin.permissions.categoryAdmin') },
])

const permColumns = computed<TableColumn[]>(() => [
  { key: 'display_name', label: t('admin.permissions.permissionName'), sortable: true },
  { key: 'permission_group', label: t('admin.permissions.group') },
  { key: 'category', label: t('admin.permissions.category') },
  { key: 'description', label: t('common.description') },
  { key: 'is_system', label: t('admin.permissions.type') },
  { key: 'is_active', label: t('common.status') },
  { key: 'actions', label: t('common.actions'), align: 'right' },
])

const filteredTablePermissions = computed(() => {
  let list = allPermissions.value

  if (selectedCategory.value !== 'all') {
    list = list.filter(p => p.category === selectedCategory.value)
  }

  if (selectedGroupId.value !== 'all') {
    const gId = Number(selectedGroupId.value)
    list = list.filter(p => p.permission_group_id === gId)
  }

  const query = permSearchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      p =>
        p.name.toLowerCase().includes(query) ||
        p.display_name.toLowerCase().includes(query) ||
        (p.display_name_am && p.display_name_am.toLowerCase().includes(query)) ||
        (p.description && p.description.toLowerCase().includes(query))
    )
  }

  return list
})

const paginatedTablePermissions = computed(() => {
  const start = (permCurrentPage.value - 1) * permPerPage.value
  return filteredTablePermissions.value.slice(start, start + permPerPage.value)
})

function getGroupName(groupId?: number): string {
  if (!groupId) return t('admin.permissionGroups.noGroup')
  const found = groups.value.find(g => g.id === groupId)
  if (!found) return t('admin.permissionGroups.noGroup')
  return (currentLocale.value === 'am' && found.display_name_am) ? found.display_name_am : found.display_name
}

// ==========================================
// Modals State
// ==========================================
const isPermModalOpen = ref(false)
const permModalMode = ref<'create' | 'edit'>('create')
const editingPermissionId = ref<number | null>(null)
const isSubmittingPerm = ref(false)

const permForm = reactive({
  name: '',
  display_name: '',
  display_name_am: '',
  description: '',
  description_am: '',
  category: 'items' as string,
  permission_group_id: null as number | null,
  is_active: true,
  is_system: false,
})

const isGroupModalOpen = ref(false)
const groupModalMode = ref<'create' | 'edit'>('create')
const editingGroupId = ref<number | null>(null)
const isSubmittingGroup = ref(false)

const groupForm = reactive({
  name: '',
  display_name: '',
  display_name_am: '',
  description: '',
  description_am: '',
  is_active: true,
  is_system: false,
})

function openCreateGroupModal() {
  closeGroupMenu()
  groupModalMode.value = 'create'
  editingGroupId.value = null
  groupForm.name = ''
  groupForm.display_name = ''
  groupForm.display_name_am = ''
  groupForm.description = ''
  groupForm.description_am = ''
  groupForm.is_active = true
  groupForm.is_system = false
  isGroupModalOpen.value = true
}

function openEditGroupModal(group: PermissionGroup) {
  closeGroupMenu()
  groupModalMode.value = 'edit'
  editingGroupId.value = group.id
  groupForm.name = group.name
  groupForm.display_name = group.display_name
  groupForm.display_name_am = group.display_name_am || ''
  groupForm.description = group.description || ''
  groupForm.description_am = group.description_am || ''
  groupForm.is_active = group.is_active
  groupForm.is_system = group.is_system
  isGroupModalOpen.value = true
}

async function handleSaveGroup() {
  if (!groupForm.display_name.trim()) {
    uiStore.warning(t('validation.required', { field: t('admin.permissionGroups.groupName') }))
    return
  }

  isSubmittingGroup.value = true
  try {
    if (groupModalMode.value === 'create') {
      const payload: CreatePermissionGroupPayload = {
        name: groupForm.name.trim() || groupForm.display_name.trim().toLowerCase().replace(/\s+/g, '_'),
        display_name: groupForm.display_name.trim(),
        display_name_am: groupForm.display_name_am.trim() || undefined,
        description: groupForm.description.trim() || undefined,
        description_am: groupForm.description_am.trim() || undefined,
        is_active: groupForm.is_active,
      }
      const created = await permissionsStore.addPermissionGroup(payload)
      selectedMasterGroupId.value = created.id
      uiStore.success(t('admin.permissionGroups.createdSuccess'))
    } else if (editingGroupId.value) {
      const payload: UpdatePermissionGroupPayload = {
        display_name: groupForm.display_name.trim(),
        display_name_am: groupForm.display_name_am.trim() || undefined,
        description: groupForm.description.trim() || undefined,
        description_am: groupForm.description_am.trim() || undefined,
        is_active: groupForm.is_active,
      }
      if (!groupForm.is_system && groupForm.name) {
        payload.name = groupForm.name.trim()
      }
      await permissionsStore.updatePermissionGroup(editingGroupId.value, payload)
      uiStore.success(t('admin.permissionGroups.updatedSuccess'))
    }
    isGroupModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || t('common.saveFailed'))
  } finally {
    isSubmittingGroup.value = false
  }
}

async function handleToggleGroupActive(group: PermissionGroup) {
  closeGroupMenu()
  try {
    const updated = await permissionsStore.toggleGroupActive(group.id)
    uiStore.success(
      t('admin.permissionGroups.statusUpdated', {
        name: group.display_name,
        status: updated.is_active ? t('common.active') : t('common.inactive'),
      })
    )
  } catch (err: any) {
    uiStore.error(err.message || t('common.updateFailed'))
  }
}

function handleDeleteGroup(group: PermissionGroup) {
  closeGroupMenu()
  if (group.is_system) {
    uiStore.error(t('admin.permissionGroups.systemGroupProtected'))
    return
  }

  uiStore.confirm({
    title: t('admin.permissionGroups.deleteConfirmTitle'),
    message: t('admin.permissionGroups.deleteConfirmMessage', { name: group.display_name }),
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        await permissionsStore.removePermissionGroup(group.id)
        if (selectedMasterGroupId.value === group.id && groups.value.length > 0) {
          selectedMasterGroupId.value = groups.value[0].id
        }
        uiStore.success(t('admin.permissionGroups.deletedSuccess'))
      } catch (err: any) {
        uiStore.error(err.response?.data?.message || err.message || t('common.deleteFailed'))
      }
    },
  })
}

// Permission Actions
function openCreatePermModal(preselectedGroupId?: number) {
  permModalMode.value = 'create'
  editingPermissionId.value = null
  permForm.name = ''
  permForm.display_name = ''
  permForm.display_name_am = ''
  permForm.description = ''
  permForm.description_am = ''
  permForm.category = 'items'
  permForm.permission_group_id = preselectedGroupId || selectedMasterGroupId.value || (groups.value.length > 0 ? groups.value[0].id : null)
  permForm.is_active = true
  permForm.is_system = false
  isPermModalOpen.value = true
}

function openEditPermModal(perm: Permission) {
  permModalMode.value = 'edit'
  editingPermissionId.value = perm.id
  permForm.name = perm.name
  permForm.display_name = perm.display_name
  permForm.display_name_am = perm.display_name_am || ''
  permForm.description = perm.description || ''
  permForm.description_am = perm.description_am || ''
  permForm.category = perm.category
  permForm.permission_group_id = perm.permission_group_id || perm.permission_group?.id || null
  permForm.is_active = perm.is_active
  permForm.is_system = perm.is_system
  isPermModalOpen.value = true
}

async function handleSavePermission() {
  if (!permForm.display_name.trim()) {
    uiStore.warning(t('validation.required', { field: t('admin.permissions.permissionName') }))
    return
  }

  if (!permForm.permission_group_id) {
    uiStore.warning(t('admin.permissions.permissionGroupRequired'))
    return
  }

  isSubmittingPerm.value = true
  try {
    if (permModalMode.value === 'create') {
      const payload: CreatePermissionPayload = {
        permission_group_id: permForm.permission_group_id,
        name: permForm.name.trim() || permForm.display_name.toUpperCase().replace(/\s+/g, '_'),
        display_name: permForm.display_name.trim(),
        display_name_am: permForm.display_name_am.trim() || undefined,
        description: permForm.description.trim() || undefined,
        description_am: permForm.description_am.trim() || undefined,
        category: permForm.category,
        is_active: permForm.is_active,
      }
      await permissionsStore.addPermission(payload)
      uiStore.success(t('admin.permissions.createdSuccess'))
    } else if (editingPermissionId.value) {
      const payload: UpdatePermissionPayload = {
        permission_group_id: permForm.permission_group_id,
        display_name: permForm.display_name.trim(),
        display_name_am: permForm.display_name_am.trim() || undefined,
        description: permForm.description.trim() || undefined,
        description_am: permForm.description_am.trim() || undefined,
        category: permForm.category,
        is_active: permForm.is_active,
      }
      if (!permForm.is_system && permForm.name) {
        payload.name = permForm.name.trim()
      }
      await permissionsStore.updatePermission(editingPermissionId.value, payload)
      uiStore.success(t('admin.permissions.updatedSuccess'))
    }
    isPermModalOpen.value = false
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || t('common.saveFailed'))
  } finally {
    isSubmittingPerm.value = false
  }
}

async function handleTogglePermActive(perm: Permission) {
  try {
    const updated = await permissionsStore.togglePermActive(perm.id)
    uiStore.success(
      t('admin.permissions.statusUpdated', {
        name: perm.display_name,
        status: updated.is_active ? t('common.active') : t('common.inactive'),
      })
    )
  } catch (err: any) {
    uiStore.error(err.message || t('common.updateFailed'))
  }
}

function handleDeletePermission(perm: Permission) {
  if (perm.is_system) {
    uiStore.error(t('admin.permissions.systemPermissionProtected'))
    return
  }

  uiStore.confirm({
    title: t('admin.permissions.deleteConfirmTitle'),
    message: t('admin.permissions.deleteConfirmMessage', { name: perm.display_name }),
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        await permissionsStore.removePermission(perm.id)
        selectedPermissionIds.value = selectedPermissionIds.value.filter(id => id !== perm.id)
        uiStore.success(t('admin.permissions.deletedSuccess'))
      } catch (err: any) {
        uiStore.error(err.response?.data?.message || err.message || t('common.deleteFailed'))
      }
    },
  })
}

// Bulk Actions
async function handleBulkToggleStatus(makeActive: boolean) {
  if (selectedPermissionIds.value.length === 0) return
  const ids = [...selectedPermissionIds.value]
  try {
    for (const id of ids) {
      await permissionsStore.updatePermission(id, { is_active: makeActive })
    }
    uiStore.success(`${ids.length} permissions updated to ${makeActive ? 'active' : 'inactive'}.`)
  } catch (err: any) {
    uiStore.error(err.message || t('common.updateFailed'))
  }
}

async function handleBulkDelete() {
  const nonSystemSelected = allPermissions.value
    .filter(p => selectedPermissionIds.value.includes(p.id) && !p.is_system)
    .map(p => p.id)

  if (nonSystemSelected.length === 0) {
    uiStore.warning('Selected permissions are system-protected and cannot be deleted.')
    return
  }

  uiStore.confirm({
    title: 'Bulk Delete Permissions',
    message: `Are you sure you want to delete ${nonSystemSelected.length} custom permissions?`,
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        for (const id of nonSystemSelected) {
          await permissionsStore.removePermission(id)
        }
        selectedPermissionIds.value = selectedPermissionIds.value.filter(id => !nonSystemSelected.includes(id))
        uiStore.success(`${nonSystemSelected.length} permissions deleted.`)
      } catch (err: any) {
        uiStore.error(err.message || t('common.deleteFailed'))
      }
    },
  })
}

// Matrix toggle state
const togglingMap = ref<Record<string, boolean>>({})

function getCellKey(roleName: string, permKey: string): string {
  return `${roleName}:${permKey}`
}

function isCellToggling(roleName: string, permKey: string): boolean {
  return !!togglingMap.value[getCellKey(roleName, permKey)]
}

function isRoleAllowed(roleName: string, permKey: string): boolean {
  return permissionsStore.isPermissionAllowed(roleName, permKey)
}

async function handleMatrixToggle(roleName: string, permKey: string) {
  const cellKey = getCellKey(roleName, permKey)
  if (togglingMap.value[cellKey]) return

  togglingMap.value[cellKey] = true
  try {
    const isNowAllowed = await permissionsStore.toggleRolePermission(roleName, permKey)
    uiStore.success(isNowAllowed ? `${roleName} → +${permKey}` : `${roleName} → -${permKey}`)
  } catch (err: any) {
    uiStore.error(err.response?.data?.message || err.message || t('common.saveFailed'))
  } finally {
    togglingMap.value[cellKey] = false
  }
}

onMounted(() => {
  permissionsStore.fetchDbPermissions(true)
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeGroupMenu">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <span
            class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/20 text-[#0B5D3B] dark:text-[#75bd97]"
          >
            <ShieldCheck class="h-3.5 w-3.5" />
            {{ t('admin.permissions.badge') }}
          </span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
          {{ t('admin.permissions.title') }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
          {{ t('admin.permissions.subtitle') }}
        </p>
      </div>

      <div class="flex items-center flex-wrap gap-2">
        <!-- View Mode Switcher -->
        <div class="inline-flex rounded-xl bg-slate-100 dark:bg-slate-800 p-1 border border-slate-200/80 dark:border-slate-700">
          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1.5 cursor-pointer',
              activeTab === 'master-detail'
                ? 'bg-white dark:bg-[#111827] text-[#0B5D3B] dark:text-[#75bd97] shadow-2xs'
                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
            ]"
            @click="activeTab = 'master-detail'"
          >
            <Layers class="h-3.5 w-3.5" />
            {{ t('admin.permissions.tabGroups') }}
          </button>

          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1.5 cursor-pointer',
              activeTab === 'table'
                ? 'bg-white dark:bg-[#111827] text-[#0B5D3B] dark:text-[#75bd97] shadow-2xs'
                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
            ]"
            @click="activeTab = 'table'"
          >
            <TableIcon class="h-3.5 w-3.5" />
            {{ t('admin.permissions.tabTable') }}
          </button>

          <button
            type="button"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-bold transition-colors inline-flex items-center gap-1.5 cursor-pointer',
              activeTab === 'matrix'
                ? 'bg-white dark:bg-[#111827] text-[#0B5D3B] dark:text-[#75bd97] shadow-2xs'
                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
            ]"
            @click="activeTab = 'matrix'"
          >
            <ShieldCheck class="h-3.5 w-3.5" />
            {{ t('admin.permissions.tabMatrix') }}
          </button>
        </div>

        <AppButton
          variant="secondary"
          size="sm"
          :loading="permissionsStore.refreshing"
          @click="permissionsStore.fetchDbPermissions(true)"
        >
          <template #icon-left>
            <RotateCcw class="h-3.5 w-3.5 mr-1" />
          </template>
          {{ t('common.refresh') }}
        </AppButton>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 1: FLAGSHIP MASTER-DETAIL VIEW (Matching Quality Reference Screenshot) -->
    <!-- ========================================================================= -->
    <div v-if="activeTab === 'master-detail'" class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
      <!-- LEFT MASTER PANEL: Permission Group Sidebar List (4 cols) -->
      <div
        class="lg:col-span-4 bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs p-4 space-y-4"
      >
        <!-- Panel Header -->
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-2">
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
              {{ t('admin.permissionGroups.group') }}
            </h2>
            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
              {{ groups.length }}
            </span>
          </div>

          <AppButton variant="secondary" size="xs" @click="openCreateGroupModal">
            <template #icon-left>
              <Plus class="h-3.5 w-3.5 mr-1 text-[#0B5D3B] dark:text-[#75bd97]" />
            </template>
            {{ t('admin.permissionGroups.addGroup') }}
          </AppButton>
        </div>

        <!-- Search Groups Input -->
        <div>
          <AppInput
            id="group-search"
            :placeholder="t('common.searchPlaceholder')"
            :model-value="groupListSearchQuery"
            class="w-full text-xs"
            @update:model-value="groupListSearchQuery = $event"
          >
            <template #icon-left>
              <Search class="h-3.5 w-3.5 text-slate-400" />
            </template>
          </AppInput>
        </div>

        <!-- Groups List (Scrollable) -->
        <div class="max-h-[580px] overflow-y-auto space-y-1 pr-1 custom-scrollbar">
          <div v-if="filteredMasterGroups.length === 0" class="py-8 text-center text-xs text-slate-400 dark:text-slate-500">
            {{ t('admin.permissionGroups.emptyTitle') }}
          </div>

          <div
            v-for="group in filteredMasterGroups"
            :key="group.id"
            :class="[
              'group relative flex items-center justify-between p-3 rounded-xl cursor-pointer transition-all duration-150 border',
              currentGroup?.id === group.id
                ? 'bg-[#E8F4EE] dark:bg-[#153C2D]/60 border-[#0B5D3B]/40 text-[#0B5D3B] dark:text-[#75bd97] shadow-xs'
                : 'bg-transparent border-transparent hover:bg-slate-50 dark:hover:bg-slate-800/60 text-slate-700 dark:text-slate-200',
            ]"
            @click="selectedMasterGroupId = group.id; groupPermPage = 1"
          >
            <!-- Left Info -->
            <div class="min-w-0 pr-2">
              <span class="text-xs font-bold block truncate">
                {{ currentLocale === 'am' && group.display_name_am ? group.display_name_am : group.display_name }}
              </span>
              <span
                v-if="group.description"
                class="text-[10px] font-normal text-slate-400 dark:text-slate-500 block truncate mt-0.5 max-w-[200px]"
              >
                {{ group.description }}
              </span>
            </div>

            <!-- Right Badge & 3-Dots Menu -->
            <div class="flex items-center gap-2 shrink-0">
              <span
                :class="[
                  'px-2 py-0.5 rounded-md text-[11px] font-bold',
                  currentGroup?.id === group.id
                    ? 'bg-[#0B5D3B] text-white'
                    : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400',
                ]"
              >
                {{ getGroupPermCount(group) }}
              </span>

              <!-- 3-Dots Action Button -->
              <div class="relative">
                <button
                  type="button"
                  class="p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-200/60 dark:hover:bg-slate-700/60 transition-colors cursor-pointer dark:text-slate-300"
                  @click="toggleGroupMenu(group.id, $event)"
                >
                  <MoreVertical class="h-3.5 w-3.5" />
                </button>

                <!-- Context Dropdown Menu -->
                <div
                  v-if="activeGroupMenuId === group.id"
                  class="absolute right-0 top-full mt-1 w-36 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg py-1 z-30 text-xs"
                  @click.stop
                >
                  <button
                    type="button"
                    class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer"
                    @click="openEditGroupModal(group)"
                  >
                    <Edit2 class="h-3 w-3 text-slate-400" />
                    {{ t('common.edit') }}
                  </button>
                  <button
                    type="button"
                    class="w-full text-left px-3 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer"
                    @click="handleToggleGroupActive(group)"
                  >
                    {{ group.is_active ? t('common.deactivate') : t('common.activate') }}
                  </button>
                  <button
                    v-if="!group.is_system"
                    type="button"
                    class="w-full text-left px-3 py-1.5 hover:bg-red-50 dark:hover:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center gap-1.5 cursor-pointer"
                    @click="handleDeleteGroup(group)"
                  >
                    <Trash2 class="h-3 w-3" />
                    {{ t('common.delete') }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT DETAIL PANEL: Group Details & Permissions Grid (8 cols) -->
      <div
        v-if="currentGroup"
        class="lg:col-span-8 bg-white dark:bg-[#111827] rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xs p-5 space-y-5"
      >
        <!-- Group Detail Header Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-3.5">
            <!-- Group Avatar Circle with Initials -->
            <div
              class="h-12 w-12 rounded-2xl bg-linear-to-br from-[#0B5D3B] to-[#153C2D] text-white flex items-center justify-center font-black text-sm tracking-wider shadow-sm shrink-0"
            >
              {{ getInitials(currentGroup.display_name) }}
            </div>

            <div>
              <div class="flex items-center gap-2">
                <h2 class="text-base font-black text-slate-900 dark:text-white">
                  {{ currentLocale === 'am' && currentGroup.display_name_am ? currentGroup.display_name_am : currentGroup.display_name }}
                </h2>
                <AppBadge :variant="currentGroup.is_system ? 'info' : 'default'" size="sm">
                  {{ currentGroup.is_system ? t('admin.permissions.system') : t('admin.permissions.custom') }}
                </AppBadge>
              </div>
              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                {{ currentGroupPermissions.length }} {{ t('admin.permissions.permissions') }} Created
                <span v-if="currentGroup.description" class="hidden md:inline font-normal text-slate-400">
                  · {{ currentGroup.description }}
                </span>
              </p>
            </div>
          </div>

          <div class="flex items-center gap-2 self-end sm:self-auto">
            <AppButton variant="ghost" size="xs" @click="openEditGroupModal(currentGroup)">
              <template #icon-left>
                <Edit2 class="h-3.5 w-3.5 mr-1" />
              </template>
              {{ t('common.edit') }}
            </AppButton>
          </div>
        </div>

        <!-- Action & Search Toolbar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          <!-- Left: Select All Checkbox -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-[#0B5D3B] dark:hover:text-[#75bd97] cursor-pointer"
              @click="toggleSelectAllCurrentPage"
            >
              <CheckSquare v-if="isAllCurrentPageSelected" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
              <Square v-else class="h-4 w-4 text-slate-400" />
              <span>Select All</span>
            </button>

            <!-- Bulk actions pill -->
            <div v-if="selectedPermissionIds.length > 0" class="flex items-center gap-1.5 ml-2">
              <span class="text-[11px] font-bold text-[#0B5D3B] dark:text-[#75bd97] bg-[#E8F4EE] dark:bg-[#153C2D] px-2 py-0.5 rounded-md">
                {{ selectedPermissionIds.length }} selected
              </span>
              <AppButton variant="ghost" size="xs" @click="handleBulkToggleStatus(true)">
                Activate
              </AppButton>
              <AppButton variant="ghost" size="xs" @click="handleBulkToggleStatus(false)">
                Deactivate
              </AppButton>
              <AppButton variant="danger" size="xs" @click="handleBulkDelete">
                Delete
              </AppButton>
            </div>
          </div>

          <!-- Right: Search, Filter & Add Permission -->
          <div class="flex items-center flex-wrap gap-2">
            <div class="w-48 sm:w-56">
              <AppInput
                id="group-perm-search"
                :placeholder="t('admin.permissions.searchPlaceholder')"
                :model-value="groupPermSearchQuery"
                class="w-full text-xs"
                @update:model-value="groupPermSearchQuery = $event; groupPermPage = 1"
              >
                <template #icon-left>
                  <Search class="h-3.5 w-3.5 text-slate-400" />
                </template>
              </AppInput>
            </div>

            <!-- Filter Status Dropdown -->
            <select
              v-model="groupPermStatusFilter"
              class="h-9 px-2.5 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
              @change="groupPermPage = 1"
            >
              <option value="all">All Status</option>
              <option value="active">Active Only</option>
              <option value="inactive">Inactive Only</option>
              <option value="system">System Core</option>
              <option value="custom">Custom</option>
            </select>

            <AppButton variant="primary" size="sm" @click="openCreatePermModal(currentGroup.id)">
              <template #icon-left>
                <Plus class="h-3.5 w-3.5 mr-1" />
              </template>
              {{ t('admin.permissions.addPermission') }}
            </AppButton>
          </div>
        </div>

        <!-- 2-Column Responsive Card Grid of Permissions -->
        <div v-if="filteredGroupPermissions.length === 0" class="py-12 text-center rounded-2xl border border-dashed border-slate-200 dark:border-slate-800">
          <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">
            {{ t('admin.permissions.emptyTitle') }}
          </p>
          <AppButton variant="secondary" size="xs" class="mt-3" @click="openCreatePermModal(currentGroup.id)">
            <template #icon-left>
              <Plus class="h-3.5 w-3.5 mr-1 text-[#0B5D3B]" />
            </template>
            {{ t('admin.permissions.createPermission') }}
          </AppButton>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
          <div
            v-for="perm in paginatedGroupPermissions"
            :key="perm.id"
            :class="[
              'p-3.5 rounded-xl border transition-all duration-150 flex items-center justify-between gap-2.5',
              selectedPermissionIds.includes(perm.id)
                ? 'bg-[#E8F4EE]/40 dark:bg-[#153C2D]/30 border-[#0B5D3B]/40 shadow-xs'
                : 'bg-white dark:bg-[#111827] border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700',
            ]"
          >
            <!-- Checkbox & Name -->
            <div class="flex items-center gap-3 min-w-0">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0 cursor-pointer dark:text-slate-400"
                @click="toggleSelectPermission(perm.id)"
              >
                <CheckSquare v-if="selectedPermissionIds.includes(perm.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>

              <div class="min-w-0">
                <span class="text-xs font-bold text-slate-900 dark:text-white block truncate" :title="perm.display_name">
                  {{ currentLocale === 'am' && perm.display_name_am ? perm.display_name_am : perm.display_name }}
                </span>
                <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 block truncate">
                  {{ perm.name }}
                </span>
              </div>
            </div>

            <!-- Status Pill & Actions -->
            <div class="flex items-center gap-1.5 shrink-0">
              <button
                type="button"
                class="cursor-pointer inline-flex items-center"
                @click="handleTogglePermActive(perm)"
              >
                <span
                  :class="[
                    'inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold transition-colors',
                    perm.is_active
                      ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-200'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-200',
                  ]"
                >
                  {{ perm.is_active ? t('common.active') : t('common.inactive') }}
                </span>
              </button>

              <button
                type="button"
                class="p-1 rounded-md text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer dark:text-slate-300"
                :title="t('common.edit')"
                @click="openEditPermModal(perm)"
              >
                <Edit2 class="h-3 w-3" />
              </button>

              <button
                v-if="!perm.is_system"
                type="button"
                class="p-1 rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/50 transition-colors cursor-pointer"
                :title="t('common.delete')"
                @click="handleDeletePermission(perm)"
              >
                <Trash2 class="h-3 w-3" />
              </button>
              <span v-else :title="t('admin.permissions.system')" class="p-1 text-slate-300 dark:text-slate-600">
                <Lock class="h-3 w-3" />
              </span>
            </div>
          </div>
        </div>

        <!-- Footer & Pagination -->
        <div
          v-if="filteredGroupPermissions.length > 0"
          class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 dark:text-slate-400"
        >
          <div>
            Showing {{ Math.min((groupPermPage - 1) * groupPermPerPage + 1, filteredGroupPermissions.length) }} to
            {{ Math.min(groupPermPage * groupPermPerPage, filteredGroupPermissions.length) }} of
            {{ filteredGroupPermissions.length }} entries
          </div>

          <div class="flex items-center gap-3">
            <div class="flex items-center gap-1.5">
              <span>Per Page:</span>
              <select
                v-model="groupPermPerPage"
                class="h-7 px-2 text-xs font-semibold rounded-lg bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 cursor-pointer"
                @change="groupPermPage = 1"
              >
                <option :value="8">8</option>
                <option :value="10">10</option>
                <option :value="16">16</option>
                <option :value="24">24</option>
              </select>
            </div>

            <!-- Page Buttons -->
            <div class="flex items-center gap-1">
              <button
                type="button"
                :disabled="groupPermPage <= 1"
                class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-800 text-xs font-bold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                @click="groupPermPage--"
              >
                &lt; Previous
              </button>

              <span class="px-2.5 py-1 rounded-lg bg-[#0B5D3B] text-white text-xs font-bold">
                {{ groupPermPage }}
              </span>

              <button
                type="button"
                :disabled="groupPermPage >= groupPermTotalPages"
                class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-800 text-xs font-bold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                @click="groupPermPage++"
              >
                Next &gt;
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 2: DATABASE PERMISSIONS TABLE VIEW                                   -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 'table'" class="space-y-4">
      <!-- Category, Group Filter & Search Bar -->
      <div class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs space-y-3">
        <div class="flex flex-wrap items-center gap-1.5 pb-2 border-b border-slate-100 dark:border-slate-800">
          <button
            v-for="cat in categories"
            :key="cat.id"
            type="button"
            :class="[
              'px-3 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer',
              selectedCategory === cat.id
                ? 'bg-[#0B5D3B] text-white shadow-2xs'
                : 'bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700',
            ]"
            @click="selectedCategory = cat.id; permCurrentPage = 1"
          >
            {{ cat.label }}
          </button>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
          <div class="flex-1 max-w-md">
            <AppInput
              id="table-perm-search"
              :placeholder="t('admin.permissions.searchPlaceholder')"
              :model-value="permSearchQuery"
              class="w-full text-xs"
              @update:model-value="permSearchQuery = $event; permCurrentPage = 1"
            >
              <template #icon-left>
                <Search class="h-4 w-4 text-slate-400" />
              </template>
            </AppInput>
          </div>

          <div class="flex items-center flex-wrap gap-2 self-end sm:self-auto">
            <select
              v-model="selectedGroupId"
              class="h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
              @change="permCurrentPage = 1"
            >
              <option value="all">{{ t('admin.permissions.allGroups') }}</option>
              <option v-for="g in groups" :key="g.id" :value="g.id">
                {{ g.display_name }}
              </option>
            </select>

            <AppButton variant="primary" size="sm" @click="openCreatePermModal()">
              <template #icon-left>
                <Plus class="h-3.5 w-3.5 mr-1" />
              </template>
              {{ t('admin.permissions.addPermission') }}
            </AppButton>
          </div>
        </div>
      </div>

      <!-- Permissions Data Table -->
      <AppDataTable
        :columns="permColumns"
        :items="paginatedTablePermissions"
        :loading="permissionsStore.loading"
        :empty-title="t('admin.permissions.emptyTitle')"
        :empty-description="t('admin.permissions.emptyDescription')"
      >
        <template #cell-display_name="{ item }">
          <div class="flex items-center gap-3">
            <div
              :class="[
                'h-8 w-8 rounded-lg flex items-center justify-center font-bold shrink-0',
                item.is_system
                  ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400'
                  : 'bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]',
              ]"
            >
              <Lock v-if="item.is_system" class="h-4 w-4" />
              <ShieldCheck v-else class="h-4 w-4" />
            </div>
            <div class="min-w-0">
              <span class="font-bold text-slate-900 dark:text-white block truncate">
                {{ (currentLocale === 'am' && item.display_name_am) ? item.display_name_am : item.display_name }}
              </span>

              <span class="text-[11px] font-mono text-slate-400 dark:text-slate-500 block truncate">
                {{ item.name }}
              </span>
            </div>
          </div>
        </template>

        <template #cell-permission_group="{ item }">
          <span
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-semibold"
          >
            <Layers class="h-3 w-3 text-[#0B5D3B] dark:text-[#75bd97]" />
            {{ item.permission_group?.display_name || getGroupName(item.permission_group_id) }}
          </span>
        </template>

        <template #cell-category="{ item }">
          <AppBadge variant="default" size="sm" class="capitalize">
            {{ item.category }}
          </AppBadge>
        </template>

        <template #cell-description="{ item }">
          <span class="text-xs text-slate-500 dark:text-slate-400 line-clamp-1 max-w-xs">
            {{ item.description || '—' }}
          </span>
        </template>

        <template #cell-is_system="{ item }">
          <AppBadge :variant="item.is_system ? 'info' : 'default'" size="sm">
            {{ item.is_system ? t('admin.permissions.system') : t('admin.permissions.custom') }}
          </AppBadge>
        </template>

        <template #cell-is_active="{ item }">
          <button
            type="button"
            class="cursor-pointer inline-flex items-center"
            @click="handleTogglePermActive(item as unknown as Permission)"
          >
            <AppBadge :variant="item.is_active ? 'success' : 'danger'" size="sm">
              {{ item.is_active ? t('common.active') : t('common.inactive') }}
            </AppBadge>
          </button>
        </template>

        <template #cell-actions="{ item }">
          <div class="flex items-center justify-end gap-1">
            <AppButton
              variant="ghost"
              size="xs"
              :title="t('common.edit')"
              @click="openEditPermModal(item as unknown as Permission)"
            >
              <Edit2 class="h-3.5 w-3.5" />
            </AppButton>
            <AppButton
              v-if="!item.is_system"
              variant="ghost"
              size="xs"
              class="text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-950/40"
              :title="t('common.delete')"
              @click="handleDeletePermission(item as unknown as Permission)"
            >
              <Trash2 class="h-3.5 w-3.5" />
            </AppButton>
          </div>
        </template>
      </AppDataTable>

      <AppPagination
        v-if="filteredTablePermissions.length > permPerPage"
        :current-page="permCurrentPage"
        :per-page="permPerPage"
        :total-items="filteredTablePermissions.length"
        @update:current-page="permCurrentPage = $event"
        @update:per-page="permPerPage = $event; permCurrentPage = 1"
      />
    </div>

    <!-- ========================================================================= -->
    <!-- VIEW 3: ROLE PERMISSIONS MATRIX VIEW                                      -->
    <!-- ========================================================================= -->
    <div v-else-if="activeTab === 'matrix'" class="space-y-4">
      <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex items-center justify-between">
          <div>
            <h2 class="text-sm font-bold text-slate-900 dark:text-white">
              {{ t('admin.permissions.matrixView') }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Live role permission binding across student, staff, and admin tiers.
            </p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-xs text-left">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/80">
                <th class="px-4 py-3 font-bold text-slate-700 dark:text-slate-300 min-w-[240px]">
                  {{ t('admin.permissions.permission') }}
                </th>
                <th
                  v-for="role in permissionsStore.roles"
                  :key="role.id"
                  class="px-4 py-3 font-bold text-center text-slate-700 dark:text-slate-300 min-w-[120px]"
                >
                  <span class="block text-xs font-bold text-slate-900 dark:text-white">
                    {{ currentLocale === 'am' && role.display_name_am ? role.display_name_am : role.display_name }}
                  </span>
                  <span class="block text-[10px] font-mono text-slate-400 font-normal uppercase">
                    {{ role.name }}
                  </span>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="perm in allPermissions"
                :key="perm.id"
                class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors"
              >
                <td class="px-4 py-2.5">
                  <span class="font-bold text-slate-900 dark:text-white block">
                    {{ currentLocale === 'am' && perm.display_name_am ? perm.display_name_am : perm.display_name }}
                  </span>
                  <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 block">
                    {{ perm.name }}
                  </span>
                </td>
                <td
                  v-for="role in permissionsStore.roles"
                  :key="role.id"
                  class="px-4 py-2.5 text-center"
                >
                  <button
                    type="button"
                    :disabled="isCellToggling(role.name, perm.name)"
                    :class="[
                      'h-6 w-6 rounded-md inline-flex items-center justify-center transition-all cursor-pointer',
                      isRoleAllowed(role.name, perm.name)
                        ? 'bg-[#0B5D3B] text-white shadow-2xs hover:bg-[#0B5D3B]/90'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700',
                    ]"
                    @click="handleMatrixToggle(role.name, perm.name)"
                  >
                    <CheckCircle2 v-if="isRoleAllowed(role.name, perm.name)" class="h-3.5 w-3.5" />
                    <span v-else class="text-[10px] font-bold">—</span>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: ADD / EDIT PERMISSION GROUP                                       -->
    <!-- ========================================================================= -->
    <AppModal
      v-model:open="isGroupModalOpen"
      :title="groupModalMode === 'create' ? t('admin.permissionGroups.addGroupTitle') : t('admin.permissionGroups.editGroupTitle')"
      max-width="md"
    >
      <div class="space-y-4 py-2">
        <AppInput
          id="modal-group-display"
          :label="t('admin.permissionGroups.groupName') + ' *'"
          :placeholder="t('admin.permissionGroups.namePlaceholder')"
          :model-value="groupForm.display_name"
          required
          @update:model-value="groupForm.display_name = $event"
        />

        <AppInput
          id="modal-group-display-am"
          :label="t('admin.permissionGroups.groupNameAm')"
          :placeholder="t('admin.permissionGroups.nameAmPlaceholder')"
          :model-value="groupForm.display_name_am"
          @update:model-value="groupForm.display_name_am = $event"
        />

        <AppInput
          id="modal-group-key"
          :label="t('admin.permissionGroups.groupKey')"
          :placeholder="t('admin.permissionGroups.keyPlaceholder')"
          :model-value="groupForm.name"
          :disabled="groupForm.is_system"
          @update:model-value="groupForm.name = $event"
        />

        <AppTextarea
          id="modal-group-desc"
          :label="t('common.description')"
          :placeholder="t('admin.permissionGroups.descPlaceholder')"
          :model-value="groupForm.description"
          @update:model-value="groupForm.description = $event"
        />

        <div class="pt-2">
          <AppCheckbox
            id="modal-group-active"
            :label="t('admin.permissionGroups.isActiveGroup')"
            :model-value="groupForm.is_active"
            @update:model-value="groupForm.is_active = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isGroupModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmittingGroup" @click="handleSaveGroup">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- ========================================================================= -->
    <!-- MODAL: ADD / EDIT PERMISSION                                             -->
    <!-- ========================================================================= -->
    <AppModal
      v-model:open="isPermModalOpen"
      :title="permModalMode === 'create' ? t('admin.permissions.addPermissionTitle') : t('admin.permissions.editPermissionTitle')"
      max-width="lg"
    >
      <div class="space-y-4 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.permissions.group') }} *
            </label>
            <select
              v-model="permForm.permission_group_id"
              class="w-full h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
            >
              <option v-for="g in groups" :key="g.id" :value="g.id">
                {{ g.display_name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.permissions.category') }} *
            </label>
            <select
              v-model="permForm.category"
              class="w-full h-10 px-3 text-xs font-semibold rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B] cursor-pointer"
            >
              <option value="items">{{ t('admin.permissions.categoryItems') }}</option>
              <option value="claims">{{ t('admin.permissions.categoryClaims') }}</option>
              <option value="custody">{{ t('admin.permissions.categoryCustody') }}</option>
              <option value="admin">{{ t('admin.permissions.categoryAdmin') }}</option>
            </select>
          </div>
        </div>

        <AppInput
          id="modal-perm-display"
          :label="t('admin.permissions.permissionName') + ' *'"
          :placeholder="t('admin.permissions.namePlaceholder')"
          :model-value="permForm.display_name"
          required
          @update:model-value="permForm.display_name = $event"
        />

        <AppInput
          id="modal-perm-display-am"
          :label="t('admin.permissions.nameAm')"
          :placeholder="t('admin.permissions.nameAmPlaceholder')"
          :model-value="permForm.display_name_am"
          @update:model-value="permForm.display_name_am = $event"
        />

        <AppInput
          id="modal-perm-key"
          :label="t('admin.permissions.permissionKey')"
          :placeholder="t('admin.permissions.keyPlaceholder')"
          :model-value="permForm.name"
          :disabled="permForm.is_system"
          @update:model-value="permForm.name = $event"
        />

        <AppTextarea
          id="modal-perm-desc"
          :label="t('common.description')"
          :placeholder="t('admin.permissions.descPlaceholder')"
          :model-value="permForm.description"
          @update:model-value="permForm.description = $event"
        />

        <div class="pt-2">
          <AppCheckbox
            id="modal-perm-active"
            :label="t('admin.permissions.isActivePermission')"
            :model-value="permForm.is_active"
            @update:model-value="permForm.is_active = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isPermModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmittingPerm" @click="handleSavePermission">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 5px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(150, 150, 150, 0.25);
  border-radius: 9999px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(150, 150, 150, 0.4);
}
</style>
