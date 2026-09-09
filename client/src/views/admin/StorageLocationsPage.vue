<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { currentLocale, t } from '@/i18n'
import { getExportFilename } from '@/stores/settings.store'
import * as custodyApi from '@/features/custody/api/custody.api'
import type { StorageLocation, Campus } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppCheckbox from '@/components/ui/AppCheckbox.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Archive,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Edit2,
  Trash2,
  CheckCircle2,
  CheckSquare,
  Square,
  Package,
  Layers,
  ToggleLeft,
  ToggleRight,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

// State
const storageLocations = ref<StorageLocation[]>([])
const loading = ref(false)
const isRefreshing = ref(false)
const campuses = computed<Campus[]>(() => referencesStore.campuses)

// Filters & Pagination
const showFilters = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')
const selectedCampusId = ref<string>('all')

// Multi-Selection State
const selectedVaultIds = ref<number[]>([])

function closeVaultMenu() {
  // Context menu handled by AppActionMenu
}

// Modal State
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const isSubmitting = ref(false)

const form = reactive({
  campus_id: '' as number | '',
  name: '',
  code: '',
  capacity: 25,
  description: '',
  is_active: true,
})

// Metrics
const totalVaults = computed(() => storageLocations.value.length)
const activeVaults = computed(() => storageLocations.value.filter(s => (s as any).is_active !== false).length)
const totalCapacity = computed(() => storageLocations.value.reduce((acc, s) => acc + (s.capacity || 0), 0))
const totalOccupancy = computed(() => storageLocations.value.reduce((acc, s) => acc + (s.current_occupancy || 0), 0))

// Filtered Storage Locations
const filteredLocations = computed(() => {
  let list = storageLocations.value

  if (selectedCampusId.value !== 'all') {
    const cId = Number(selectedCampusId.value)
    list = list.filter(s => s.campus_id === cId)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      s =>
        s.name.toLowerCase().includes(query) ||
        ((s as any).code && (s as any).code.toLowerCase().includes(query)) ||
        (s.shelf_cabinet_code && s.shelf_cabinet_code.toLowerCase().includes(query)) ||
        (s.building && s.building.toLowerCase().includes(query))
    )
  }

  return list
})

const paginatedLocations = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredLocations.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredLocations.value.length / perPage.value) || 1)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedLocations.value.length === 0) return false
  return paginatedLocations.value.every(s => selectedVaultIds.value.includes(s.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedLocations.value.map(s => s.id)
    selectedVaultIds.value = selectedVaultIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedLocations.value.map(s => s.id)
    const newSelected = new Set([...selectedVaultIds.value, ...pageIds])
    selectedVaultIds.value = Array.from(newSelected)
  }
}

function toggleSelectVault(id: number) {
  const index = selectedVaultIds.value.indexOf(id)
  if (index !== -1) {
    selectedVaultIds.value.splice(index, 1)
  } else {
    selectedVaultIds.value.push(id)
  }
}

const campusFilterOptions = computed(() => {
  const options = [{ label: t('admin.storageLocations.allCampuses'), value: 'all' }]
  campuses.value.forEach(c => {
    options.push({
      label: currentLocale.value === 'am' && c.display_name_am ? c.display_name_am : c.name,
      value: String(c.id),
    })
  })
  return options
})

const formCampusOptions = computed(() => {
  return campuses.value.map(c => ({
    label: currentLocale.value === 'am' && c.display_name_am ? c.display_name_am : c.name,
    value: c.id,
  }))
})

async function fetchVaults() {
  loading.value = true
  try {
    const res = await custodyApi.getStorageLocations()
    storageLocations.value = Array.isArray(res) ? res : (res as any).data || []
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    loading.value = false
  }
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await Promise.all([
      fetchVaults(),
      referencesStore.fetchCampuses(true),
      referencesStore.fetchReferences(true),
    ])
    uiStore.success('Storage locations refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh storage locations')
  } finally {
    isRefreshing.value = false
  }
}

onMounted(() => {
  fetchVaults()
  referencesStore.fetchCampuses(true)
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.campus_id = campuses.value.length > 0 ? campuses.value[0].id : ''
  form.name = ''
  form.code = ''
  form.capacity = 25
  form.description = ''
  form.is_active = true
  isModalOpen.value = true
}

function openEditModal(vault: StorageLocation) {
  closeVaultMenu()
  modalMode.value = 'edit'
  editingId.value = vault.id
  form.campus_id = vault.campus_id
  form.name = vault.name
  form.code = (vault as any).code || vault.shelf_cabinet_code || ''
  form.capacity = vault.capacity || 25
  form.description = (vault as any).description || ''
  form.is_active = (vault as any).is_active !== false
  isModalOpen.value = true
}

async function handleSaveVault() {
  if (!form.campus_id || !form.name.trim()) {
    uiStore.error('Campus and vault name are required.')
    return
  }

  isSubmitting.value = true
  try {
    if (modalMode.value === 'create') {
      const created = await custodyApi.createStorageLocation({
        campus_id: Number(form.campus_id),
        name: form.name.trim(),
        shelf_cabinet_code: form.code.trim() || undefined,
        capacity: Number(form.capacity) || 25,
        description: form.description.trim() || undefined,
        is_active: form.is_active,
      })
      storageLocations.value.unshift(created)
      uiStore.success(t('admin.storageLocations.createdSuccess'))
    } else if (editingId.value) {
      const updated = await custodyApi.updateStorageLocation(editingId.value, {
        campus_id: Number(form.campus_id),
        name: form.name.trim(),
        shelf_cabinet_code: form.code.trim() || undefined,
        capacity: Number(form.capacity) || 25,
        description: form.description.trim() || undefined,
        is_active: form.is_active,
      })
      const index = storageLocations.value.findIndex(s => s.id === editingId.value)
      if (index !== -1) {
        storageLocations.value[index] = updated
      }
      uiStore.success(t('admin.storageLocations.updatedSuccess'))
    }
    isModalOpen.value = false
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    isSubmitting.value = false
  }
}

function handleToggleStatus(vault: StorageLocation) {
  closeVaultMenu()
  const isActive = (vault as any).is_active !== false
  uiStore.confirm({
    title: isActive ? t('admin.storageLocations.deactivateTitle') : t('admin.storageLocations.activateTitle'),
    message: `${t('admin.storageLocations.toggleConfirm')} "${vault.name}"?`,
    confirmText: isActive ? t('admin.storageLocations.deactivateBtn') : t('admin.storageLocations.activateBtn'),
    variant: isActive ? 'warning' : 'primary',
    onConfirm: async () => {
      try {
        const updated = await custodyApi.updateStorageLocation(vault.id, {
          is_active: !isActive,
        })
        const index = storageLocations.value.findIndex(s => s.id === vault.id)
        if (index !== -1) {
          storageLocations.value[index] = updated
        }
        uiStore.success(t('admin.storageLocations.updatedSuccess'))
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

async function handleDeleteVault(id: number) {
  closeVaultMenu()
  uiStore.confirm({
    title: t('admin.storageLocations.deleteTitle'),
    message: t('admin.storageLocations.deleteConfirm'),
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        await custodyApi.deleteStorageLocation(id)
        storageLocations.value = storageLocations.value.filter(s => s.id !== id)
        uiStore.success(t('admin.storageLocations.deletedSuccess'))
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

// Bulk Actions
async function handleBulkToggleStatus(activate: boolean) {
  if (selectedVaultIds.value.length === 0) return
  const ids = [...selectedVaultIds.value]
  for (const id of ids) {
    const s = storageLocations.value.find(item => item.id === id)
    if (s && ((s as any).is_active !== false) !== activate) {
      const updated = await custodyApi.updateStorageLocation(id, { is_active: activate })
      const index = storageLocations.value.findIndex(item => item.id === id)
      if (index !== -1) storageLocations.value[index] = updated
    }
  }
  uiStore.success(`${ids.length} storage locations ${activate ? 'activated' : 'deactivated'}.`)
}

// Export CSV
function exportVaultsCsv() {
  if (storageLocations.value.length === 0) {
    uiStore.warning('No storage locations to export')
    return
  }
  const headers = ['ID', 'Vault Name', 'Code', 'Campus', 'Capacity', 'Occupancy', 'Status', 'Description']
  const rows = storageLocations.value.map(s => [
    s.id,
    `"${s.name}"`,
    `"${s.shelf_cabinet_code || (s as any).code || ''}"`,
    `"${s.campus?.name || ''}"`,
    s.capacity || 0,
    s.current_occupancy || 0,
    (s as any).is_active !== false ? 'Active' : 'Inactive',
    `"${(s as any).description || ''}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', getExportFilename('vaults'))
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Storage locations exported as CSV.')
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeVaultMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Archive class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.administrativeStructure') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.storageLocations.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: All Vaults -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Storage Vaults
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalVaults }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Archive class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Vaults -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Active Vaults
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeVaults }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Total Capacity -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Storage Capacity
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCapacity }} <span class="text-xs font-bold text-slate-400">slots</span>
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Layers class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Items In Custody -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Items in Custody
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalOccupancy }} <span class="text-xs font-bold text-slate-400">items</span>
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Package class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.storageLocations.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalVaults }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.storageLocations.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="vault-search"
            :placeholder="t('common.searchPlaceholder')"
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
          <X v-if="showFilters" class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" />
          <Filter v-else class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" />
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
          @click="exportVaultsCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <!-- Refresh Button -->
        <button
          type="button"
          title="Refresh List"
          :disabled="isRefreshing || loading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || loading ? 'animate-spin' : ''" />
        </button>

        <!-- Create New Vault Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.storageLocations.addVault') }}
        </AppButton>
      </div>

    </div>

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 gap-3 transition-all duration-200"
    >
      <!-- Campus Filter -->
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.storageLocations.campus') }}
        </label>
        <AppSelect
          :options="campusFilterOptions"
          :model-value="selectedCampusId"
          class="w-full text-xs"
          @update:model-value="selectedCampusId = String($event); currentPage = 1"
        />
      </div>
    </div>

    <!-- Bulk Actions Header (When selected) -->
    <div
      v-if="selectedVaultIds.length > 0"
      class="px-4 py-2.5 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D]/60 border border-[#0B5D3B]/30 flex items-center justify-between text-xs transition-all shadow-xs"
    >
      <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedVaultIds.length }} storage locations selected
      </span>
      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(true)">
          {{ t('admin.storageLocations.activateBtn') }}
        </AppButton>
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(false)">
          {{ t('admin.storageLocations.deactivateBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Enterprise Storage Locations Data Table -->
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
              {{ t('admin.storageLocations.name') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.storageLocations.code') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.storageLocations.campus') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Capacity & Occupancy
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && storageLocations.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4 space-y-1.5">
              <div class="h-4 w-40 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded font-mono" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4 space-y-1">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-16 bg-slate-100 dark:bg-slate-800/60 rounded" />
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
        <tbody v-else-if="paginatedLocations.length === 0">
          <tr>
            <td colspan="7" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedCampusId !== 'all' ? 'No storage vaults match your active filters' : 'No storage vaults defined' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedCampusId !== 'all' ? 'Try adjusting your search criteria or campus filter.' : 'Create secure storage facilities and vaults to hold found property in physical custody.' }}
                </p>
                <div v-if="!searchQuery && selectedCampusId === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Storage Vault</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="vault in paginatedLocations"
            :key="vault.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedVaultIds.includes(vault.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectVault(vault.id)"
              >
                <CheckSquare v-if="selectedVaultIds.includes(vault.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Vault Name (Clean Text) -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ vault.name }}
                </p>
                <p v-if="vault.building || (vault as any).description" class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 max-w-xs truncate">
                  {{ vault.building ? `Building: ${vault.building}` : (vault as any).description }}
                </p>
              </div>
            </td>

            <!-- Code (Monospace) -->
            <td class="px-4 py-3.5">
              <span class="font-mono text-slate-700 dark:text-slate-300 text-xs font-semibold">
                {{ vault.shelf_cabinet_code || (vault as any).code || '—' }}
              </span>
            </td>

            <!-- Campus -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ vault.campus?.name || '—' }}
            </td>

            <!-- Capacity & Occupancy -->
            <td class="px-4 py-3.5">
              <div class="flex items-center gap-2">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                  {{ vault.current_occupancy || 0 }} / {{ vault.capacity || 25 }}
                </span>
                <span class="text-[11px] text-slate-400">items</span>
              </div>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="cursor-pointer"
                @click="handleToggleStatus(vault)"
              >
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition-colors',
                    (vault as any).is_active !== false
                      ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60 hover:bg-emerald-100'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200',
                  ]"
                >
                  {{ (vault as any).is_active !== false ? t('common.active') : t('common.inactive') }}
                </span>
              </button>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Edit Vault -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openEditModal(vault)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <!-- 2. Activate / Deactivate Toggle -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleToggleStatus(vault)"
                >
                  <ToggleRight v-if="(vault as any).is_active !== false" class="h-4 w-4 text-emerald-500" />
                  <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                  <span>{{ (vault as any).is_active !== false ? t('admin.storageLocations.deactivateBtn') : t('admin.storageLocations.activateBtn') }}</span>
                </button>

                <div class="my-1 border-t border-slate-100 dark:border-slate-800" />

                <!-- 3. Delete Vault -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleDeleteVault(vault.id)"
                >
                  <Trash2 class="h-4 w-4 text-rose-500" />
                  <span>{{ t('common.delete') }}</span>
                </button>
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
      :total="filteredLocations.length"
      :per-page="perPage"
      @update:current-page="currentPage = $event"
      @update:per-page="perPage = $event; currentPage = 1"
    />

    <!-- Modal Form -->
    <AppModal
      v-model:open="isModalOpen"
      :title="modalMode === 'create' ? t('admin.storageLocations.addLocation') : t('admin.storageLocations.editLocation')"
      max-width="lg"
    >
      <div class="space-y-4 py-2">
        <AppSelect
          :label="t('admin.storageLocations.campus') + ' *'"
          :options="formCampusOptions"
          :model-value="form.campus_id"
          required
          @update:model-value="form.campus_id = Number($event)"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="modal-vault-name"
            :label="t('admin.storageLocations.name') + ' *'"
            placeholder="e.g. Main Library Vault #1"
            :model-value="form.name"
            required
            @update:model-value="form.name = $event"
          />

          <AppInput
            id="modal-vault-code"
            :label="t('admin.storageLocations.code')"
            placeholder="e.g. LIB-VAULT-01"
            :model-value="form.code"
            @update:model-value="form.code = $event"
          />
        </div>

        <AppInput
          id="modal-vault-capacity"
          type="number"
          :label="t('admin.storageLocations.capacity')"
          :model-value="String(form.capacity)"
          @update:model-value="form.capacity = Number($event) || 0"
        />

        <AppInput
          id="modal-vault-desc"
          :label="t('admin.storageLocations.description')"
          placeholder="Building, room number, or custodian notes..."
          :model-value="form.description"
          @update:model-value="form.description = $event"
        />

        <div class="pt-2">
          <AppCheckbox
            id="modal-vault-active"
            :label="t('common.active')"
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
          <AppButton variant="primary" :loading="isSubmitting" @click="handleSaveVault">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
