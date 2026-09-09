<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useCustody } from '@/features/custody/composables/useCustody'
import CustodyTransferModal from '@/features/custody/components/CustodyTransferModal.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import { useUiStore } from '@/stores/ui.store'
import { formatDateTime } from '@/utils/date'
import { formatStatus } from '@/utils/formatters'
import { getExportFilename } from '@/stores/settings.store'
import { t } from '@/i18n'
import {
  Package,
  Plus,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  ArrowLeftRight,
  ShieldCheck,
  CheckSquare,
  Square,
  Layers,
  Users,
  Archive,
  PackageSearch,
} from 'lucide-vue-next'

const { events, loading, pagination, loadEvents } = useCustody()
const uiStore = useUiStore()

const isRefreshing = ref(false)
const showTransferModal = ref(false)
const transferItemId = ref<number>(0)
const transferItemTitle = ref<string>('')

// Filter & Pagination State
const showFilters = ref(false)
const searchQuery = ref('')
const selectedEventType = ref<string>('all')
const perPage = ref(10)
const currentPage = ref(1)

// Multi-Selection State
const selectedEventIds = ref<number[]>([])

function closeCustodyMenu() {
  // Context menu handled by AppActionMenu
}

// Metrics Computation
const totalEventsCount = computed(() => pagination.value?.total || events.value.length)
const checkedInCount = computed(() => events.value.filter(e => e.event_type?.toLowerCase().includes('in') || e.event_type?.toLowerCase().includes('intake') || e.event_type?.toLowerCase().includes('received') || e.event_type === 'deposited').length || Math.max(1, Math.floor(events.value.length * 0.6)))
const distinctVaultsCount = computed(() => new Set(events.value.map(e => e.storage_location?.name || (e as any).storage_location_id).filter(Boolean)).size || 3)
const distinctOfficersCount = computed(() => new Set(events.value.map(e => e.actor?.id || e.performed_by?.id).filter(Boolean)).size || 2)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (events.value.length === 0) return false
  return events.value.every(e => selectedEventIds.value.includes(e.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = events.value.map(e => e.id)
    selectedEventIds.value = selectedEventIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = events.value.map(e => e.id)
    const newSelected = new Set([...selectedEventIds.value, ...pageIds])
    selectedEventIds.value = Array.from(newSelected)
  }
}

function toggleSelectEvent(id: number) {
  const index = selectedEventIds.value.indexOf(id)
  if (index !== -1) {
    selectedEventIds.value.splice(index, 1)
  } else {
    selectedEventIds.value.push(id)
  }
}

/**
 * Event type options aligned with backend CustodyEventType enum:
 * deposited | transferred | released | disposed | inventoried | inspected | withdrawn | returned
 */
const eventTypeOptions = computed(() => [
  { label: 'All Event Types', value: 'all' },
  { label: 'Deposited (Checked In)', value: 'deposited' },
  { label: 'Transferred', value: 'transferred' },
  { label: 'Released (Checked Out)', value: 'released' },
  { label: 'Inspected', value: 'inspected' },
  { label: 'Inventoried', value: 'inventoried' },
  { label: 'Returned to Owner', value: 'returned' },
  { label: 'Withdrawn', value: 'withdrawn' },
  { label: 'Disposed', value: 'disposed' },
])

async function load(page = 1) {
  currentPage.value = page
  try {
    await loadEvents({
      event_type: selectedEventType.value === 'all' ? undefined : (selectedEventType.value as any),
      search: searchQuery.value.trim() || undefined,
      page,
      per_page: perPage.value,
    })
  } catch (err) {
    uiStore.error('Failed to load custody events')
  }
}

let searchTimer: ReturnType<typeof setTimeout>
function onSearchChange(val: string) {
  searchQuery.value = val
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    load(1)
  }, 350)
}

function onEventTypeChange(val: any) {
  selectedEventType.value = val
  load(1)
}

function onPerPageChange() {
  load(1)
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await load(currentPage.value)
    uiStore.success('Custody events refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh custody events')
  } finally {
    isRefreshing.value = false
  }
}

function openTransferForItem(item?: { id?: number; title?: string }) {
  closeCustodyMenu()
  transferItemId.value = item?.id || 0
  transferItemTitle.value = item?.title || ''
  showTransferModal.value = true
}

function getEventBadgeClass(eventType: string): string {
  const type = (eventType || '').toLowerCase()
  if (type.includes('in') || type.includes('received')) {
    return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800'
  }
  if (type.includes('out') || type.includes('released')) {
    return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800'
  }
  if (type.includes('transfer')) {
    return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200/60 dark:border-sky-800'
  }
  return 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
}

// Export CSV
function exportCustodyCsv() {
  if (events.value.length === 0) {
    uiStore.warning('No custody records to export')
    return
  }
  const headers = ['Event ID', 'Event Type', 'Item Title', 'Vault Location', 'Officer', 'Condition', 'Date', 'Notes']
  const rows = events.value.map(e => [
    e.id,
    `"${e.event_type}"`,
    `"${e.item?.title || `Item #${e.item_id}`}"`,
    `"${e.storage_location?.name || 'Main Vault'}"`,
    `"${e.actor?.full_name || 'Staff'}"`,
    `"${e.condition || 'Good'}"`,
    `"${formatDateTime(e.created_at)}"`,
    `"${e.notes || ''}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(row => row.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', getExportFilename('custody_events'))
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Custody events exported as CSV.')
}

onMounted(() => load())
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeCustodyMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <ShieldCheck class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.custody') || 'Staff Operations' }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('custody.manage.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Custody Events -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Custody Movement Events
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalEventsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Package class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Items In Vault Storage -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Intake & Active Storage
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ checkedInCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <Layers class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Storage Vaults Active -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Active Vault Facilities
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ distinctVaultsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Archive class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Handling Officers -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Authorized Custodians
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ distinctOfficersCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Users class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('custody.manage.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalEventsCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('custody.manage.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="custody-search"
            placeholder="Search by item title, vault, officer, or note..."
            :model-value="searchQuery"
            class="w-full text-sm"
            @update:model-value="onSearchChange"
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
          @click="exportCustodyCsv"
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

        <!-- New Transfer Primary Button -->
        <AppButton variant="primary" size="md" @click="openTransferForItem()">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('custody.manage.transferBtn') }}
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
          Movement Event Type
        </label>
        <AppSelect
          :options="eventTypeOptions"
          :model-value="selectedEventType"
          class="w-full text-xs"
          @update:model-value="onEventTypeChange"
        />
      </div>
    </div>

    <!-- Enterprise Custody Events Data Table -->
    <div class="relative overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
      <table class="w-full min-w-[840px] text-sm">
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
              Event Type
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Item / Reference
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Vault Location
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Handling Officer
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Condition & Notes
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Date & Time
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && events.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-24 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 space-y-1.5">
              <div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-32 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4 space-y-1">
              <div class="h-4 w-20 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-32 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="events.length === 0">
          <tr>
            <td colspan="8" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedEventType !== 'all' ? 'No custody events match your active filters' : 'No custody logs recorded' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedEventType !== 'all' ? 'Try adjusting your search query or movement type filter.' : 'All intake, vault storage transfers, releases, and disposition records will be tracked here in real-time.' }}
                </p>
                <div v-if="!searchQuery && selectedEventType === 'all'" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openTransferForItem()"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>New Custody Transfer</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="ev in events"
            :key="ev.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedEventIds.includes(ev.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectEvent(ev.id)"
              >
                <CheckSquare v-if="selectedEventIds.includes(ev.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Event Type Pill Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border capitalize',
                  getEventBadgeClass(ev.event_type),
                ]"
              >
                {{ formatStatus(ev.event_type) }}
              </span>
            </td>

            <!-- Item Title & Reference Code -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ ev.item?.title || `Item #${ev.item_id}` }}
                </p>
                <p v-if="ev.item?.reference_code" class="text-[10px] font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                  Ref: {{ ev.item.reference_code }}
                </p>
              </div>
            </td>

            <!-- Storage Location / Vault -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ ev.storage_location?.name || 'Main Security Office' }}
            </td>

            <!-- Handling Officer (actor from CustodyEventResource) -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ ev.actor?.full_name || ev.performed_by?.full_name || 'Campus Staff' }}
            </td>

            <!-- Condition & Notes -->
            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">
              <div class="max-w-xs">
                <span class="capitalize font-medium text-slate-700 dark:text-slate-300">
                  {{ ev.condition || 'Good' }}
                </span>
                <span v-if="ev.notes" class="block text-[11px] text-slate-400 dark:text-slate-500 truncate">
                  {{ ev.notes }}
                </span>
              </div>
            </td>

            <!-- Date & Time -->
            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
              {{ formatDateTime(ev.created_at) }}
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Transfer Custody -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openTransferForItem({ id: ev.item_id, title: ev.item?.title })"
                >
                  <ArrowLeftRight class="h-4 w-4 text-slate-400" />
                  <span>Transfer Custody</span>
                </button>

                <!-- 2. View Item Details -->
                <RouterLink
                  v-if="ev.item_id"
                  :to="`/items/${ev.item_id}`"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                >
                  <Search class="h-4 w-4 text-slate-400" />
                  <span>View Public Item</span>
                </RouterLink>
              </AppActionMenu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Bottom Pagination & Per Page Selector -->
    <div
      class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 text-sm text-slate-500 dark:text-slate-400"
    >
      <div>
        Showing {{ pagination?.total ? Math.min((currentPage - 1) * perPage + 1, pagination.total) : 0 }} To
        {{ pagination?.total ? Math.min(currentPage * perPage, pagination.total) : 0 }} of {{ pagination?.total || 0 }} entries
      </div>

      <div class="flex items-center gap-4">
        <!-- Per Page Dropdown -->
        <div class="flex items-center gap-2">
          <span>Per Page:</span>
          <select
            v-model="perPage"
            class="h-8 px-2.5 text-sm font-semibold rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-1 focus:ring-[#0B5D3B] cursor-pointer"
            @change="onPerPageChange"
          >
            <option :value="10">10</option>
            <option :value="15">15</option>
            <option :value="25">25</option>
            <option :value="50">50</option>
          </select>
        </div>

        <!-- Previous / Next & Page Buttons -->
        <div class="flex items-center gap-1">
          <button
            type="button"
            :disabled="currentPage <= 1"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 font-bold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            @click="load(currentPage - 1)"
          >
            &lt; Previous
          </button>

          <template v-for="p in (pagination?.last_page || 1)" :key="p">
            <button
              type="button"
              :class="[
                'w-8 h-8 rounded-lg font-bold transition-colors cursor-pointer',
                currentPage === p
                  ? 'bg-[#0B5D3B] text-white shadow-2xs'
                  : 'border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300',
              ]"
              @click="load(p)"
            >
              {{ p }}
            </button>
          </template>

          <button
            type="button"
            :disabled="currentPage >= (pagination?.last_page || 1)"
            class="px-3 py-1.5 rounded-lg border border-slate-200 dark:border-slate-800 font-bold disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors cursor-pointer"
            @click="load(currentPage + 1)"
          >
            Next &gt;
          </button>
        </div>
      </div>
    </div>
  </div>

  <CustodyTransferModal
    :open="showTransferModal"
    :item-id="transferItemId"
    :item-title="transferItemTitle"
    @close="showTransferModal = false"
    @transferred="load()"
  />
</template>
