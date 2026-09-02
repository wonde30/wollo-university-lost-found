<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import {
  getAnnouncements,
  createAnnouncement,
  updateAnnouncement,
  deleteAnnouncement,
  toggleAnnouncementActive,
  bulkToggleAnnouncements,
  bulkDeleteAnnouncements,
} from '@/features/admin/api/admin.api'
import { useUiStore } from '@/stores/ui.store'
import { useAnnouncementsStore } from '@/stores/announcements.store'
import { getErrorMessage } from '@/utils/error-handler'
import { formatDate } from '@/utils/date'
import { t } from '@/i18n'
import type {
  Announcement,
  StoreAnnouncementData,
} from '@/features/admin/types/admin.types'

import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppSkeleton from '@/components/ui/AppSkeleton.vue'

import {
  Megaphone,
  Radio,
  AlertTriangle,
  AlertCircle,
  Wrench,
  CheckCircle2,
  Users,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Edit2,
  Trash2,
  CheckSquare,
  Square,
  Eye,
  Check,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const announcementsStore = useAnnouncementsStore()

// State
const announcements = ref<Announcement[]>([])
const loading = ref(false)
const isRefreshing = ref(false)

// Pagination & Filter State
const showFilters = ref(false)
const currentPage = ref(1)
const perPage = ref(10)
const totalItems = ref(0)
const lastPage = ref(1)
const searchQuery = ref('')
const selectedType = ref<string>('all')
const selectedAudience = ref<string>('all')
const selectedStatus = ref<string>('all')

// Multi-Selection State
const selectedIds = ref<number[]>([])

// Modal States
const isModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const isSubmitting = ref(false)
const formErrors = ref<Record<string, string>>({})

// View Details Modal State
const isViewModalOpen = ref(false)
const viewingItem = ref<Announcement | null>(null)

const form = reactive<{
  title: string
  body: string
  type: string
  audience: string
  is_active: boolean
  starts_at: string
  ends_at: string
}>({
  title: '',
  body: '',
  type: 'info',
  audience: 'all',
  is_active: true,
  starts_at: '',
  ends_at: '',
})

// Metrics Computation
const totalCount = computed(() => totalItems.value)
const activeCount = computed(() => announcements.value.filter(a => a.is_active).length)
const urgentCount = computed(() => announcements.value.filter(a => a.type === 'urgent' || a.type === 'warning').length)
const studentAudienceCount = computed(() => announcements.value.filter(a => a.audience === 'students' || a.audience === 'all').length)

// Type, Audience & Status filter options
const typeFilterOptions = computed(() => [
  { value: 'all', label: t('common.allTypes') },
  { value: 'info', label: t('admin.announcements.types.info') },
  { value: 'warning', label: t('admin.announcements.types.warning') },
  { value: 'urgent', label: t('admin.announcements.types.urgent') },
  { value: 'maintenance', label: t('admin.announcements.types.maintenance') },
  { value: 'success', label: t('admin.announcements.types.success') },
])

const audienceFilterOptions = computed(() => [
  { value: 'all', label: t('admin.announcements.audiences.all') },
  { value: 'students', label: t('admin.announcements.audiences.students') },
  { value: 'staff', label: t('admin.announcements.audiences.staff') },
  { value: 'admin', label: t('admin.announcements.audiences.admin') },
])

const statusFilterOptions = computed(() => [
  { value: 'all', label: t('common.allStatuses') },
  { value: 'active', label: t('common.active') },
  { value: 'inactive', label: t('common.inactive') },
])

// Load announcements
async function fetchList(isRefresh = false): Promise<void> {
  if (isRefresh) {
    isRefreshing.value = true
  } else {
    loading.value = true
  }

  try {
    const params: any = {
      page: currentPage.value,
      per_page: perPage.value,
    }

    if (searchQuery.value.trim()) {
      params.search = searchQuery.value.trim()
    }
    if (selectedType.value !== 'all') {
      params.type = selectedType.value
    }
    if (selectedAudience.value !== 'all') {
      params.audience = selectedAudience.value
    }
    if (selectedStatus.value !== 'all') {
      params.is_active = selectedStatus.value === 'active'
    }

    const res = await getAnnouncements(params)
    announcements.value = res.data || []
    if (res.meta) {
      totalItems.value = res.meta.total
      currentPage.value = res.meta.current_page
      lastPage.value = res.meta.last_page
      perPage.value = res.meta.per_page
    } else {
      totalItems.value = announcements.value.length
    }
  } catch (err) {
    uiStore.showToast(getErrorMessage(err), 'error')
  } finally {
    loading.value = false
    isRefreshing.value = false
  }
}

onMounted(() => {
  fetchList()
})

function handleSearch(): void {
  currentPage.value = 1
  fetchList()
}

function handleResetFilters(): void {
  searchQuery.value = ''
  selectedType.value = 'all'
  selectedAudience.value = 'all'
  selectedStatus.value = 'all'
  currentPage.value = 1
  fetchList()
}

function handlePageChange(page: number): void {
  currentPage.value = page
  fetchList()
}

// Selection helpers
const isAllCurrentPageSelected = computed(() => {
  if (announcements.value.length === 0) return false
  return announcements.value.every(a => selectedIds.value.includes(a.id))
})

function toggleSelectAll(): void {
  if (isAllCurrentPageSelected.value) {
    const currentIds = announcements.value.map(a => a.id)
    selectedIds.value = selectedIds.value.filter(id => !currentIds.includes(id))
  } else {
    const currentIds = announcements.value.map(a => a.id)
    selectedIds.value = Array.from(new Set([...selectedIds.value, ...currentIds]))
  }
}

function toggleSelect(id: number): void {
  const index = selectedIds.value.indexOf(id)
  if (index !== -1) {
    selectedIds.value.splice(index, 1)
  } else {
    selectedIds.value.push(id)
  }
}

// Bulk Actions
async function handleBulkActivate(): Promise<void> {
  if (selectedIds.value.length === 0) return
  try {
    await bulkToggleAnnouncements(selectedIds.value, true)
    uiStore.showToast(t('admin.announcements.activated'), 'success')
    selectedIds.value = []
    await fetchList()
    announcementsStore.fetchActive(true)
  } catch (err) {
    uiStore.showToast(getErrorMessage(err), 'error')
  }
}

async function handleBulkDeactivate(): Promise<void> {
  if (selectedIds.value.length === 0) return
  try {
    await bulkToggleAnnouncements(selectedIds.value, false)
    uiStore.showToast(t('admin.announcements.deactivated'), 'success')
    selectedIds.value = []
    await fetchList()
    announcementsStore.fetchActive(true)
  } catch (err) {
    uiStore.showToast(getErrorMessage(err), 'error')
  }
}

function handleBulkDelete(): void {
  if (selectedIds.value.length === 0) return
  uiStore.showConfirm({
    title: t('admin.announcements.deleteTitle'),
    message: `Are you sure you want to delete ${selectedIds.value.length} selected announcements?`,
    variant: 'danger',
    confirmText: t('common.delete'),
    onConfirm: async () => {
      try {
        await bulkDeleteAnnouncements(selectedIds.value)
        uiStore.showToast(t('admin.announcements.deletedSuccess'), 'success')
        selectedIds.value = []
        await fetchList()
        announcementsStore.fetchActive(true)
      } catch (err) {
        uiStore.showToast(getErrorMessage(err), 'error')
      }
    },
  })
}

// Modal actions
function openCreateModal(): void {
  modalMode.value = 'create'
  editingId.value = null
  formErrors.value = {}
  form.title = ''
  form.body = ''
  form.type = 'info'
  form.audience = 'all'
  form.is_active = true
  form.starts_at = ''
  form.ends_at = ''
  isModalOpen.value = true
}

function openEditModal(announcement: Announcement): void {
  modalMode.value = 'edit'
  editingId.value = announcement.id
  formErrors.value = {}
  form.title = announcement.title
  form.body = announcement.body
  form.type = announcement.type
  form.audience = announcement.audience
  form.is_active = announcement.is_active
  form.starts_at = announcement.starts_at ? announcement.starts_at.slice(0, 16) : ''
  form.ends_at = announcement.ends_at ? announcement.ends_at.slice(0, 16) : ''
  isModalOpen.value = true
}

function openViewModal(announcement: Announcement): void {
  viewingItem.value = announcement
  isViewModalOpen.value = true
}

async function handleSubmit(): Promise<void> {
  formErrors.value = {}
  if (!form.title.trim()) {
    formErrors.value.title = t('validation.required')
  }
  if (!form.body.trim()) {
    formErrors.value.body = t('validation.required')
  }

  if (Object.keys(formErrors.value).length > 0) {
    return
  }

  isSubmitting.value = true
  try {
    const payload: StoreAnnouncementData = {
      title: form.title.trim(),
      body: form.body.trim(),
      type: form.type,
      audience: form.audience,
      is_active: form.is_active,
      starts_at: form.starts_at ? new Date(form.starts_at).toISOString() : null,
      ends_at: form.ends_at ? new Date(form.ends_at).toISOString() : null,
    }

    if (modalMode.value === 'create') {
      await createAnnouncement(payload)
      uiStore.showToast(t('admin.announcements.createdSuccess'), 'success')
    } else if (editingId.value) {
      await updateAnnouncement(editingId.value, payload)
      uiStore.showToast(t('admin.announcements.updatedSuccess'), 'success')
    }

    isModalOpen.value = false
    await fetchList()
    announcementsStore.fetchActive(true)
  } catch (err) {
    uiStore.showToast(getErrorMessage(err), 'error')
  } finally {
    isSubmitting.value = false
  }
}

// Toggle status directly
async function handleToggleActive(announcement: Announcement): Promise<void> {
  try {
    const res = await toggleAnnouncementActive(announcement.id)
    announcement.is_active = res.is_active
    uiStore.showToast(
      res.is_active
        ? t('admin.announcements.activated')
        : t('admin.announcements.deactivated'),
      'success'
    )
    announcementsStore.fetchActive(true)
  } catch (err) {
    uiStore.showToast(getErrorMessage(err), 'error')
  }
}

// Delete single announcement
function handleDelete(announcement: Announcement): void {
  uiStore.showConfirm({
    title: t('admin.announcements.deleteTitle'),
    message: t('admin.announcements.deleteConfirm', { title: announcement.title }),
    variant: 'danger',
    confirmText: t('common.delete'),
    onConfirm: async () => {
      try {
        await deleteAnnouncement(announcement.id)
        uiStore.showToast(t('admin.announcements.deletedSuccess'), 'success')
        await fetchList()
        announcementsStore.fetchActive(true)
      } catch (err) {
        uiStore.showToast(getErrorMessage(err), 'error')
      }
    },
  })
}

// CSV Export
function exportAnnouncementsCsv(): void {
  if (announcements.value.length === 0) {
    uiStore.warning('No announcements to export')
    return
  }

  const headers = ['ID', 'Title', 'Type', 'Audience', 'Status', 'Starts At', 'Ends At', 'Created At', 'Body']
  const rows = announcements.value.map(a => [
    a.id,
    `"${a.title.replace(/"/g, '""')}"`,
    a.type,
    a.audience,
    a.is_active ? 'Active' : 'Inactive',
    a.starts_at || 'Indefinite',
    a.ends_at || 'Indefinite',
    a.created_at || '',
    `"${a.body.replace(/"/g, '""')}"`,
  ])

  const csvContent = 'data:text/csv;charset=utf-8,\uFEFF' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_announcements_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Announcements exported as CSV.')
}

// Icon helper for notice types
function getTypeIcon(type: string) {
  switch (type) {
    case 'urgent':
      return AlertCircle
    case 'warning':
      return AlertTriangle
    case 'maintenance':
      return Wrench
    case 'success':
      return CheckCircle2
    case 'info':
    default:
      return Megaphone
  }
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- 1. Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Megaphone class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.adminSetup') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.announcements.title') }}</span>
    </div>

    <!-- 2. Top Metric Cards (4 Cards Grid) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Announcements -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.announcements.totalBroadcasts') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/20 text-[#0B5D3B] dark:text-[#75bd97] flex items-center justify-center font-bold">
          <Megaphone class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Broadcasts -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.announcements.activeBroadcasts') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-0.5">
            {{ activeCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <Radio class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Urgent Alerts -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.announcements.urgentAlerts') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-amber-600 dark:text-amber-400 tracking-tight mt-0.5">
            {{ urgentCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <AlertTriangle class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Student Reach -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.announcements.studentCoverage') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-sky-600 dark:text-sky-400 tracking-tight mt-0.5">
            {{ studentAudienceCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Users class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- 3. Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.announcements.title') }}
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#0B5D3B]/10 dark:bg-[#0B5D3B]/20 text-[#0B5D3B] dark:text-[#75bd97] border border-[#0B5D3B]/20">
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.announcements.subtitle') }}
      </p>
    </div>

    <!-- 4. Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Left: Search & Filter Toggle -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="announcement-search"
            :placeholder="t('admin.announcements.searchPlaceholder')"
            :model-value="searchQuery"
            class="w-full text-sm"
            @update:model-value="searchQuery = $event; currentPage = 1"
            @keyup.enter="handleSearch"
          >
            <template #icon-left>
              <Search class="h-4 w-4 text-slate-400" />
            </template>
          </AppInput>
        </div>

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

      <!-- Right: Action Buttons -->
      <div class="flex items-center gap-2 shrink-0">
        <button
          type="button"
          title="Export CSV"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer"
          @click="exportAnnouncementsCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <button
          type="button"
          title="Refresh List"
          :disabled="isRefreshing || loading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="fetchList(true)"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || loading ? 'animate-spin' : ''" />
        </button>

        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.announcements.newAnnouncement') }}
        </AppButton>
      </div>
    </div>

    <!-- 5. Collapsible Filter Panel -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-3 gap-3 transition-all duration-200"
    >
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.announcements.table.type') }}
        </label>
        <AppSelect
          v-model="selectedType"
          :options="typeFilterOptions"
          class="w-full text-xs"
          @update:model-value="handleSearch"
        />
      </div>

      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.announcements.table.audience') }}
        </label>
        <AppSelect
          v-model="selectedAudience"
          :options="audienceFilterOptions"
          class="w-full text-xs"
          @update:model-value="handleSearch"
        />
      </div>

      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ t('admin.announcements.table.status') }}
        </label>
        <AppSelect
          v-model="selectedStatus"
          :options="statusFilterOptions"
          class="w-full text-xs"
          @update:model-value="handleSearch"
        />
      </div>

      <div class="sm:col-span-3 flex justify-end pt-1">
        <button
          type="button"
          class="text-xs font-bold text-[#0B5D3B] dark:text-[#75bd97] hover:underline cursor-pointer"
          @click="handleResetFilters"
        >
          {{ t('common.resetFilters') }}
        </button>
      </div>
    </div>

    <!-- 6. Floating Bulk Actions Bar -->
    <Transition name="fade">
      <div
        v-if="selectedIds.length > 0"
        class="bg-slate-900 dark:bg-slate-800 text-white px-4 py-2.5 rounded-2xl shadow-xl flex items-center justify-between gap-4 text-xs font-semibold animate-scale-in"
      >
        <div class="flex items-center gap-2">
          <CheckSquare class="h-4 w-4 text-[#75bd97]" />
          <span>{{ selectedIds.length }} {{ t('common.selected') || 'selected' }}</span>
        </div>

        <div class="flex items-center gap-2">
          <button
            type="button"
            class="px-2.5 py-1 rounded-lg bg-emerald-600/80 hover:bg-emerald-600 text-white font-bold transition-colors cursor-pointer inline-flex items-center gap-1"
            @click="handleBulkActivate"
          >
            <Check class="h-3.5 w-3.5" />
            <span>{{ t('common.activate') }}</span>
          </button>

          <button
            type="button"
            class="px-2.5 py-1 rounded-lg bg-amber-600/80 hover:bg-amber-600 text-white font-bold transition-colors cursor-pointer inline-flex items-center gap-1"
            @click="handleBulkDeactivate"
          >
            <X class="h-3.5 w-3.5" />
            <span>{{ t('common.deactivate') }}</span>
          </button>

          <button
            type="button"
            class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold transition-colors cursor-pointer inline-flex items-center gap-1"
            @click="handleBulkDelete"
          >
            <Trash2 class="h-3.5 w-3.5" />
            <span>{{ t('common.delete') }}</span>
          </button>

          <button
            type="button"
            class="p-1 rounded-md text-slate-400 hover:text-white transition-colors cursor-pointer ml-1"
            title="Clear Selection"
            @click="selectedIds = []"
          >
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>
    </Transition>

    <!-- 7. Data Table Card -->
    <div class="bg-white dark:bg-[#111827] rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-2xs overflow-hidden">
      <!-- Loading Skeleton -->
      <div v-if="loading" class="p-6 space-y-4">
        <AppSkeleton v-for="i in 5" :key="i" class="h-14 w-full rounded-xl" />
      </div>

      <!-- Empty State -->
      <div
        v-else-if="announcements.length === 0"
        class="py-16 text-center space-y-3 px-4"
      >
        <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center">
          <Megaphone class="h-7 w-7" />
        </div>
        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
          {{ t('admin.announcements.noAnnouncements') }}
        </h3>
        <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto">
          {{ t('admin.announcements.noAnnouncementsDesc') }}
        </p>
        <AppButton variant="primary" size="sm" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4" />
          </template>
          {{ t('admin.announcements.newAnnouncement') }}
        </AppButton>
      </div>

      <!-- Table Content -->
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="border-b border-slate-200/90 dark:border-slate-800 bg-slate-50/80 dark:bg-[#0B1120] text-slate-600 dark:text-slate-400 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3.5 px-4 w-10 text-center">
                <button
                  type="button"
                  class="cursor-pointer"
                  @click="toggleSelectAll"
                >
                  <CheckSquare v-if="isAllCurrentPageSelected" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                  <Square v-else class="h-4 w-4 text-slate-400" />
                </button>
              </th>
              <th class="py-3.5 px-4 min-w-[320px]">{{ t('admin.announcements.table.announcement') }}</th>
              <th class="py-3.5 px-4 min-w-[130px]">{{ t('admin.announcements.table.type') }}</th>
              <th class="py-3.5 px-4 min-w-[140px]">{{ t('admin.announcements.table.audience') }}</th>
              <th class="py-3.5 px-4 min-w-[180px]">{{ t('admin.announcements.table.validity') }}</th>
              <th class="py-3.5 px-4 min-w-[90px] text-center">{{ t('admin.announcements.table.status') }}</th>
              <th class="py-3.5 px-4 w-16 text-right">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            <tr
              v-for="item in announcements"
              :key="item.id"
              class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors"
            >
              <!-- Checkbox -->
              <td class="py-3.5 px-4 text-center">
                <button
                  type="button"
                  class="cursor-pointer"
                  @click="toggleSelect(item.id)"
                >
                  <CheckSquare v-if="selectedIds.includes(item.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                  <Square v-else class="h-4 w-4 text-slate-400" />
                </button>
              </td>

              <!-- Announcement Headline, Snippet & Author -->
              <td class="py-3.5 px-4">
                <div class="space-y-1">
                  <div
                    class="font-bold text-slate-900 dark:text-slate-100 text-sm hover:text-[#0B5D3B] dark:hover:text-[#75bd97] cursor-pointer transition-colors"
                    @click="openViewModal(item)"
                  >
                    {{ item.title }}
                  </div>
                  <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-2 max-w-lg leading-relaxed">
                    {{ item.body }}
                  </p>
                  <div class="flex items-center gap-2 pt-0.5 text-[10px] text-slate-400">
                    <span v-if="item.created_at">
                      {{ t('common.createdOn') }} {{ formatDate(item.created_at) }}
                    </span>
                    <span v-if="item.creator || item.created_by_user">&bull;</span>
                    <span v-if="item.creator || item.created_by_user" class="font-medium text-slate-600 dark:text-slate-300">
                      {{ item.creator?.full_name || item.created_by_user?.full_name }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Notice Type Badge -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
                  :class="{
                    'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200/60 dark:border-rose-900/60': item.type === 'urgent',
                    'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 border border-amber-200/60 dark:border-amber-900/60': item.type === 'warning',
                    'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-900/60': item.type === 'maintenance',
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-900/60': item.type === 'success',
                    'bg-[#E8F4EE] text-[#0B5D3B] dark:bg-[#0B5D3B]/20 dark:text-[#75bd97] border border-[#0B5D3B]/20': item.type === 'info',
                  }"
                >
                  <component :is="getTypeIcon(item.type)" class="h-3.5 w-3.5 shrink-0" />
                  <span>{{ t(`admin.announcements.types.${item.type}`) || item.type }}</span>
                </span>
              </td>

              <!-- Target Audience Badge -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200/60 dark:border-slate-700">
                  <Users class="h-3 w-3 text-slate-400" />
                  <span>{{ t(`admin.announcements.audiences.${item.audience}`) || item.audience }}</span>
                </span>
              </td>

              <!-- Validity Schedule Window -->
              <td class="py-3.5 px-4 whitespace-nowrap text-slate-600 dark:text-slate-300 text-[11px]">
                <div v-if="item.starts_at || item.ends_at" class="space-y-0.5">
                  <div v-if="item.starts_at" class="flex items-center gap-1.5">
                    <span class="text-slate-400 text-[10px] uppercase font-bold">From:</span>
                    <span class="font-medium">{{ formatDate(item.starts_at) }}</span>
                  </div>
                  <div v-if="item.ends_at" class="flex items-center gap-1.5">
                    <span class="text-slate-400 text-[10px] uppercase font-bold">To:</span>
                    <span class="font-medium">{{ formatDate(item.ends_at) }}</span>
                  </div>
                </div>
                <span v-else class="text-slate-400 italic">
                  {{ t('admin.announcements.indefinite') }}
                </span>
              </td>

              <!-- Status Toggle Switch -->
              <td class="py-3.5 px-4 text-center">
                <button
                  type="button"
                  class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                  :class="item.is_active ? 'bg-[#0B5D3B]' : 'bg-slate-300 dark:bg-slate-700'"
                  :aria-label="t('common.toggleStatus')"
                  @click="handleToggleActive(item)"
                >
                  <span
                    class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                    :class="item.is_active ? 'translate-x-4' : 'translate-x-0'"
                  />
                </button>
              </td>

              <!-- Action Menu -->
              <td class="py-3.5 px-4 text-center">
                <AppActionMenu v-slot="{ close }" width-class="w-52">
                  <!-- 1. View Details -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); openViewModal(item)"
                  >
                    <Eye class="h-4 w-4 text-slate-400" />
                    <span>{{ t('common.viewDetails') }}</span>
                  </button>

                  <!-- 2. Edit Announcement -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); openEditModal(item)"
                  >
                    <Edit2 class="h-4 w-4 text-slate-400" />
                    <span>{{ t('common.edit') }}</span>
                  </button>

                  <!-- 3. Toggle Status -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); handleToggleActive(item)"
                  >
                    <Check v-if="!item.is_active" class="h-4 w-4 text-emerald-500" />
                    <X v-else class="h-4 w-4 text-amber-500" />
                    <span>{{ item.is_active ? t('common.deactivate') : t('common.activate') }}</span>
                  </button>

                  <div class="h-px bg-slate-100 dark:bg-slate-800 my-1" />

                  <!-- 4. Delete -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/30 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); handleDelete(item)"
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

      <!-- Pagination -->
      <div v-if="totalItems > perPage" class="p-4 border-t border-slate-200/90 dark:border-slate-800">
        <AppPagination
          :current-page="currentPage"
          :total-pages="lastPage"
          :total-items="totalItems"
          :per-page="perPage"
          @page-change="handlePageChange"
        />
      </div>
    </div>

    <!-- 8. View Details Modal -->
    <AppModal
      v-model="isViewModalOpen"
      :title="t('admin.announcements.title')"
      size="lg"
    >
      <div v-if="viewingItem" class="space-y-4 text-xs">
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 space-y-2">
          <div class="flex items-center justify-between gap-2">
            <span
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold"
              :class="{
                'bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300': viewingItem.type === 'urgent',
                'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300': viewingItem.type === 'warning',
                'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/80 dark:text-indigo-300': viewingItem.type === 'maintenance',
                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300': viewingItem.type === 'success',
                'bg-[#E8F4EE] text-[#0B5D3B] dark:bg-[#0B5D3B]/30 dark:text-[#75bd97]': viewingItem.type === 'info',
              }"
            >
              <component :is="getTypeIcon(viewingItem.type)" class="h-3.5 w-3.5" />
              {{ t(`admin.announcements.types.${viewingItem.type}`) || viewingItem.type }}
            </span>

            <span
              class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
              :class="viewingItem.is_active ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-slate-700 dark:bg-slate-700 dark:text-slate-300'"
            >
              {{ viewingItem.is_active ? t('common.active') : t('common.inactive') }}
            </span>
          </div>

          <h2 class="text-base font-bold text-slate-900 dark:text-white">
            {{ viewingItem.title }}
          </h2>

          <p class="text-xs text-slate-700 dark:text-slate-300 whitespace-pre-wrap leading-relaxed">
            {{ viewingItem.body }}
          </p>
        </div>

        <div class="grid grid-cols-2 gap-3 text-[11px]">
          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <span class="text-slate-400 font-bold block mb-0.5 uppercase">{{ t('admin.announcements.table.audience') }}</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">
              {{ t(`admin.announcements.audiences.${viewingItem.audience}`) || viewingItem.audience }}
            </span>
          </div>

          <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
            <span class="text-slate-400 font-bold block mb-0.5 uppercase">{{ t('admin.announcements.table.validity') }}</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">
              {{ viewingItem.starts_at ? formatDate(viewingItem.starts_at) : 'Immediate' }} &rarr; {{ viewingItem.ends_at ? formatDate(viewingItem.ends_at) : 'Indefinite' }}
            </span>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <AppButton variant="outline" size="sm" @click="isViewModalOpen = false">
            {{ t('common.close') }}
          </AppButton>
          <AppButton variant="primary" size="sm" @click="isViewModalOpen = false; openEditModal(viewingItem)">
            <template #icon-left>
              <Edit2 class="h-3.5 w-3.5" />
            </template>
            {{ t('common.edit') }}
          </AppButton>
        </div>
      </div>
    </AppModal>

    <!-- 9. Create / Edit Modal -->
    <AppModal
      v-model="isModalOpen"
      :title="modalMode === 'create' ? t('admin.announcements.newAnnouncement') : t('admin.announcements.editAnnouncement')"
      size="lg"
    >
      <form class="space-y-4" @submit.prevent="handleSubmit">
        <!-- Title -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            {{ t('admin.announcements.fields.title') }} *
          </label>
          <AppInput
            v-model="form.title"
            :placeholder="t('admin.announcements.fields.titlePlaceholder')"
            :error="formErrors.title"
          />
        </div>

        <!-- Body -->
        <div>
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            {{ t('admin.announcements.fields.body') }} *
          </label>
          <AppTextarea
            v-model="form.body"
            :rows="3"
            :placeholder="t('admin.announcements.fields.bodyPlaceholder')"
            :error="formErrors.body"
          />
        </div>

        <!-- Type & Audience Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.announcements.fields.type') }}
            </label>
            <AppSelect
              v-model="form.type"
              :options="typeFilterOptions.filter(o => o.value !== 'all')"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.announcements.fields.audience') }}
            </label>
            <AppSelect
              v-model="form.audience"
              :options="audienceFilterOptions"
            />
          </div>
        </div>

        <!-- Start & End Validity Dates -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.announcements.fields.startsAt') }}
            </label>
            <input
              v-model="form.starts_at"
              type="datetime-local"
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-[#0B5D3B]"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
              {{ t('admin.announcements.fields.endsAt') }}
            </label>
            <input
              v-model="form.ends_at"
              type="datetime-local"
              class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-[#0B5D3B]"
            />
          </div>
        </div>

        <!-- Is Active Toggle -->
        <div class="flex items-center gap-2 pt-1">
          <input
            id="announcement-active-check"
            v-model="form.is_active"
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-[#0B5D3B] focus:ring-[#0B5D3B] cursor-pointer"
          />
          <label for="announcement-active-check" class="text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
            {{ t('admin.announcements.fields.publishImmediately') }}
          </label>
        </div>

        <!-- Live Preview Box -->
        <div class="pt-3 border-t border-slate-100 dark:border-slate-800">
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 flex items-center gap-1.5">
            <Eye class="h-3.5 w-3.5" />
            {{ t('admin.announcements.livePreview') }}
          </p>

          <div
            class="rounded-xl p-3 border text-xs flex items-center gap-2.5"
            :class="{
              'bg-rose-500/10 border-rose-500/30 text-rose-900 dark:text-rose-100': form.type === 'urgent',
              'bg-amber-500/10 border-amber-500/30 text-amber-900 dark:text-amber-100': form.type === 'warning',
              'bg-indigo-500/10 border-indigo-500/30 text-indigo-900 dark:text-indigo-100': form.type === 'maintenance',
              'bg-emerald-500/10 border-emerald-500/30 text-emerald-900 dark:text-emerald-100': form.type === 'success',
              'bg-[#0B5D3B]/10 border-[#0B5D3B]/30 text-[#073824] dark:text-emerald-100': form.type === 'info',
            }"
          >
            <component :is="getTypeIcon(form.type)" class="h-4 w-4 shrink-0" />
            <div class="min-w-0 flex-1">
              <span class="font-bold mr-1.5">{{ form.title || t('admin.announcements.sampleTitle') }}:</span>
              <span>{{ form.body || t('admin.announcements.sampleBody') }}</span>
            </div>
          </div>
        </div>

        <!-- Footer actions -->
        <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
          <AppButton
            type="button"
            variant="outline"
            @click="isModalOpen = false"
          >
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton
            type="submit"
            variant="primary"
            :loading="isSubmitting"
          >
            {{ modalMode === 'create' ? t('admin.announcements.publish') : t('common.save') }}
          </AppButton>
        </div>
      </form>
    </AppModal>
  </div>
</template>
