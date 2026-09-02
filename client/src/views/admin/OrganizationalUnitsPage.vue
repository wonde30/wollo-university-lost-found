<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { currentLocale, t } from '@/i18n'
import * as adminApi from '@/features/admin/api/admin.api'
import type { OrganizationalUnit, OrganizationalUnitType, Campus } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Building2,
  Building,
  Layers,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Edit2,
  Trash2,
  CheckCircle2,
  FolderTree,
  ListTree,
  Tag,
  CheckSquare,
  Square,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

// Navigation Tabs
const activeTab = ref<'units' | 'types'>('units')

// State for Units
const units = ref<OrganizationalUnit[]>([])
const unitTypes = ref<OrganizationalUnitType[]>([])
const loading = ref(false)
const isRefreshing = ref(false)
const campuses = computed<Campus[]>(() => referencesStore.campuses)

// Unit Filters & Search
const showFilters = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')
const selectedCampusId = ref<string>('all')
const selectedTypeId = ref<string>('all')
const selectedStatus = ref<'all' | 'active' | 'inactive'>('all')

// Selected IDs for Bulk Ops
const selectedUnitIds = ref<number[]>([])

// Unit Modal State
const isUnitModalOpen = ref(false)
const unitModalMode = ref<'create' | 'edit'>('create')
const editingUnitId = ref<number | null>(null)
const isSubmittingUnit = ref(false)

const unitForm = reactive({
  campus_id: '' as number | '',
  type_id: '' as number | '',
  parent_id: '' as number | '',
  name: '',
  name_am: '',
  short_code: '',
  description: '',
  is_active: true,
})

// Type Modal State
const isTypeModalOpen = ref(false)
const typeModalMode = ref<'create' | 'edit'>('create')
const editingTypeId = ref<number | null>(null)
const isSubmittingType = ref(false)

const typeForm = reactive({
  code: '',
  name: '',
  name_am: '',
  description: '',
  is_root: false,
  is_active: true,
})

// Unit Type Filter Search
const typeSearchQuery = ref('')

// Load Data
async function loadData(force = false): Promise<void> {
  loading.value = true
  try {
    const [, unitsRes, typesRes] = await Promise.all([
      referencesStore.fetchCampuses(force),
      referencesStore.fetchOrganizationalUnits(force),
      referencesStore.fetchOrganizationalUnitTypes(force),
    ])


    units.value = unitsRes || []
    unitTypes.value = typesRes || []
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to load organizational structure data'))
  } finally {
    loading.value = false
    isRefreshing.value = false
  }
}

async function handleRefresh(): Promise<void> {
  isRefreshing.value = true
  await loadData(true)
  uiStore.info(t('common.refreshed') || 'Data refreshed')
}

onMounted(() => {
  loadData(true)
})

// Metrics
const totalUnitsCount = computed(() => units.value.length)
const activeUnitsCount = computed(() => units.value.filter(u => u.is_active).length)
const rootUnitsCount = computed(() => units.value.filter(u => !u.parent_id || u.type?.code === 'college' || u.type?.code === 'institute' || u.type?.code === 'division').length)
const childUnitsCount = computed(() => units.value.filter(u => !!u.parent_id || u.type?.code === 'department' || u.type?.code === 'school').length)

// Unit Filter Options
const campusFilterOptions = computed(() => [
  { value: 'all', label: t('admin.campuses.all') || 'All Campuses' },
  ...campuses.value.map(c => ({ value: String(c.id), label: c.name })),
])

const typeFilterOptions = computed(() => [
  { value: 'all', label: t('admin.units.allTypes') || 'All Unit Types' },
  ...unitTypes.value.map(t => ({
    value: String(t.id),
    label: (currentLocale.value === 'am' && t.name_am) ? t.name_am : t.name,
  })),
])

const statusFilterOptions = [
  { value: 'all', label: t('common.all') || 'All Status' },
  { value: 'active', label: t('common.active') || 'Active' },
  { value: 'inactive', label: t('common.inactive') || 'Inactive' },
]

// Modal Selection Options
const campusSelectOptions = computed(() =>
  campuses.value.map(c => ({ value: c.id, label: c.name }))
)

const unitTypeSelectOptions = computed(() =>
  unitTypes.value.map(t => ({
    value: t.id,
    label: (currentLocale.value === 'am' && t.name_am) ? `${t.name_am} (${t.name})` : t.name,
  }))
)

const potentialParents = computed(() => {
  if (!unitForm.campus_id) return []
  return units.value
    .filter(u => u.campus_id === Number(unitForm.campus_id) && (!editingUnitId.value || u.id !== editingUnitId.value))
    .map(u => ({
      value: u.id,
      label: `${u.name} (${u.type?.name || 'Unit'})`,
    }))
})

// Filtered Units
const filteredUnits = computed(() => {
  let list = units.value

  if (selectedCampusId.value !== 'all') {
    const cId = Number(selectedCampusId.value)
    list = list.filter(u => u.campus_id === cId)
  }

  if (selectedTypeId.value !== 'all') {
    const tId = Number(selectedTypeId.value)
    list = list.filter(u => u.type_id === tId || u.type?.id === tId)
  }

  if (selectedStatus.value === 'active') {
    list = list.filter(u => u.is_active)
  } else if (selectedStatus.value === 'inactive') {
    list = list.filter(u => !u.is_active)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      u =>
        u.name.toLowerCase().includes(query) ||
        (u.name_am && u.name_am.toLowerCase().includes(query)) ||
        (u.short_code && u.short_code.toLowerCase().includes(query)) ||
        (u.campus?.name && u.campus.name.toLowerCase().includes(query)) ||
        (u.type?.name && u.type.name.toLowerCase().includes(query))
    )
  }

  return list
})

// Filtered Types
const filteredUnitTypes = computed(() => {
  let list = unitTypes.value
  const query = typeSearchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      t =>
        t.name.toLowerCase().includes(query) ||
        t.code.toLowerCase().includes(query) ||
        (t.name_am && t.name_am.toLowerCase().includes(query))
    )
  }
  return list
})

// Pagination
const totalPages = computed(() => Math.ceil(filteredUnits.value.length / perPage.value) || 1)
const paginatedUnits = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredUnits.value.slice(start, start + perPage.value)
})

// Multi-Selection
const isAllSelected = computed(
  () => paginatedUnits.value.length > 0 && paginatedUnits.value.every(u => selectedUnitIds.value.includes(u.id))
)

function toggleSelectAll(): void {
  if (isAllSelected.value) {
    const pageIds = paginatedUnits.value.map(u => u.id)
    selectedUnitIds.value = selectedUnitIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedUnits.value.map(u => u.id)
    selectedUnitIds.value = Array.from(new Set([...selectedUnitIds.value, ...pageIds]))
  }
}

function toggleSelectUnit(id: number): void {
  const index = selectedUnitIds.value.indexOf(id)
  if (index === -1) {
    selectedUnitIds.value.push(id)
  } else {
    selectedUnitIds.value.splice(index, 1)
  }
}

// Unit Modal Actions
function openCreateUnitModal(): void {
  unitModalMode.value = 'create'
  editingUnitId.value = null
  unitForm.campus_id = campuses.value[0]?.id || ''
  unitForm.type_id = unitTypes.value[0]?.id || ''
  unitForm.parent_id = ''
  unitForm.name = ''
  unitForm.name_am = ''
  unitForm.short_code = ''
  unitForm.description = ''
  unitForm.is_active = true
  isUnitModalOpen.value = true
}

function openEditUnitModal(unit: OrganizationalUnit): void {
  unitModalMode.value = 'edit'
  editingUnitId.value = unit.id
  unitForm.campus_id = unit.campus_id
  unitForm.type_id = unit.type_id || unit.type?.id || (unitTypes.value[0]?.id || '')
  unitForm.parent_id = unit.parent_id || ''
  unitForm.name = unit.name
  unitForm.name_am = unit.name_am || ''
  unitForm.short_code = unit.short_code || ''
  unitForm.description = unit.description || ''
  unitForm.is_active = unit.is_active
  isUnitModalOpen.value = true
}

async function handleUnitSubmit(): Promise<void> {
  if (!unitForm.name.trim() || !unitForm.short_code.trim() || !unitForm.campus_id || !unitForm.type_id) {
    uiStore.error(t('validation.requiredFields') || 'Please fill in all required fields.')
    return
  }

  isSubmittingUnit.value = true
  try {
    const payload = {
      campus_id: Number(unitForm.campus_id),
      type_id: Number(unitForm.type_id),
      parent_id: unitForm.parent_id ? Number(unitForm.parent_id) : null,
      name: unitForm.name.trim(),
      name_am: unitForm.name_am?.trim() || null,
      short_code: unitForm.short_code.trim().toUpperCase(),
      description: unitForm.description?.trim() || null,
      is_active: unitForm.is_active,
    }

    if (unitModalMode.value === 'create') {
      const created = await adminApi.createOrganizationalUnit(payload)
      units.value.unshift(created)
      uiStore.success(t('admin.units.created') || 'Organizational unit created successfully')
    } else if (editingUnitId.value) {
      const updated = await adminApi.updateOrganizationalUnit(editingUnitId.value, payload)
      const idx = units.value.findIndex(u => u.id === editingUnitId.value)
      if (idx !== -1) units.value[idx] = updated
      uiStore.success(t('admin.units.updated') || 'Organizational unit updated successfully')
    }

    isUnitModalOpen.value = false
    referencesStore.invalidate()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to save organizational unit'))
  } finally {
    isSubmittingUnit.value = false
  }
}

function handleDeleteUnit(unit: OrganizationalUnit): void {
  uiStore.confirm({
    title: t('admin.units.deleteTitle') || 'Delete Unit',
    message: t('admin.units.deleteConfirm') || `Are you sure you want to delete "${unit.name}"?`,
    confirmText: t('common.delete') || 'Delete',
    variant: 'danger',
    onConfirm: async () => {
      try {
        await adminApi.deleteOrganizationalUnit(unit.id)
        units.value = units.value.filter(u => u.id !== unit.id)
        selectedUnitIds.value = selectedUnitIds.value.filter(id => id !== unit.id)
        uiStore.success(t('admin.units.deleted') || 'Organizational unit deleted successfully')
        referencesStore.invalidate()
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete organizational unit'))
      }
    },
  })
}

async function handleBulkToggleStatus(makeActive: boolean): Promise<void> {
  if (selectedUnitIds.value.length === 0) return
  const ids = [...selectedUnitIds.value]
  try {
    for (const id of ids) {
      const unit = units.value.find(u => u.id === id)
      if (unit) {
        await adminApi.updateOrganizationalUnit(id, { ...unit, is_active: makeActive })
        unit.is_active = makeActive
      }
    }
    uiStore.success(`${ids.length} units updated.`)
    referencesStore.invalidate()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update units'))
  }
}

async function handleBulkDelete(): Promise<void> {
  if (selectedUnitIds.value.length === 0) return
  const ids = [...selectedUnitIds.value]
  uiStore.confirm({
    title: t('admin.units.deleteTitle') || 'Delete Units',
    message: `Are you sure you want to delete ${ids.length} selected organizational units?`,
    confirmText: t('common.delete') || 'Delete',
    variant: 'danger',
    onConfirm: async () => {
      try {
        for (const id of ids) {
          await adminApi.deleteOrganizationalUnit(id)
        }
        units.value = units.value.filter(u => !ids.includes(u.id))
        selectedUnitIds.value = []
        uiStore.success(`${ids.length} units deleted successfully.`)
        referencesStore.invalidate()
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete selected units'))
      }
    },
  })
}

async function handleToggleUnitActive(unit: OrganizationalUnit): Promise<void> {
  try {
    const updated = await adminApi.updateOrganizationalUnit(unit.id, {
      ...unit,
      is_active: !unit.is_active,
    })
    const idx = units.value.findIndex(u => u.id === unit.id)
    if (idx !== -1) units.value[idx] = updated
    uiStore.success(updated.is_active ? 'Unit activated' : 'Unit deactivated')
    referencesStore.invalidate()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update status'))
  }
}

// Unit Type Modal Actions
function openCreateTypeModal(): void {
  typeModalMode.value = 'create'
  editingTypeId.value = null
  typeForm.code = ''
  typeForm.name = ''
  typeForm.name_am = ''
  typeForm.description = ''
  typeForm.is_root = false
  typeForm.is_active = true
  isTypeModalOpen.value = true
}

function openEditTypeModal(type: OrganizationalUnitType): void {
  typeModalMode.value = 'edit'
  editingTypeId.value = type.id
  typeForm.code = type.code
  typeForm.name = type.name
  typeForm.name_am = type.name_am || ''
  typeForm.description = type.description || ''
  typeForm.is_root = !!type.is_root
  typeForm.is_active = type.is_active !== false
  isTypeModalOpen.value = true
}

async function handleTypeSubmit(): Promise<void> {
  if (!typeForm.code.trim() || !typeForm.name.trim()) {
    uiStore.error('Please enter type code and name.')
    return
  }

  isSubmittingType.value = true
  try {
    const payload = {
      code: typeForm.code.trim().toLowerCase(),
      name: typeForm.name.trim(),
      name_am: typeForm.name_am?.trim() || null,
      description: typeForm.description?.trim() || null,
      is_root: typeForm.is_root,
      is_active: typeForm.is_active,
    }

    if (typeModalMode.value === 'create') {
      const created = await adminApi.createOrganizationalUnitType(payload)
      unitTypes.value.unshift(created)
      uiStore.success('Unit type created successfully')
    } else if (editingTypeId.value) {
      const updated = await adminApi.updateOrganizationalUnitType(editingTypeId.value, payload)
      const idx = unitTypes.value.findIndex(t => t.id === editingTypeId.value)
      if (idx !== -1) unitTypes.value[idx] = updated
      uiStore.success('Unit type updated successfully')
    }

    isTypeModalOpen.value = false
    referencesStore.invalidate()
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to save unit type'))
  } finally {
    isSubmittingType.value = false
  }
}

function handleDeleteType(type: OrganizationalUnitType): void {
  uiStore.confirm({
    title: 'Delete Unit Type',
    message: `Are you sure you want to delete unit type "${type.name}"?`,
    confirmText: t('common.delete') || 'Delete',
    variant: 'danger',
    onConfirm: async () => {
      try {
        await adminApi.deleteOrganizationalUnitType(type.id)
        unitTypes.value = unitTypes.value.filter(t => t.id !== type.id)
        uiStore.success('Unit type deleted successfully')
        referencesStore.invalidate()
      } catch (err) {
        uiStore.error(getErrorMessage(err, 'Failed to delete unit type'))
      }
    },
  })
}

// Export CSV
function exportUnitsCsv(): void {
  if (units.value.length === 0) {
    uiStore.warning('No data to export')
    return
  }

  const headers = ['ID', 'Campus', 'Type', 'Parent Unit', 'Name', 'Name (AM)', 'Short Code', 'Status']
  const rows = filteredUnits.value.map(u => [
    u.id,
    u.campus?.name || '',
    u.type?.name || '',
    u.parent?.name || 'None',
    `"${u.name.replace(/"/g, '""')}"`,
    `"${(u.name_am || '').replace(/"/g, '""')}"`,
    u.short_code,
    u.is_active ? 'Active' : 'Inactive',
  ])

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `organizational_units_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Export completed')
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white flex items-center gap-2.5">
          <Building2 class="w-7 h-7 text-[#0B5D3B] dark:text-[#75bd97]" />
          {{ t('nav.organizationalUnits') || 'Organizational Structure' }}
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
          {{ t('admin.units.subtitle') }}
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2">
        <button
          type="button"
          :title="t('common.export')"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer"
          @click="exportUnitsCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <button
          type="button"
          :title="t('common.refresh')"
          :disabled="isRefreshing || loading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || loading ? 'animate-spin' : ''" />
        </button>

        <AppButton
          v-if="activeTab === 'units'"
          variant="primary"
          size="md"
          @click="openCreateUnitModal"
        >
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.units.addUnit') }}
        </AppButton>

        <AppButton
          v-else
          variant="primary"
          size="md"
          @click="openCreateTypeModal"
        >
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.units.addType') }}
        </AppButton>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 dark:border-slate-800">
      <button
        type="button"
        :class="[
          'px-4 py-2.5 text-sm font-bold border-b-2 transition-colors flex items-center gap-2 cursor-pointer',
          activeTab === 'units'
            ? 'border-[#0B5D3B] text-[#0B5D3B] dark:text-[#75bd97] dark:border-[#75bd97] font-extrabold'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300',
        ]"
        @click="activeTab = 'units'"
      >
        <ListTree class="w-4 h-4" />
        {{ t('admin.units.tabUnits') }}
        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 dark:bg-slate-800 font-bold">
          {{ units.length }}
        </span>
      </button>

      <button
        type="button"
        :class="[
          'px-4 py-2.5 text-sm font-bold border-b-2 transition-colors flex items-center gap-2 cursor-pointer',
          activeTab === 'types'
            ? 'border-[#0B5D3B] text-[#0B5D3B] dark:text-[#75bd97] dark:border-[#75bd97] font-extrabold'
            : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-300',
        ]"
        @click="activeTab = 'types'"
      >
        <Tag class="w-4 h-4" />
        {{ t('admin.units.tabTypes') }}
        <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 dark:bg-slate-800 font-bold">
          {{ unitTypes.length }}
        </span>
      </button>
    </div>

    <!-- TAB 1: UNITS -->
    <div v-if="activeTab === 'units'" class="space-y-4">
      <!-- Metric Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
            <span>{{ t('admin.units.totalUnits') }}</span>
            <Building2 class="w-4 h-4 text-[#0B5D3B]" />
          </div>
          <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white mt-1">{{ totalUnitsCount }}</p>
        </div>

        <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
            <span>{{ t('admin.units.activeUnits') }}</span>
            <CheckCircle2 class="w-4 h-4 text-emerald-500" />
          </div>
          <p class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ activeUnitsCount }}</p>
        </div>

        <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
            <span>{{ t('admin.units.rootColleges') }}</span>
            <Layers class="w-4 h-4 text-blue-500" />
          </div>
          <p class="text-2xl sm:text-3xl font-black text-blue-600 dark:text-blue-400 mt-1">{{ rootUnitsCount }}</p>
        </div>

        <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs">
          <div class="flex items-center justify-between text-slate-500 dark:text-slate-400 text-[11px] font-bold uppercase tracking-wider">
            <span>{{ t('admin.units.subUnits') }}</span>
            <Building class="w-4 h-4 text-amber-500" />
          </div>
          <p class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ childUnitsCount }}</p>
        </div>
      </div>

      <!-- Filters & Search Toolbar -->
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs space-y-4">
        <div class="flex flex-col sm:flex-row gap-3 items-center justify-between">
          <div class="relative w-full sm:max-w-md">
            <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="t('admin.units.searchPlaceholder')"
              class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
            />
            <button
              v-if="searchQuery"
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
              @click="searchQuery = ''"
            >
              <X class="w-3.5 h-3.5" />
            </button>
          </div>

          <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <AppButton
              variant="outline"
              size="sm"
              :class="showFilters ? 'bg-slate-100 dark:bg-slate-800' : ''"
              @click="showFilters = !showFilters"
            >
              <Filter class="w-4 h-4 mr-1.5" />
              {{ t('common.filters') || 'Filters' }}
            </AppButton>
          </div>
        </div>

        <!-- Expanded Filter Dropdowns -->
        <div v-if="showFilters" class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">{{ t('admin.units.campus') }}</label>
            <AppSelect
              v-model="selectedCampusId"
              :options="campusFilterOptions"
              class="w-full text-xs"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">{{ t('admin.units.type') }}</label>
            <AppSelect
              v-model="selectedTypeId"
              :options="typeFilterOptions"
              class="w-full text-xs"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1.5">{{ t('common.status') }}</label>
            <AppSelect
              v-model="selectedStatus"
              :options="statusFilterOptions"
              class="w-full text-xs"
            />
          </div>
        </div>
      </div>

      <!-- Units Table -->
      <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs overflow-hidden">
        <!-- Bulk Action Bar -->
        <div
          v-if="selectedUnitIds.length > 0"
          class="flex items-center justify-between p-3 bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-200 dark:border-emerald-800 text-xs font-semibold"
        >
          <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-200">
            <span>{{ selectedUnitIds.length }} {{ t('common.selected') || 'selected' }}</span>
          </div>
          <div class="flex items-center gap-2">
            <AppButton
              variant="outline"
              size="xs"
              @click="handleBulkToggleStatus(true)"
            >
              {{ t('common.activate') || 'Activate' }}
            </AppButton>
            <AppButton
              variant="outline"
              size="xs"
              @click="handleBulkToggleStatus(false)"
            >
              {{ t('common.deactivate') || 'Deactivate' }}
            </AppButton>
            <AppButton
              variant="danger"
              size="xs"
              @click="handleBulkDelete"
            >
              {{ t('common.delete') || 'Delete' }}
            </AppButton>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/75 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 uppercase font-semibold tracking-wider">
                <th class="py-3.5 px-4 w-10">
                  <button type="button" @click="toggleSelectAll">
                    <CheckSquare v-if="isAllSelected" class="w-4 h-4 text-[#0B5D3B]" />
                    <Square v-else class="w-4 h-4 text-slate-400" />
                  </button>
                </th>
                <th class="py-3.5 px-4">{{ t('admin.units.name') }}</th>
                <th class="py-3.5 px-4">{{ t('admin.units.code') }}</th>
                <th class="py-3.5 px-4">{{ t('admin.units.campus') }}</th>
                <th class="py-3.5 px-4">{{ t('admin.units.type') }}</th>
                <th class="py-3.5 px-4">{{ t('admin.units.parent') }}</th>
                <th class="py-3.5 px-4">{{ t('common.status') }}</th>
                <th class="py-3.5 px-4 text-right">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody v-if="loading && units.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr v-for="n in 4" :key="n" class="animate-pulse">
                <td class="py-4 px-4 text-center">
                  <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
                </td>
                <td class="py-4 px-4 space-y-1.5">
                  <div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded" />
                  <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded font-mono" />
                </td>
                <td class="py-4 px-4">
                  <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
                </td>
                <td class="py-4 px-4">
                  <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
                </td>
                <td class="py-4 px-4">
                  <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
                </td>
                <td class="py-4 px-4">
                  <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
                </td>
                <td class="py-4 px-4 text-center">
                  <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
                </td>
              </tr>
            </tbody>

            <tbody v-else-if="paginatedUnits.length === 0">
              <tr>
                <td colspan="8" class="p-12 text-center">
                  <div class="max-w-xs mx-auto space-y-2 text-center">
                    <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                      <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                    </div>
                    <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                      {{ searchQuery || selectedCampusId !== 'all' || selectedTypeId !== 'all' || selectedStatus !== 'all' ? 'No units match your active filters' : 'No organizational units defined' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                      {{ searchQuery || selectedCampusId !== 'all' || selectedTypeId !== 'all' || selectedStatus !== 'all' ? 'Try adjusting your search criteria or hierarchy filter.' : 'Create colleges, faculties, departments, and offices to map university hierarchy.' }}
                    </p>
                    <div v-if="!searchQuery && selectedCampusId === 'all' && selectedTypeId === 'all' && selectedStatus === 'all'" class="pt-2 flex items-center justify-center">
                      <button
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                        @click="openCreateUnitModal"
                      >
                        <Plus class="h-3.5 w-3.5" />
                        <span>Add Organizational Unit</span>
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
            </tbody>

            <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
              <tr
                v-for="unit in paginatedUnits"
                :key="unit.id"
                :class="[
                  'hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors',
                  selectedUnitIds.includes(unit.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
                ]"
              >
                <td class="py-3 px-4">
                  <button type="button" @click="toggleSelectUnit(unit.id)">
                    <CheckSquare v-if="selectedUnitIds.includes(unit.id)" class="w-4 h-4 text-[#0B5D3B]" />
                    <Square v-else class="w-4 h-4 text-slate-400" />
                  </button>
                </td>

                <td class="py-3 px-4">
                  <div class="font-semibold text-slate-900 dark:text-white">
                    {{ (currentLocale === 'am' && unit.name_am) ? unit.name_am : unit.name }}
                  </div>
                </td>

                <td class="py-3 px-4">
                  <span class="px-2 py-0.5 rounded-md font-mono text-[11px] bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold">
                    {{ unit.short_code }}
                  </span>
                </td>

                <td class="py-3 px-4 text-slate-600 dark:text-slate-300">
                  {{ (currentLocale === 'am' && unit.campus?.name_am) ? unit.campus.name_am : (unit.campus?.name || '—') }}
                </td>

                <td class="py-3 px-4">
                  <span class="px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                    {{ (currentLocale === 'am' && unit.type?.name_am) ? unit.type.name_am : (unit.type?.name || 'Unit') }}
                  </span>
                </td>

                <td class="py-3 px-4 text-slate-500 dark:text-slate-400">
                  <div v-if="unit.parent" class="flex items-center gap-1.5">
                    <FolderTree class="w-3.5 h-3.5 text-slate-400" />
                    <span>{{ (currentLocale === 'am' && unit.parent.name_am) ? unit.parent.name_am : unit.parent.name }}</span>
                  </div>
                  <span v-else class="text-slate-400 italic">{{ t('admin.units.rootLevel') }}</span>
                </td>

                <td class="py-3 px-4">
                  <button
                    type="button"
                    :class="[
                      'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors cursor-pointer',
                      unit.is_active
                        ? 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-300 hover:bg-emerald-200'
                        : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200',
                    ]"
                    @click="handleToggleUnitActive(unit)"
                  >
                    {{ unit.is_active ? t('common.active') : t('common.inactive') }}
                  </button>
                </td>

                <td class="py-3 px-4 text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <button
                      type="button"
                      class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                      :title="t('admin.units.editUnit')"
                      @click="openEditUnitModal(unit)"
                    >
                      <Edit2 class="w-3.5 h-3.5" />
                    </button>
                    <button
                      type="button"
                      class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors"
                      :title="t('common.delete')"
                      @click="handleDeleteUnit(unit)"
                    >
                      <Trash2 class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <AppPagination
          :current-page="currentPage"
          :total-pages="totalPages"
          :total="filteredUnits.length"
          :per-page="perPage"
          @update:current-page="currentPage = $event"
          @update:per-page="perPage = $event; currentPage = 1"
        />
      </div>
    </div>

    <!-- TAB 2: UNIT TYPES -->
    <div v-else class="space-y-6">
      <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div class="relative w-full sm:max-w-md">
          <Search class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
          <input
            v-model="typeSearchQuery"
            type="text"
            :placeholder="t('admin.units.searchTypes')"
            class="w-full pl-10 pr-4 py-2 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="uType in filteredUnitTypes"
          :key="uType.id"
          class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xs hover:shadow-md transition-shadow relative overflow-hidden flex flex-col justify-between"
        >
          <div>
            <div class="flex items-center justify-between mb-3">
              <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-bold bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                {{ uType.code }}
              </span>
              <span
                v-if="uType.is_root"
                class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-300"
              >
                {{ t('admin.units.rootLevel') }}
              </span>
            </div>

            <h3 class="text-base font-bold text-slate-900 dark:text-white">
              {{ (currentLocale === 'am' && uType.name_am) ? uType.name_am : uType.name }}
            </h3>

            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2.5 line-clamp-2">
              {{ uType.description || '' }}
            </p>

          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
            <span
              :class="[
                'text-[10px] font-bold uppercase',
                uType.is_active !== false ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400',
              ]"
            >
              {{ uType.is_active !== false ? t('common.active') : t('common.inactive') }}
            </span>

            <div class="flex items-center gap-1.5">
              <button
                type="button"
                class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800"
                :title="t('admin.units.editType')"
                @click="openEditTypeModal(uType)"
              >
                <Edit2 class="w-3.5 h-3.5" />
              </button>
              <button
                type="button"
                class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                :title="t('common.delete')"
                @click="handleDeleteType(uType)"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- MODAL: Create/Edit Organizational Unit -->
    <AppModal
      v-model:open="isUnitModalOpen"
      :title="unitModalMode === 'create' ? (t('admin.units.addUnit') || 'Add Organizational Unit') : 'Edit Organizational Unit'"
    >
      <form class="space-y-4" @submit.prevent="handleUnitSubmit">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Campus *</label>
            <AppSelect
              v-model="unitForm.campus_id"
              :options="campusSelectOptions"
              required
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Unit Type *</label>
            <AppSelect
              v-model="unitForm.type_id"
              :options="unitTypeSelectOptions"
              required
            />
          </div>
        </div>

        <div v-if="potentialParents.length > 0">
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
            Parent Unit (Optional)
          </label>
          <AppSelect
            v-model="unitForm.parent_id"
            :options="[{ value: '', label: 'None (Root Level)' }, ...potentialParents]"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            id="unit-name"
            label="Unit Name (English) *"
            placeholder="e.g. Department of Computer Science"
            :model-value="unitForm.name"
            required
            @update:model-value="unitForm.name = $event"
          />

          <AppInput
            id="unit-code"
            label="Short Code *"
            placeholder="e.g. CS or FOE"
            :model-value="unitForm.short_code"
            required
            @update:model-value="unitForm.short_code = $event"
          />
        </div>

        <AppInput
          id="unit-name-am"
          label="Unit Name (Amharic)"
          placeholder="e.g. የኮምፒውተር ሳይንስ ትምህርት ክፍል"
          :model-value="unitForm.name_am"
          @update:model-value="unitForm.name_am = $event"
        />

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
          <textarea
            v-model="unitForm.description"
            rows="3"
            placeholder="Brief description of the organizational unit..."
            class="w-full p-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
          ></textarea>
        </div>

        <div class="flex items-center gap-2 pt-2">
          <AppCheckbox
            id="unit-is-active"
            :model-value="unitForm.is_active"
            label="Active Unit (Visible across registration & forms)"
            @update:model-value="unitForm.is_active = $event"
          />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <AppButton
            type="button"
            variant="outline"
            size="sm"
            @click="isUnitModalOpen = false"
          >
            {{ t('common.cancel') || 'Cancel' }}
          </AppButton>

          <AppButton
            type="submit"
            variant="primary"
            size="sm"
            :loading="isSubmittingUnit"
          >
            {{ unitModalMode === 'create' ? 'Create Unit' : 'Save Changes' }}
          </AppButton>
        </div>
      </form>
    </AppModal>

    <!-- MODAL: Create/Edit Unit Type -->
    <AppModal
      v-model:open="isTypeModalOpen"
      :title="typeModalMode === 'create' ? (t('admin.units.addType') || 'Add Unit Type') : 'Edit Unit Type'"
    >
      <form class="space-y-4" @submit.prevent="handleTypeSubmit">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <AppInput
            id="type-code"
            label="Type Code *"
            placeholder="e.g. college, department, school"
            :model-value="typeForm.code"
            required
            @update:model-value="typeForm.code = $event"
          />

          <AppInput
            id="type-name"
            label="Type Name (English) *"
            placeholder="e.g. College or Department"
            :model-value="typeForm.name"
            required
            @update:model-value="typeForm.name = $event"
          />
        </div>

        <AppInput
          id="type-name-am"
          label="Type Name (Amharic)"
          placeholder="e.g. ኮሌጅ or ትምህርት ክፍል"
          :model-value="typeForm.name_am"
          @update:model-value="typeForm.name_am = $event"
        />

        <div>
          <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Description</label>
          <textarea
            v-model="typeForm.description"
            rows="2"
            placeholder="Description of this unit level..."
            class="w-full p-3 text-xs rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0B5D3B]"
          ></textarea>
        </div>

        <div class="space-y-2 pt-2">
          <AppCheckbox
            id="type-is-root"
            :model-value="typeForm.is_root"
            label="Is Root Level (Directly under Campus, e.g. College/Division)"
            @update:model-value="typeForm.is_root = $event"
          />

          <AppCheckbox
            id="type-is-active"
            :model-value="typeForm.is_active"
            label="Active Type"
            @update:model-value="typeForm.is_active = $event"
          />
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <AppButton
            type="button"
            variant="outline"
            size="sm"
            @click="isTypeModalOpen = false"
          >
            {{ t('common.cancel') || 'Cancel' }}
          </AppButton>

          <AppButton
            type="submit"
            variant="primary"
            size="sm"
            :loading="isSubmittingType"
          >
            {{ typeModalMode === 'create' ? 'Create Type' : 'Save Changes' }}
          </AppButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
