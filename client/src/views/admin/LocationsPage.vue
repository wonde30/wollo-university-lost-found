<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { currentLocale, t } from '@/i18n'
import type { Location } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  MapPin,
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
  Building,
  Layers,
  ToggleLeft,
  ToggleRight,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const locations = computed(() => referencesStore.locations)
const campuses = computed(() => referencesStore.campuses)
const loading = computed(() => referencesStore.loading && locations.value.length === 0)
const isRefreshing = ref(false)

// Pagination & Filters
const showFilters = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')
const selectedCampusId = ref<string>('all')

// Multi-Selection State
const selectedLocIds = ref<number[]>([])

function closeLocMenu() {
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
  building: '',
  floor: '',
})

// Metrics
const totalCount = computed(() => locations.value.length)
const activeCount = computed(() => locations.value.filter(l => (l as any).is_active !== false).length)
const buildingsCount = computed(() => new Set(locations.value.map(l => l.building).filter(Boolean)).size)
const campusesCovered = computed(() => new Set(locations.value.map(l => l.campus_id)).size)

// Filtered Locations
const filteredLocations = computed(() => {
  let list = locations.value

  if (selectedCampusId.value !== 'all') {
    const cId = Number(selectedCampusId.value)
    list = list.filter(l => l.campus_id === cId)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      l =>
        l.name.toLowerCase().includes(query) ||
        (l.code && l.code.toLowerCase().includes(query)) ||
        (l.building && l.building.toLowerCase().includes(query)) ||
        (l.floor && l.floor.toLowerCase().includes(query))
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
  return paginatedLocations.value.every(l => selectedLocIds.value.includes(l.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedLocations.value.map(l => l.id)
    selectedLocIds.value = selectedLocIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedLocations.value.map(l => l.id)
    const newSelected = new Set([...selectedLocIds.value, ...pageIds])
    selectedLocIds.value = Array.from(newSelected)
  }
}

function toggleSelectLoc(id: number) {
  const index = selectedLocIds.value.indexOf(id)
  if (index !== -1) {
    selectedLocIds.value.splice(index, 1)
  } else {
    selectedLocIds.value.push(id)
  }
}

const campusFilterOptions = computed(() => {
  const options = [{ label: t('admin.locations.allCampuses'), value: 'all' }]
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

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await Promise.all([
      referencesStore.fetchCampuses(true),
      referencesStore.fetchLocations(true),
    ])
    uiStore.success('Locations refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh locations')
  } finally {
    isRefreshing.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    referencesStore.fetchCampuses(true),
    referencesStore.fetchLocations(true),
  ])
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.campus_id = campuses.value.length > 0 ? campuses.value[0].id : ''
  form.name = ''
  form.code = ''
  form.building = ''
  form.floor = ''
  isModalOpen.value = true
}

function openEditModal(location: Location) {
  closeLocMenu()
  modalMode.value = 'edit'
  editingId.value = location.id
  form.campus_id = location.campus_id
  form.name = location.name
  form.code = location.code || ''
  form.building = location.building || ''
  form.floor = location.floor || ''
  isModalOpen.value = true
}

async function handleSaveLocation() {
  if (!form.name.trim() || !form.code.trim() || !form.campus_id) {
    uiStore.error(t('admin.locations.requiredError'))
    return
  }

  isSubmitting.value = true
  try {
    if (modalMode.value === 'create') {
      await referencesStore.createLocation({
        campus_id: Number(form.campus_id),
        name: form.name.trim(),
        code: form.code.trim(),
        building: form.building.trim() || null,
        floor: form.floor.trim() || null,
      })
      uiStore.success(t('admin.locations.createdSuccess'))
    } else if (editingId.value) {
      await referencesStore.updateLocation(editingId.value, {
        campus_id: Number(form.campus_id),
        name: form.name.trim(),
        code: form.code.trim(),
        building: form.building.trim() || null,
        floor: form.floor.trim() || null,
      })
      uiStore.success(t('admin.locations.updatedSuccess'))
    }
    isModalOpen.value = false
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    isSubmitting.value = false
  }
}

async function handleToggleActive(loc: Location) {
  closeLocMenu()
  const isActive = (loc as any).is_active !== false
  const actionTitle = isActive ? t('admin.locations.deactivateBtn') : t('admin.locations.activateBtn')
  uiStore.confirm({
    title: t('admin.locations.toggleStatusTitle', { action: actionTitle }),
    message: `${actionTitle} "${(currentLocale.value === 'am' && (loc as any).display_name_am) ? (loc as any).display_name_am : loc.name}"?`,
    confirmText: actionTitle,
    variant: isActive ? 'danger' : 'primary',
    onConfirm: async () => {
      try {
        await referencesStore.updateLocation(loc.id, {
          is_active: !isActive,
        } as any)
        uiStore.success(isActive ? t('admin.locations.deactivatedSuccess') : t('admin.locations.activatedSuccess'))
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

// Bulk Actions
async function handleBulkToggleStatus(activate: boolean) {
  if (selectedLocIds.value.length === 0) return
  const ids = [...selectedLocIds.value]
  for (const id of ids) {
    const l = locations.value.find(item => item.id === id)
    if (l && ((l as any).is_active !== false) !== activate) {
      await referencesStore.updateLocation(id, { is_active: activate } as any)
    }
  }
  uiStore.success(`${ids.length} locations updated.`)
}

// Export CSV
function exportLocationsCsv() {
  if (locations.value.length === 0) {
    uiStore.warning('No locations to export')
    return
  }
  const headers = ['ID', 'Location Name', 'Code', 'Campus', 'Building', 'Floor', 'Status']
  const rows = locations.value.map(l => [
    l.id,
    `"${l.name}"`,
    `"${l.code || ''}"`,
    `"${l.campus?.name || ''}"`,
    `"${l.building || ''}"`,
    `"${l.floor || ''}"`,
    (l as any).is_active !== false ? 'Active' : 'Inactive',
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_locations_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Locations exported as CSV.')
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeLocMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <MapPin class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.administrativeStructure') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.locations.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Locations -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Specific Locations
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <MapPin class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Locations -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Active Spots
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Buildings & Halls -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Distinct Buildings
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ buildingsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Building class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Campuses Covered -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Campuses Covered
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ campusesCovered }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Layers class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.locations.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.locations.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="loc-search"
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
          @click="exportLocationsCsv"
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

        <!-- Create New Location Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.locations.addLocation') }}
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
          {{ t('admin.locations.campus') }}
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
      v-if="selectedLocIds.length > 0"
      class="px-4 py-2.5 rounded-xl bg-[#E8F4EE] dark:bg-[#153C2D]/60 border border-[#0B5D3B]/30 flex items-center justify-between text-xs transition-all shadow-xs"
    >
      <span class="font-bold text-[#0B5D3B] dark:text-[#75bd97]">
        {{ selectedLocIds.length }} locations selected
      </span>
      <div class="flex items-center gap-2">
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(true)">
          {{ t('admin.locations.activateBtn') }}
        </AppButton>
        <AppButton variant="secondary" size="xs" @click="handleBulkToggleStatus(false)">
          {{ t('admin.locations.deactivateBtn') }}
        </AppButton>
      </div>
    </div>

    <!-- Enterprise Locations Data Table -->
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
              {{ t('admin.locations.name') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.locations.code') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.locations.campus') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Building & Floor
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && locations.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
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
              <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded" />
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
                  {{ searchQuery || selectedCampusId !== 'all' ? 'No locations match your active filters' : 'No campus sub-locations defined' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedCampusId !== 'all' ? 'Try adjusting your search criteria or campus filter.' : 'Add buildings, gates, libraries, and laboratories to localize item incidents.' }}
                </p>
                <div v-if="!searchQuery && selectedCampusId === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Location</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="loc in paginatedLocations"
            :key="loc.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedLocIds.includes(loc.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectLoc(loc.id)"
              >
                <CheckSquare v-if="selectedLocIds.includes(loc.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Location Name (Clean Text) -->
            <td class="px-4 py-3.5">
              <p class="font-bold text-slate-900 dark:text-white">
                {{ (currentLocale === 'am' && (loc as any).display_name_am) ? (loc as any).display_name_am : loc.name }}
              </p>
            </td>

            <!-- Code (Monospace) -->
            <td class="px-4 py-3.5">
              <span class="font-mono text-slate-700 dark:text-slate-300 text-xs font-semibold">
                {{ loc.code }}
              </span>
            </td>

            <!-- Campus -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ loc.campus?.name || '—' }}
            </td>

            <!-- Building & Floor -->
            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
              <span v-if="loc.building || loc.floor">
                {{ [loc.building, loc.floor ? `Floor ${loc.floor}` : ''].filter(Boolean).join(' • ') }}
              </span>
              <span v-else class="text-slate-400">—</span>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <button
                type="button"
                class="cursor-pointer"
                @click="handleToggleActive(loc)"
              >
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border transition-colors',
                    (loc as any).is_active !== false
                      ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60 hover:bg-emerald-100'
                      : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700 hover:bg-slate-200',
                  ]"
                >
                  {{ (loc as any).is_active !== false ? t('common.active') : t('common.inactive') }}
                </span>
              </button>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Edit Location -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openEditModal(loc)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <!-- 2. Activate / Deactivate Toggle -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleToggleActive(loc)"
                >
                  <ToggleRight v-if="(loc as any).is_active !== false" class="h-4 w-4 text-emerald-500" />
                  <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                  <span>{{ (loc as any).is_active !== false ? t('admin.locations.deactivateBtn') : t('admin.locations.activateBtn') }}</span>
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
      :title="modalMode === 'create' ? t('admin.locations.addLocation') : t('admin.locations.editLocation') || 'Edit Location'"
      max-width="lg"
    >
      <div class="space-y-4 py-2">
        <AppSelect
          :label="t('admin.locations.campus') + ' *'"
          :options="formCampusOptions"
          :model-value="form.campus_id"
          required
          @update:model-value="form.campus_id = Number($event)"
        />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="modal-loc-name"
            :label="t('admin.locations.name') + ' *'"
            :placeholder="t('admin.locations.placeholders.name')"
            :model-value="form.name"
            required
            @update:model-value="form.name = $event"
          />

          <AppInput
            id="modal-loc-code"
            :label="t('admin.locations.code') + ' *'"
            :placeholder="t('admin.locations.placeholders.code')"
            :model-value="form.code"
            required
            @update:model-value="form.code = $event"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <AppInput
            id="modal-loc-building"
            :label="t('admin.locations.building')"
            :placeholder="t('admin.locations.placeholders.building')"
            :model-value="form.building"
            @update:model-value="form.building = $event"
          />

          <AppInput
            id="modal-loc-floor"
            :label="t('admin.locations.floor')"
            :placeholder="t('admin.locations.placeholders.floor')"
            :model-value="form.floor"
            @update:model-value="form.floor = $event"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmitting" @click="handleSaveLocation">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
