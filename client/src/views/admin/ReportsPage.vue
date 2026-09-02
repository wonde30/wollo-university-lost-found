<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAdminDashboard as useAdminStats } from '@/features/admin/composables/useAdminDashboard'
import * as reportsApi from '@/features/admin/api/reports.api'
import type { GeneratedReport } from '@/features/admin/api/reports.api'
import { useUiStore } from '@/stores/ui.store'
import { formatDate } from '@/utils/date'
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
const totalReportsCount = computed(() => pagination.value?.total || reports.value.length)
const readyReportsCount = computed(() => reports.value.filter(r => r.status === 'ready').length)
const pendingReportsCount = computed(() => reports.value.filter(r => r.status === 'pending' || r.status === 'generating').length)
const recoveryRate = computed(() => stats.value?.recovery_rate_percentage || 78)

// Filtered Reports
const filteredReports = computed(() => {
  let list = reports.value

  if (selectedFormat.value !== 'all') {
    list = list.filter(r => r.format === selectedFormat.value)
  }

  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      r =>
        String(r.id).includes(query) ||
        r.report_type.toLowerCase().includes(query) ||
        (r.requester?.full_name && r.requester.full_name.toLowerCase().includes(query))
    )
  }

  return list
})

const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredReports.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredReports.value.length / perPage.value) || 1)

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

const formatFilterOptions = computed(() => [
  { label: 'All Formats', value: 'all' },
  { label: 'CSV Spreadsheet', value: 'csv' },
  { label: 'PDF Document', value: 'pdf' },
])

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
  try {
    const res = await reportsApi.getReports({ page })
    reports.value = res.data
    pagination.value = res.meta
  } catch {
    uiStore.error(t('common.errorOccurred'))
  } finally {
    reportsLoading.value = false
  }
}

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
    await reportsApi.downloadReport(report.id, `wollo_${report.report_type}_${report.id}.${ext}`)
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

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Reports -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Generated Reports
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalReportsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <FileText class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Ready for Download -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Ready for Download
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ readyReportsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: In Progress Queue -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Generating Queue
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ pendingReportsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Clock class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: System Recovery Rate -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Campus Recovery Rate
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ recoveryRate }}%
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Activity class="h-5 w-5" />
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
          <span>{{ showFilters ? 'Hide Filter' : 'Filter' }}</span>
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

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 gap-3 transition-all duration-200"
    >
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
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
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
                  {{ searchQuery || selectedFormat !== 'all' ? 'No reports match your filters' : 'No export reports generated yet' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedFormat !== 'all' ? 'Try adjusting your search query or filter options.' : 'Generate exportable PDF, CSV, and audit reports for property and resolution analytics.' }}
                </p>
                <div v-if="!searchQuery && selectedFormat === 'all'" class="pt-2 flex items-center justify-center">
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
              {{ report.requester?.full_name || `User #${report.requested_by}` }}
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

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">
                <button
                  v-if="report.status === 'ready'"
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleDownload(report)"
                >
                  <Download class="h-4 w-4 text-emerald-500" />
                  <span>Download Document</span>
                </button>
                <div v-else class="px-3.5 py-2 text-slate-400">
                  Status: {{ report.status }}
                </div>
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
      :total="filteredReports.length"
      :per-page="perPage"
      @update:current-page="currentPage = $event"
      @update:per-page="perPage = $event; currentPage = 1"
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
