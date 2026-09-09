<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useAdminDashboard as useAdminStats } from '@/features/admin/composables/useAdminDashboard'
import * as reportsApi from '@/features/admin/api/reports.api'
import type { GeneratedReport } from '@/features/admin/api/reports.api'
import { useUiStore } from '@/stores/ui.store'
import { formatDate } from '@/utils/date'
import { getExportFilename } from '@/stores/settings.store'
import { t } from '@/i18n'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  FileText,
  Plus,
  Download,
  RotateCcw,
  Search,
  Filter,
  X,
  CheckCircle2,
  Clock,
  CheckSquare,
  Square,
  Activity,
  PackageSearch,
} from 'lucide-vue-next'

const { stats, fetchStats } = useAdminStats()
const uiStore = useUiStore()

const reports = ref<GeneratedReport[]>([])
const reportsLoading = ref(false)
const isRefreshing = ref(false)
const generating = ref(false)
const downloadingId = ref<number | null>(null)
const createModalOpen = ref(false)
const pagination = ref<any>(null)

// Filter & Pagination State
const showFilters = ref(false)
const searchQuery = ref('')
const selectedFormat = ref<string>('all')
const selectedReportType = ref<string>('all')
const selectedStatus = ref<string>('all')
const selectedDateRange = ref<string>('all')
const perPage = ref(10)
const currentPage = ref(1)

// Multi-Selection State
const selectedReportIds = ref<number[]>([])

let pollTimer: ReturnType<typeof setInterval> | null = null

const form = ref({
  report_type: 'item_list',
  format: 'csv' as 'csv' | 'pdf',
  date_from: '',
  date_to: '',
})

// Metrics
const totalReportsCount = computed(() => pagination.value?.total ?? reports.value.length)
const readyReportsCount = computed(() => reports.value.filter(r => r.status === 'ready').length)
const pendingReportsCount = computed(() => reports.value.filter(r => r.status === 'pending' || r.status === 'generating' || r.status === 'processing').length)
const recoveryRate = computed(() => stats.value?.recovery_rate_percentage ?? 0)

// Filter Options
const formatFilterOptions = computed(() => [
  { label: 'All Formats', value: 'all' },
  { label: 'CSV Spreadsheet', value: 'csv' },
  { label: 'PDF Document', value: 'pdf' },
])

const reportTypeFilterOptions = computed(() => [
  { label: 'All Report Types', value: 'all' },
  { label: 'Item Inventory (item_list)', value: 'item_list' },
  { label: 'Claim Summary (claim_summary)', value: 'claim_summary' },
  { label: 'User Activity (user_activity)', value: 'user_activity' },
  { label: 'Resolution Turnaround (resolution_time)', value: 'resolution_time' },
  { label: 'Search Analytics (search_analytics)', value: 'search_analytics' },
  { label: 'Audit Export (audit_export)', value: 'audit_export' },
])

const statusFilterOptions = computed(() => [
  { label: 'All Statuses', value: 'all' },
  { label: 'Ready for Download', value: 'ready' },
  { label: 'Processing / Generating', value: 'generating' },
  { label: 'Pending Queue', value: 'pending' },
  { label: 'Failed', value: 'failed' },
])

const dateRangeFilterOptions = computed(() => [
  { label: 'All Time', value: 'all' },
  { label: 'Today', value: 'today' },
  { label: 'Last 7 Days', value: '7d' },
  { label: 'Last 30 Days', value: '30d' },
  { label: 'Last 90 Days', value: '90d' },
])

const hasActiveFilters = computed(() => {
  return (
    searchQuery.value.trim() !== '' ||
    selectedFormat.value !== 'all' ||
    selectedReportType.value !== 'all' ||
    selectedStatus.value !== 'all' ||
    selectedDateRange.value !== 'all'
  )
})

function clearFilters() {
  searchQuery.value = ''
  selectedFormat.value = 'all'
  selectedReportType.value = 'all'
  selectedStatus.value = 'all'
  selectedDateRange.value = 'all'
  loadReports(1)
}

const paginatedReports = computed(() => reports.value)
const totalPages = computed(() => pagination.value?.last_page || 1)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedReports.value.length === 0) return false
  return paginatedReports.value.every(r => selectedReportIds.value.includes(r.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedReports.value.map(r => r.id)
    selectedReportIds.value = selectedReportIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedReports.value.map(r => r.id)
    const newSelected = new Set([...selectedReportIds.value, ...pageIds])
    selectedReportIds.value = Array.from(newSelected)
  }
}

function toggleSelectReport(id: number) {
  const index = selectedReportIds.value.indexOf(id)
  if (index !== -1) {
    selectedReportIds.value.splice(index, 1)
  } else {
    selectedReportIds.value.push(id)
  }
}

const reportTypeOptions = computed(() => [
  { label: `${t('admin.reports.reportType')} - Item Inventory (item_list)`, value: 'item_list' },
  { label: `${t('claims.title')} Summary (claim_summary)`, value: 'claim_summary' },
  { label: `${t('nav.users')} Activity (user_activity)`, value: 'user_activity' },
  { label: `${t('admin.dashboard.avgTurnaround')} (resolution_time)`, value: 'resolution_time' },
  { label: `${t('admin.dashboard.searchFailRate')} (search_analytics)`, value: 'search_analytics' },
  { label: `${t('admin.auditLogs.title')} (audit_export)`, value: 'audit_export' },
])

const formatOptions = computed(() => [
  { label: 'CSV Spreadsheet (.csv)', value: 'csv' },
  { label: 'PDF Document (.pdf)', value: 'pdf' },
])

async function loadReports(page = 1) {
  reportsLoading.value = true
  currentPage.value = page
  try {
    const params: Record<string, any> = {
      page,
      per_page: perPage.value,
    }
    if (selectedReportType.value !== 'all') params.report_type = selectedReportType.value
    if (selectedFormat.value !== 'all') params.format = selectedFormat.value
    if (selectedStatus.value !== 'all') params.status = selectedStatus.value
    if (searchQuery.value.trim()) params.search = searchQuery.value.trim()
    if (selectedDateRange.value !== 'all') {
      const now = new Date()
      if (selectedDateRange.value === 'today') {
        params.date_from = now.toISOString().slice(0, 10)
        params.date_to = now.toISOString().slice(0, 10)
      } else if (selectedDateRange.value === '7d') {
        const past = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        params.date_from = past.toISOString().slice(0, 10)
        params.date_to = now.toISOString().slice(0, 10)
      } else if (selectedDateRange.value === '30d') {
        const past = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
        params.date_from = past.toISOString().slice(0, 10)
        params.date_to = now.toISOString().slice(0, 10)
      } else if (selectedDateRange.value === '90d') {
        const past = new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000)
        params.date_from = past.toISOString().slice(0, 10)
        params.date_to = now.toISOString().slice(0, 10)
      }
    }

    const res = await reportsApi.getReports(params)
    reports.value = res.data
    pagination.value = res.meta
  } catch {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    reportsLoading.value = false
  }
}

watch([selectedFormat, selectedReportType, selectedStatus, selectedDateRange], () => {
  loadReports(1)
})

let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchDebounceTimer) clearTimeout(searchDebounceTimer)
  searchDebounceTimer = setTimeout(() => {
    loadReports(1)
  }, 300)
})

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await Promise.all([fetchStats(), loadReports(currentPage.value)])
    uiStore.success('Reports refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh reports')
  } finally {
    isRefreshing.value = false
  }
}

function applyDatePreset(preset: 'today' | '7days' | 'month' | 'all') {
  const now = new Date()
  if (preset === 'today') {
    const todayStr = now.toISOString().slice(0, 10)
    form.value.date_from = todayStr
    form.value.date_to = todayStr
  } else if (preset === '7days') {
    const prev = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
    form.value.date_from = prev.toISOString().slice(0, 10)
    form.value.date_to = now.toISOString().slice(0, 10)
  } else if (preset === 'month') {
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1)
    form.value.date_from = firstDay.toISOString().slice(0, 10)
    form.value.date_to = now.toISOString().slice(0, 10)
  } else {
    form.value.date_from = ''
    form.value.date_to = ''
  }
}

async function handleGenerateReport() {
  generating.value = true
  try {
    const payload: reportsApi.GenerateReportPayload = {
      report_type: form.value.report_type,
      format: form.value.format,
      filters: {
        date_from: form.value.date_from || undefined,
        date_to: form.value.date_to || undefined,
      },
    }

    await reportsApi.generateReport(payload)
    uiStore.success(t('admin.reports.generatedSuccess'))
    createModalOpen.value = false
    await loadReports(1)
  } catch {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    generating.value = false
  }
}

async function handleDownload(report: GeneratedReport) {
  downloadingId.value = report.id
  try {
    const ext = report.format || 'csv'
    await reportsApi.downloadReport(report.id, getExportFilename(`${report.report_type}_${report.id}`, ext))
    uiStore.success(t('admin.reports.downloadedSuccess'))
  } catch {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    downloadingId.value = null
  }
}

function getStatusBadgeClass(status: string): string {
  switch (status) {
    case 'ready':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800'
    case 'generating':
    case 'pending':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/60 dark:border-amber-800'
    case 'failed':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800'
    default:
      return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
  }
}

onMounted(() => {
  fetchStats()
  loadReports(1)

  pollTimer = setInterval(() => {
    const hasPending = reports.value.some(r => r.status === 'pending' || r.status === 'generating')
    if (hasPending) {
      loadReports(currentPage.value)
    }
  }, 4000)
})

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer)
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <FileText class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.analytics') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.reports.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid (Benchmarked with Dashboard KPI Design) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Reports -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-28">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
              <FileText class="h-3.5 w-3.5" />
            </div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
              Total Reports
            </h3>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200/80 dark:border-slate-700/60">
            Registry
          </span>
        </div>
        <div>
          <span class="text-2xl font-black font-mono tracking-tight text-slate-900 dark:text-white">
            {{ totalReportsCount }}
          </span>
          <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">All generated export documents</p>
        </div>
      </div>

      <!-- Card 2: Ready for Download -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-28">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
              <CheckCircle2 class="h-3.5 w-3.5" />
            </div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
              Ready to Download
            </h3>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
            Available
          </span>
        </div>
        <div>
          <span class="text-2xl font-black font-mono tracking-tight text-emerald-600 dark:text-emerald-400">
            {{ readyReportsCount }}
          </span>
          <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Processed & ready for export</p>
        </div>
      </div>

      <!-- Card 3: In Progress Queue -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-28">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400">
              <Clock class="h-3.5 w-3.5" />
            </div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
              Processing Queue
            </h3>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
            Active
          </span>
        </div>
        <div>
          <span class="text-2xl font-black font-mono tracking-tight text-amber-600 dark:text-amber-400">
            {{ pendingReportsCount }}
          </span>
          <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Queued background exports</p>
        </div>
      </div>

      <!-- Card 4: Campus Recovery Rate -->
      <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex flex-col justify-between h-28">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="h-7 w-7 rounded-lg flex items-center justify-center bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97]">
              <Activity class="h-3.5 w-3.5" />
            </div>
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-800 dark:text-slate-200 leading-tight">
              Recovery Rate
            </h3>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold font-mono bg-[#E8F4EE] dark:bg-[#153C2D] text-[#0B5D3B] dark:text-[#75bd97] border border-emerald-200/80 dark:border-emerald-800/60">
            Institutional
          </span>
        </div>
        <div>
          <span class="text-2xl font-black font-mono tracking-tight text-[#0B5D3B] dark:text-[#75bd97]">
            {{ recoveryRate }}%
          </span>
          <p class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5">Verified return ratio</p>
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.reports.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalReportsCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.reports.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="report-search"
            placeholder="Search reports by ID, type, or user..."
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
          <span>{{ showFilters ? 'Hide Filters' : 'Filter' }}</span>
        </button>
      </div>

      <!-- Action Buttons on Right -->
      <div class="flex items-center gap-2 shrink-0">
        <!-- Refresh Button -->
        <button
          type="button"
          title="Refresh List"
          :disabled="isRefreshing || reportsLoading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || reportsLoading ? 'animate-spin' : ''" />
        </button>

        <!-- Generate Report Button -->
        <AppButton variant="primary" size="md" @click="createModalOpen = true">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.reports.generateReport') }}
        </AppButton>
      </div>
    </div>

    <!-- Collapsible Filter Bar with 4 Distinct Filters -->
    <div
      v-if="showFilters"
      class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs space-y-3 transition-all duration-200"
    >
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- 1. Report Type -->
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
            Report Type
          </label>
          <AppSelect
            :options="reportTypeFilterOptions"
            :model-value="selectedReportType"
            class="w-full text-xs"
            @update:model-value="selectedReportType = String($event); currentPage = 1"
          />
        </div>

        <!-- 2. Status -->
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
            Status
          </label>
          <AppSelect
            :options="statusFilterOptions"
            :model-value="selectedStatus"
            class="w-full text-xs"
            @update:model-value="selectedStatus = String($event); currentPage = 1"
          />
        </div>

        <!-- 3. Date Created -->
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
            Date Created
          </label>
          <AppSelect
            :options="dateRangeFilterOptions"
            :model-value="selectedDateRange"
            class="w-full text-xs"
            @update:model-value="selectedDateRange = String($event); currentPage = 1"
          />
        </div>

        <!-- 4. File Format -->
        <div>
          <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
            File Format
          </label>
          <AppSelect
            :options="formatFilterOptions"
            :model-value="selectedFormat"
            class="w-full text-xs"
            @update:model-value="selectedFormat = String($event); currentPage = 1"
          />
        </div>
      </div>

      <div v-if="hasActiveFilters" class="flex justify-end pt-1">
        <button
          type="button"
          class="text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline inline-flex items-center gap-1 cursor-pointer"
          @click="clearFilters"
        >
          <X class="h-3 w-3" />
          <span>Reset All Filters</span>
        </button>
      </div>
    </div>

    <!-- Enterprise Reports Data Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
      <table class="w-full min-w-[780px] text-sm">
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
              Report ID
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.reports.reportType') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.user') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.format') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.time') }}
            </th>
            <th class="w-20 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="reportsLoading && reports.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-24 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="paginatedReports.length === 0">
          <tr>
            <td colspan="8" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ hasActiveFilters ? 'No reports match your filters' : 'No export reports generated yet' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ hasActiveFilters ? 'Try adjusting your search query or filter options.' : 'Generate exportable PDF, CSV, and audit reports for property and resolution analytics.' }}
                </p>
                <div v-if="!hasActiveFilters" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="createModalOpen = true"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Generate Report</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="report in paginatedReports"
            :key="report.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedReportIds.includes(report.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectReport(report.id)"
              >
                <CheckSquare v-if="selectedReportIds.includes(report.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Report ID (Monospace) -->
            <td class="px-4 py-3.5 font-mono text-slate-800 dark:text-slate-200 font-bold">
              #REP-{{ String(report.id).padStart(4, '0') }}
            </td>

            <!-- Report Type (Clean Text) -->
            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white capitalize">
              {{ report.report_type.replace(/_/g, ' ') }}
            </td>

            <!-- Requester / User -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              <div class="flex flex-col">
                <span class="font-bold text-slate-900 dark:text-white">
                  {{ report.requester?.full_name || report.requested_by_user?.full_name || report.generated_by?.full_name || (report.requested_by ? `Staff #${report.requested_by}` : 'Administrator') }}
                </span>
                <span v-if="report.requester?.email || report.requested_by_user?.email" class="text-[11px] text-slate-400 font-mono">
                  {{ report.requester?.email || report.requested_by_user?.email }}
                </span>
              </div>
            </td>

            <!-- Format -->
            <td class="px-4 py-3.5">
              <span class="font-mono uppercase font-bold text-[11px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                {{ report.format || 'CSV' }}
              </span>
            </td>

            <!-- Status Pill Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border uppercase',
                  getStatusBadgeClass(report.status),
                ]"
              >
                {{ report.status }}
              </span>
            </td>

            <!-- Created At -->
            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
              {{ formatDate(report.created_at) }}
            </td>

            <!-- Action Column with Direct Download & Action Menu -->
            <td class="px-4 py-3.5 text-center">
              <div class="flex items-center justify-center gap-1">
                <button
                  v-if="report.status === 'ready'"
                  type="button"
                  title="Download Document"
                  :disabled="downloadingId === report.id"
                  class="h-8 w-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60 flex items-center justify-center transition-colors cursor-pointer disabled:opacity-50"
                  @click="handleDownload(report)"
                >
                  <Download class="h-3.5 w-3.5" :class="downloadingId === report.id ? 'animate-bounce' : ''" />
                </button>
                <AppActionMenu v-slot="{ close }" width-class="w-52">
                  <button
                    v-if="report.status === 'ready'"
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                    @click="close(); handleDownload(report)"
                  >
                    <Download class="h-4 w-4 text-emerald-500" />
                    <span>Download ({{ (report.format || 'CSV').toUpperCase() }})</span>
                  </button>
                  <div v-else class="px-3.5 py-2 text-slate-400 text-xs italic">
                    Status: {{ report.status }}
                  </div>
                </AppActionMenu>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Pagination -->
    <AppPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      :total="totalReportsCount"
      :per-page="perPage"
      @update:current-page="loadReports($event)"
      @update:per-page="perPage = $event; loadReports(1)"
    />

    <!-- Create Report Modal -->
    <AppModal
      v-model:open="createModalOpen"
      :title="t('admin.reports.generateReport')"
      max-width="md"
    >
      <div class="space-y-4 py-2 text-xs">
        <AppSelect
          :label="t('admin.reports.reportType') + ' *'"
          :options="reportTypeOptions"
          :model-value="form.report_type"
          required
          @update:model-value="form.report_type = $event as string"
        />

        <AppSelect
          :label="t('admin.format') + ' *'"
          :options="formatOptions"
          :model-value="form.format"
          required
          @update:model-value="form.format = $event as any"
        />

        <!-- Quick Date Range Presets -->
        <div>
          <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 block mb-1.5">
            {{ t('admin.reports.dateRange') }}
          </label>
          <div class="flex items-center gap-1.5 flex-wrap mb-2">
            <button
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
              @click="applyDatePreset('today')"
            >
              {{ t('common.today') }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
              @click="applyDatePreset('7days')"
            >
              {{ t('common.last7Days') }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
              @click="applyDatePreset('month')"
            >
              {{ t('common.thisMonth') }}
            </button>
            <button
              type="button"
              class="px-2.5 py-1 rounded-md text-[11px] font-semibold border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors cursor-pointer"
              @click="applyDatePreset('all')"
            >
              {{ t('common.allTime') }}
            </button>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <AppInput
              id="report-date-from"
              type="date"
              :label="t('admin.reports.dateFrom')"
              :model-value="form.date_from"
              @update:model-value="form.date_from = $event"
            />
            <AppInput
              id="report-date-to"
              type="date"
              :label="t('admin.reports.dateTo')"
              :model-value="form.date_to"
              @update:model-value="form.date_to = $event"
            />
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="createModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton
            variant="primary"
            :loading="generating"
            @click="handleGenerateReport"
          >
            {{ t('admin.reports.generate') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
