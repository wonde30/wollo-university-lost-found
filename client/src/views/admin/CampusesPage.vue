<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { currentLocale, t } from '@/i18n'
import type { Campus } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Building2,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Edit2,
  CheckCircle2,
  CheckSquare,
  Square,
  MapPin,
  Landmark,
  ToggleLeft,
  ToggleRight,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const campuses = computed(() => referencesStore.campuses)
const loading = computed(() => referencesStore.loading && campuses.value.length === 0)
const isRefreshing = ref(false)

// Pagination & Filter State
const showFilters = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')
const selectedStatus = ref<'all' | 'active' | 'inactive'>('all')

// Multi-Selection State
const selectedCampusIds = ref<number[]>([])

function closeCampusMenu() {
  // Context menu handled by AppActionMenu
}

// Modal State
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const isSubmitting = ref(false)

const form = reactive({
  name: '',
  display_name_am: '',
  code: '',
  address: '',
  description: '',
  is_active: true,
})

// Metrics Computation
const totalCount = computed(() => campuses.value.length)
const activeCount = computed(() => campuses.value.filter(c => c.is_active !== false).length)
const branchSitesCount = computed(() => campuses.value.filter(c => c.code !== 'MAIN').length)

// Filtered Campuses
const filteredCampuses = computed(() => {
  let list = campuses.value

  if (selectedStatus.value === 'active') {
    list = list.filter(c => c.is_active !== false)
  } else if (selectedStatus.value === 'inactive') {
    list = list.filter(c => c.is_active === false)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      c =>
        c.name.toLowerCase().includes(query) ||
        (c.display_name_am && c.display_name_am.includes(query)) ||
        (c.short_code || c.code || '').toLowerCase().includes(query) ||
        (c.address && c.address.toLowerCase().includes(query))
    )
  }

  return list
})

const paginatedCampuses = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredCampuses.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredCampuses.value.length / perPage.value) || 1)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedCampuses.value.length === 0) return false
  return paginatedCampuses.value.every(c => selectedCampusIds.value.includes(c.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedCampuses.value.map(c => c.id)
    selectedCampusIds.value = selectedCampusIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedCampuses.value.map(c => c.id)
    const newSelected = new Set([...selectedCampusIds.value, ...pageIds])
    selectedCampusIds.value = Array.from(newSelected)
  }
}

function toggleSelectCampus(id: number) {
  const index = selectedCampusIds.value.indexOf(id)
  if (index !== -1) {
    selectedCampusIds.value.splice(index, 1)
  } else {
    selectedCampusIds.value.push(id)
  }
}

const statusFilterOptions = computed(() => [
  { label: 'All Status', value: 'all' },
  { label: t('common.active'), value: 'active' },
  { label: t('common.inactive'), value: 'inactive' },
])

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await referencesStore.fetchCampuses(true)
    uiStore.success('Campuses refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh campuses')
  } finally {
    isRefreshing.value = false
  }
}

onMounted(async () => {
  await referencesStore.fetchCampuses(true)
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.name = ''
  form.display_name_am = ''
  form.code = ''
  form.address = ''
  form.description = ''
  form.is_active = true
  isModalOpen.value = true
}

function openEditModal(campus: Campus) {
  closeCampusMenu()
  modalMode.value = 'edit'
  editingId.value = campus.id
  form.name = campus.name
  form.display_name_am = (campus as any).display_name_am || ''
  form.code = campus.short_code || campus.code || ''
  form.address = campus.address || ''
  form.description = campus.description || ''
  form.is_active = campus.is_active !== false
  isModalOpen.value = true
}

async function handleSaveCampus() {
  if (!form.name.trim() || !form.code.trim()) {
    uiStore.error(t('admin.campuses.requiredError'))
    return
  }

  isSubmitting.value = true
  try {
    const payload = {
      name: form.name.trim(),
      short_code: form.code.trim().toUpperCase(),
      code: form.code.trim().toUpperCase(),
      address: form.address.trim() || null,
      description: form.description.trim() || null,
    }
    if (modalMode.value === 'create') {
      await referencesStore.createCampus(payload)
      uiStore.success(t('admin.campuses.createdSuccess'))
    } else if (editingId.value) {
      await referencesStore.updateCampus(editingId.value, payload)
      uiStore.success(t('admin.campuses.updatedSuccess'))
    }
    isModalOpen.value = false
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    isSubmitting.value = false
  }
}

async function handleToggleActive(campus: Campus) {
  closeCampusMenu()
  const actionTitle = campus.is_active ? t('admin.campuses.deactivateBtn') : t('admin.campuses.activateBtn')
  uiStore.confirm({
    title: t('admin.campuses.toggleStatusTitle', { action: actionTitle }),
    message: `${actionTitle} "${(currentLocale.value === 'am' && campus.display_name_am) ? campus.display_name_am : campus.name}"?`,
    confirmText: actionTitle,
    variant: campus.is_active ? 'danger' : 'primary',
    onConfirm: async () => {
      try {
        if (campus.is_active) {
          await referencesStore.deleteCampus(campus.id)
          uiStore.success(t('admin.campuses.deactivatedSuccess'))
        } else {
          await referencesStore.restoreCampus(campus.id)
          uiStore.success(t('admin.campuses.activatedSuccess'))
        }
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

// Bulk Actions
async function handleBulkToggleStatus(activate: boolean) {
  if (selectedCampusIds.value.length === 0) return
  const ids = [...selectedCampusIds.value]
  for (const id of ids) {
    const c = campuses.value.find(item => item.id === id)
    if (c && (c.is_active !== false) !== activate) {
      if (activate) {
        await referencesStore.restoreCampus(id)
      } else {
        await referencesStore.deleteCampus(id)
      }
    }
  }
  uiStore.success(`${ids.length} campuses updated.`)
}

// Export CSV
function exportCampusesCsv() {
  if (campuses.value.length === 0) {
    uiStore.warning('No campuses to export')
    return
  }
  const headers = ['ID', 'Name', 'Code', 'Address', 'Status', 'Description']
  const rows = campuses.value.map(c => [
    c.id,
    `"${c.name}"`,
    `"${c.code}"`,
    `"${c.address || ''}"`,
    c.is_active !== false ? 'Active' : 'Inactive',
    `"${c.description || ''}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_campuses_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Campuses exported as CSV.')
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeCampusMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Building2 class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.administrativeStructure') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.campuses.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Campuses -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Institutional Campuses
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:bg-blue-400 flex items-center justify-center font-bold">
          <Landmark class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Sites -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Active Instructional Sites
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:bg-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Branch Sites -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Branch Campuses
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ branchSitesCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:bg-sky-400 flex items-center justify-center font-bold">
          <Building2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Geographic Coverage -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Regional Locations
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            Dessie & Kombolcha
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:bg-amber-400 flex items-center justify-center font-bold">
          <MapPin class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.campuses.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.campuses.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="campus-search"
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
          @click="exportCampusesCsv"
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

        <!-- Create New Campus Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.campuses.addCampus') }}
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
          :options="statusFilterOptions"
          :model-value="selectedStatus"
          class="w-full text-xs"
          @update:model-value="selectedStatus = $event as any; currentPage = 1"
        />
      </div>
    </div>

    <!-- Bulk Actions Header (When selected) -->
    <div
      v-if="selectedCampusIds.length > 0"
      class="px-4 py-2.5 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D]/60 border border-[#0B5D3B]/30 flex items-center justify-between text-xs transition-all shadow-xs"
    >
      <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedCampusIds.length }} campuses selected
      </span>
      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(true)">
          {{ t('admin.campuses.activateBtn') }}
        </AppButton>
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(false)">
          {{ t('admin.campuses.deactivateBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Enterprise Campuses Data Table -->
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
              {{ t('admin.campuses.name') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.campuses.code') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.campuses.address') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && campuses.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
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
            <td class="px-4 py-4">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="paginatedCampuses.length === 0">
          <tr>
            <td colspan="6" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedStatus !== 'all' ? 'No campuses match your active filters' : 'No university campuses found' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedStatus !== 'all' ? 'Try adjusting your search query or status filter.' : 'Add campus locations such as Meda or Kelem to organize lost and found reporting.' }}
                </p>
                <div v-if="!searchQuery && selectedStatus === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Campus</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="campus in paginatedCampuses"
            :key="campus.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedCampusIds.includes(campus.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectCampus(campus.id)"
              >
                <CheckSquare v-if="selectedCampusIds.includes(campus.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Campus Name (Clean Text) -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ (currentLocale === 'am' && campus.display_name_am) ? campus.display_name_am : (campus.display_name || campus.name) }}
                </p>
                <p v-if="campus.description" class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5 max-w-xs truncate">
                  {{ campus.description }}
                </p>
              </div>
            </td>

            <!-- Code (Monospace) -->
            <td class="px-4 py-3.5">
              <span class="font-mono text-slate-700 dark:text-slate-300 text-xs font-semibold">
                {{ campus.short_code || campus.code }}
              </span>
            </td>

            <!-- Address -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ campus.address || campus.description || '—' }}
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="cursor-pointer"
                @click="handleToggleActive(campus)"
              >
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition-colors',
                    campus.is_active !== false
                      ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60 hover:bg-emerald-100'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200',
                  ]"
                >
                  {{ campus.is_active !== false ? t('common.active') : t('common.inactive') }}
                </span>
              </button>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Edit Campus -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openEditModal(campus)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <!-- 2. Activate / Deactivate Toggle -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleToggleActive(campus)"
                >
                  <ToggleRight v-if="campus.is_active !== false" class="h-4 w-4 text-emerald-500" />
                  <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                  <span>{{ campus.is_active !== false ? t('admin.campuses.deactivateBtn') : t('admin.campuses.activateBtn') }}</span>
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
      :total="filteredCampuses.length"
      :per-page="perPage"
      @update:current-page="currentPage = $event"
      @update:per-page="perPage = $event; currentPage = 1"
    />

    <!-- Modal Form: Create / Edit Campus -->
    <AppModal
      v-model:open="isModalOpen"
      :title="modalMode === 'create' ? t('admin.campuses.addCampus') : t('admin.campuses.editCampus') || 'Edit Campus'"
      max-width="lg"
    >
      <div class="space-y-4 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="campus-name"
            :label="t('admin.campuses.name') + ' *'"
            :placeholder="t('admin.campuses.placeholders.name')"
            :model-value="form.name"
            required
            @update:model-value="form.name = $event"
          />

          <AppInput
            id="campus-code"
            :label="t('admin.campuses.code') + ' *'"
            :placeholder="t('admin.campuses.placeholders.code')"
            :model-value="form.code"
            required
            @update:model-value="form.code = $event"
          />
        </div>

        <AppInput
          id="campus-address"
          :label="t('admin.campuses.address')"
          :placeholder="t('admin.campuses.placeholders.address')"
          :model-value="form.address"
          @update:model-value="form.address = $event"
        />

        <AppInput
          id="campus-desc"
          :label="t('admin.campuses.description')"
          :placeholder="t('admin.campuses.placeholders.description')"
          :model-value="form.description"
          @update:model-value="form.description = $event"
        />
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmitting" @click="handleSaveCampus">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
