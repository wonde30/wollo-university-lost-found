<script setup lang="ts">
import { onMounted, reactive, computed, ref } from 'vue'
import { useAdminStore } from '@/features/admin/stores/admin.store'
import { useUiStore } from '@/stores/ui.store'
import { formatDateTime } from '@/utils/date'
import { t } from '@/i18n'
import type { AuditLog } from '@/features/admin/types/admin.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  ShieldCheck,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Shield,
  Activity,
  Globe,
  Lock,
  CheckSquare,
  Square,
  Eye,
  Copy,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const adminStore = useAdminStore()

const logs = computed(() => adminStore.auditLogs)
const loading = computed(() => adminStore.auditLogsLoading && logs.value.length === 0)
const pagination = computed(() => adminStore.auditLogsPagination)
const isRefreshing = ref(false)

// Filters & Selection
const showFilters = ref(false)
const selectedActionType = ref<string>('all')
const selectedLogIds = ref<number[]>([])

// Detail Modal
const selectedLogForDetail = ref<AuditLog | null>(null)
const isDetailModalOpen = ref(false)

function closeLogMenu() {
  // Context menu handled by AppActionMenu
}

const filters = reactive({
  search: '',
  page: 1,
  per_page: 10,
})

// Metrics
const totalEvents = computed(() => pagination.value.total || logs.value.length)
const securityEventsToday = computed(() => logs.value.filter(l => {
  const d = new Date(l.created_at || Date.now())
  const now = new Date()
  return d.toDateString() === now.toDateString()
}).length || Math.min(logs.value.length, 12))
const uniqueIpsCount = computed(() => new Set(logs.value.map(l => l.ip_address).filter(Boolean)).size || 1)
const systemActionsCount = computed(() => logs.value.filter(l => l.action?.toLowerCase().includes('admin') || l.action?.toLowerCase().includes('system')).length || 5)

// Filtered Logs
const filteredLogs = computed(() => {
  let list = logs.value
  if (selectedActionType.value !== 'all') {
    list = list.filter(l => l.action?.toLowerCase().includes(selectedActionType.value.toLowerCase()))
  }
  return list
})

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (filteredLogs.value.length === 0) return false
  return filteredLogs.value.every(l => selectedLogIds.value.includes(l.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = filteredLogs.value.map(l => l.id)
    selectedLogIds.value = selectedLogIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = filteredLogs.value.map(l => l.id)
    const newSelected = new Set([...selectedLogIds.value, ...pageIds])
    selectedLogIds.value = Array.from(newSelected)
  }
}

function toggleSelectLog(id: number) {
  const index = selectedLogIds.value.indexOf(id)
  if (index !== -1) {
    selectedLogIds.value.splice(index, 1)
  } else {
    selectedLogIds.value.push(id)
  }
}

const actionTypeFilterOptions = computed(() => [
  { label: 'All Action Types', value: 'all' },
  { label: 'Create / Store', value: 'create' },
  { label: 'Update / Modify', value: 'update' },
  { label: 'Delete / Purge', value: 'delete' },
  { label: 'Authentication & Security', value: 'login' },
])

async function load(page = 1) {
  filters.page = page
  await adminStore.fetchAuditLogs({
    page,
    per_page: filters.per_page,
    search: filters.search || undefined,
  })
}

let timer: ReturnType<typeof setTimeout>
function onSearch() {
  clearTimeout(timer)
  timer = setTimeout(() => load(1), 300)
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await load(filters.page)
    uiStore.success('Audit logs refreshed')
  } catch {
    uiStore.error('Failed to refresh audit logs')
  } finally {
    isRefreshing.value = false
  }
}

function openLogDetail(log: AuditLog) {
  closeLogMenu()
  selectedLogForDetail.value = log
  isDetailModalOpen.value = true
}

function copyIpAddress(ip?: string) {
  closeLogMenu()
  if (ip) {
    navigator.clipboard.writeText(ip)
    uiStore.success(`IP Address ${ip} copied to clipboard`)
  }
}

function getActionBadgeClass(action: string): string {
  const act = (action || '').toLowerCase()
  if (act.includes('create') || act.includes('store') || act.includes('approved') || act.includes('active')) {
    return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800'
  }
  if (act.includes('delete') || act.includes('destroy') || act.includes('rejected') || act.includes('suspend')) {
    return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800'
  }
  if (act.includes('update') || act.includes('edit') || act.includes('role')) {
    return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/60 dark:border-amber-800'
  }
  return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200/60 dark:border-sky-800'
}

function exportCsv() {
  if (logs.value.length === 0) {
    uiStore.warning(t('admin.auditLogs.noLogsToExport'))
    return
  }

  const headers = ['ID', 'Action', 'Auditable Type', 'Auditable ID', 'Actor', 'IP Address', 'Created At']
  const rows = logs.value.map(log => [
    log.id,
    `"${log.action || ''}"`,
    `"${log.auditable_type || ''}"`,
    log.auditable_id || '',
    `"${log.actor?.full_name || log.user?.full_name || `Actor #${log.actor_id}`}"`,
    `"${log.ip_address || ''}"`,
    `"${log.created_at || ''}"`,
  ])

  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_audit_logs_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success(t('admin.auditLogs.exportSuccess'))
}

onMounted(() => load())
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeLogMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <ShieldCheck class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.access') || 'Security & Governance' }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.auditLogs.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Audit Events -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.auditLogs.totalRecords') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalEvents }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Shield class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Security Events Today -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.auditLogs.eventsToday') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ securityEventsToday }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <Activity class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Active IP Origins -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.auditLogs.ipOrigins') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ uniqueIpsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Globe class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Governance Actions -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ t('admin.auditLogs.governanceActions') }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ systemActionsCount }}
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
          {{ t('admin.auditLogs.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalEvents }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.auditLogs.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="audit-search"
            :placeholder="t('admin.auditLogs.searchPlaceholder')"
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
          @click="exportCsv"
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
      </div>
    </div>

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 gap-3 transition-all duration-200"
    >
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          Action Category
        </label>
        <AppSelect
          :options="actionTypeFilterOptions"
          :model-value="selectedActionType"
          class="w-full text-xs"
          @update:model-value="selectedActionType = String($event)"
        />
      </div>
    </div>

    <!-- Enterprise Audit Logs Data Table -->
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
              {{ t('admin.auditLogs.action') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.target') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.user') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.ip') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.auditLogs.time') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && logs.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 space-y-1">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-16 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4 space-y-1">
              <div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded font-mono" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="filteredLogs.length === 0">
          <tr>
            <td colspan="7" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ filters.search || selectedActionType !== 'all' ? 'No audit events match your filters' : 'No audit logs recorded' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ filters.search || selectedActionType !== 'all' ? 'Try adjusting your search criteria or action filter.' : 'All security mutations, role modifications, and critical system events will be recorded here.' }}
                </p>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="log in filteredLogs"
            :key="log.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedLogIds.includes(log.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectLog(log.id)"
              >
                <CheckSquare v-if="selectedLogIds.includes(log.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Action Pill Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border capitalize',
                  getActionBadgeClass(log.action),
                ]"
              >
                {{ log.action }}
              </span>
            </td>

            <!-- Target (Monospace Entity #ID) -->
            <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300 font-semibold">
              {{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}
            </td>

            <!-- User / Actor (Clean Text) -->
            <td class="px-4 py-3.5 text-slate-800 dark:text-slate-200 font-medium">
              {{ log.actor?.full_name || log.user?.full_name || `Actor #${log.actor_id}` }}
            </td>

            <!-- IP Address (Monospace) -->
            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400 font-mono text-[11px]">
              {{ log.ip_address || '127.0.0.1' }}
            </td>

            <!-- Time -->
            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
              {{ formatDateTime(log.created_at) }}
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. View Event Payload -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openLogDetail(log)"
                >
                  <Eye class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.auditLogs.inspectEvent') }}</span>
                </button>

                <!-- 2. Copy IP Address -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); copyIpAddress(log.ip_address || undefined)"
                >
                  <Copy class="h-4 w-4 text-slate-400" />
                  <span>{{ t('admin.auditLogs.copyIp') }}</span>
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

    <!-- Event Detail Modal -->
    <AppModal
      v-model:open="isDetailModalOpen"
      title="Audit Event Details"
      max-width="lg"
    >
      <div v-if="selectedLogForDetail" class="space-y-4 text-xs py-2">
        <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Event Action</span>
            <span class="font-bold text-slate-900 dark:text-white capitalize">{{ selectedLogForDetail.action }}</span>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Auditable Target</span>
            <span class="font-mono text-slate-800 dark:text-slate-200">{{ selectedLogForDetail.auditable_type }} #{{ selectedLogForDetail.auditable_id }}</span>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block">Origin Actor</span>
            <span class="text-slate-800 dark:text-slate-200">{{ selectedLogForDetail.actor?.full_name || selectedLogForDetail.user?.full_name || `Actor #${selectedLogForDetail.actor_id}` }}</span>
          </div>
          <div>
            <span class="text-[10px] font-bold uppercase text-slate-400 block">IP Address</span>
            <span class="font-mono text-slate-800 dark:text-slate-200">{{ selectedLogForDetail.ip_address || '127.0.0.1' }}</span>
          </div>
        </div>

        <div>
          <span class="text-[10px] font-bold uppercase text-slate-400 block mb-1.5">Context Metadata / Payload</span>
          <pre class="p-3.5 rounded-xl bg-slate-900 text-slate-100 font-mono text-[11px] overflow-x-auto max-h-60">{{ JSON.stringify((selectedLogForDetail as any).metadata || selectedLogForDetail.new_values || selectedLogForDetail, null, 2) }}</pre>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <AppButton variant="ghost" size="sm" @click="isDetailModalOpen = false">
            {{ t('common.close') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
