<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { useClaims } from '@/features/claims/composables/useClaims'
import { useReturnsStore } from '@/features/returns/stores/returns.store'
import { useUiStore } from '@/stores/ui.store'
import { getUserSummary, type UserSummaryData } from '@/features/auth/api/auth.api'
import { getErrorMessage } from '@/utils/error-handler'
import { t } from '@/i18n'
import type { Claim } from '@/features/claims/types/claim.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import { formatDate } from '@/utils/date'
import { claimStatusLabels } from '@/utils/formatters'
import { getExportFilename } from '@/stores/settings.store'
import {
  ClipboardList,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Eye,
  CheckCircle2,
  CheckSquare,
  Square,
  Clock,
  Handshake,
  PackageSearch,
} from 'lucide-vue-next'

const { claims, loading, pagination, loadClaims } = useClaims()
const returnsStore = useReturnsStore()
const uiStore = useUiStore()

const isRefreshing = ref(false)
const searchQuery = ref('')
const selectedStatus = ref<'all' | 'pending' | 'approved' | 'rejected'>('all')
const showFilters = ref(false)
const perPage = ref(10)
const currentPage = ref(1)

const userSummary = ref<UserSummaryData | null>(null)

const selectedClaim = ref<Claim | null>(null)
const detailModalOpen = ref(false)
const confirmingReturn = ref(false)

function closeClaimMenu() {
  // Context menu handled by AppActionMenu
}

// Multi-Selection State
const selectedClaimIds = ref<number[]>([])

// Metrics
const totalCount = computed(() => userSummary.value?.my_claims_count ?? (pagination.value?.total || claims.value.length))
const pendingCount = computed(() => userSummary.value?.active_claims_count ?? claims.value.filter(c => c.status === 'pending').length)
const approvedCount = computed(() => claims.value.filter(c => c.status === 'approved').length)
const resolvedCount = computed(() => userSummary.value?.resolved_claims_count ?? claims.value.filter(c => c.status === 'approved' && (c.return_record?.recipient_confirmed || c.return_record?.confirmed_at)).length)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (claims.value.length === 0) return false
  return claims.value.every(c => selectedClaimIds.value.includes(c.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = claims.value.map(c => c.id)
    selectedClaimIds.value = selectedClaimIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = claims.value.map(c => c.id)
    const newSelected = new Set([...selectedClaimIds.value, ...pageIds])
    selectedClaimIds.value = Array.from(newSelected)
  }
}

function toggleSelectClaim(id: number) {
  const index = selectedClaimIds.value.indexOf(id)
  if (index !== -1) {
    selectedClaimIds.value.splice(index, 1)
  } else {
    selectedClaimIds.value.push(id)
  }
}

const statusLabels = claimStatusLabels

const statusFilterOptions = computed(() => [
  { label: 'All Claim Statuses', value: 'all' },
  { label: statusLabels.pending, value: 'pending' },
  { label: statusLabels.under_review, value: 'under_review' },
  { label: statusLabels.approved, value: 'approved' },
  { label: statusLabels.rejected, value: 'rejected' },
  { label: statusLabels.reversed, value: 'reversed' },
])

async function load(page = 1) {
  currentPage.value = page
  try {
    const [summary] = await Promise.all([
      getUserSummary().catch(() => null),
      loadClaims({
        page,
        per_page: perPage.value,
        status: selectedStatus.value === 'all' ? undefined : (selectedStatus.value as any),
        search: searchQuery.value.trim() || undefined,
      }, true),
    ])
    if (summary) {
      userSummary.value = summary
    }
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to load claims'))
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

function onStatusChange(val: any) {
  selectedStatus.value = val
  load(1)
}

function onPerPageChange() {
  load(1)
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await load(currentPage.value)
    uiStore.success('Claims refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh claims')
  } finally {
    isRefreshing.value = false
  }
}

function openClaimDetail(claim: Claim) {
  closeClaimMenu()
  selectedClaim.value = claim
  detailModalOpen.value = true
}

async function handleConfirmCollection() {
  if (!selectedClaim.value?.return_record?.id) return

  confirmingReturn.value = true
  try {
    const updatedReturn = await returnsStore.confirmReturn(selectedClaim.value.return_record.id)
    if (selectedClaim.value.return_record) {
      selectedClaim.value.return_record = updatedReturn
    }
    uiStore.success(t('returns.confirmSuccess') || 'Item collection confirmed successfully.')
    await load(currentPage.value)
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    confirmingReturn.value = false
  }
}

function getClaimBadgeClass(status: string): string {
  switch (status) {
    case 'approved':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800'
    case 'under_review':
      return 'bg-sky-50 dark:bg-sky-950/60 text-sky-700 dark:text-sky-300 border-sky-200/60 dark:border-sky-800'
    case 'rejected':
    case 'reversed':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800'
    case 'cancelled':
      return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
    case 'pending':
    default:
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/60 dark:border-amber-800'
  }
}

// Export CSV
function exportClaimsCsv() {
  if (claims.value.length === 0) {
    uiStore.warning('No claims to export')
    return
  }
  const headers = ['Claim ID', 'Item Name', 'Item Ref', 'Submitted Date', 'Status']
  const rows = claims.value.map(c => [
    `"#CLM-${String(c.id).padStart(4, '0')}"`,
    `"${c.item?.title || ''}"`,
    `"${c.item?.reference_code || ''}"`,
    `"${formatDate(c.created_at)}"`,
    `"${c.status}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', getExportFilename('my_claims'))
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Claims exported as CSV.')
}

onMounted(() => load())
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeClaimMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <ClipboardList class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.claims') || 'Student Portal' }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('nav.myClaims') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Claims -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Claims Filed
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <ClipboardList class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Pending Verification -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Under Verification
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ pendingCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Clock class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Approved Claims -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Approved Claims
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ approvedCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Handed Over & Collected -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Handed Over & Collected
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ resolvedCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Handshake class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('nav.myClaims') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('claims.myClaims.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="claim-search"
            placeholder="Search by claim ID, item name, or reference..."
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
          @click="exportClaimsCsv"
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

        <!-- Browse Found Items Button -->
        <RouterLink to="/browse">
          <AppButton variant="primary" size="md">
            <template #icon-left>
              <Search class="h-4 w-4 mr-1" />
            </template>
            Browse Found Items
          </AppButton>
        </RouterLink>
      </div>
    </div>

    <!-- Collapsible Filter Bar -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 gap-3 transition-all duration-200"
    >
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          Claim Status
        </label>
        <AppSelect
          :options="statusFilterOptions"
          :model-value="selectedStatus"
          class="w-full text-xs"
          @update:model-value="onStatusChange"
        />
      </div>
    </div>

    <!-- Enterprise Claims Data Table -->
    <div class="relative overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
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
              Claim ID
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('claims.review.claimedItem') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('claims.review.submittedDate') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('claims.review.verificationStatus') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && claims.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4 space-y-1.5">
              <div class="h-4 w-36 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-48 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-24 bg-slate-200 dark:bg-slate-800 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="claims.length === 0">
          <tr>
            <td colspan="6" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedStatus !== 'all' ? 'No claims match your active filters' : 'No claims filed yet' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedStatus !== 'all' ? 'Try adjusting your search query or status filter.' : 'When you find your lost property among found items, submit an ownership claim to track verification and handover.' }}
                </p>
                <div v-if="!searchQuery && selectedStatus === 'all'" class="pt-2 flex items-center justify-center">
                  <RouterLink to="/items" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors">
                    Browse Found Items
                  </RouterLink>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="claim in claims"
            :key="claim.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedClaimIds.includes(claim.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectClaim(claim.id)"
              >
                <CheckSquare v-if="selectedClaimIds.includes(claim.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Claim ID (Monospace) -->
            <td class="px-4 py-3.5 font-mono text-slate-800 dark:text-slate-200 font-bold">
              #CLM-{{ String(claim.id).padStart(4, '0') }}
            </td>

            <!-- Claimed Item (Clean Text) -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ claim.item?.title || 'Unknown Item' }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                  Ref: {{ claim.item?.reference_code || 'N/A' }}
                  <span v-if="claim.item?.campus?.name"> • {{ claim.item.campus.name }}</span>
                </p>
              </div>
            </td>

            <!-- Submitted Date -->
            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-300">
              {{ formatDate(claim.created_at) }}
            </td>

            <!-- Status Pill Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border capitalize',
                  getClaimBadgeClass(claim.status),
                ]"
              >
                {{ statusLabels[claim.status] ?? claim.status }}
              </span>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. View Details -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openClaimDetail(claim)"
                >
                  <Eye class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.viewDetails') || 'View Details' }}</span>
                </button>

                <!-- 2. Track Item -->
                <RouterLink
                  v-if="claim.item?.id"
                  :to="`/items/${claim.item.id}`"
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

    <!-- Claim Detail Modal -->
    <AppModal
      v-model:open="detailModalOpen"
      :title="t('claims.review.claimDetails')"
      max-width="lg"
    >
      <div v-if="selectedClaim" class="space-y-4 text-xs">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
          <div>
            <p class="font-bold text-slate-900 dark:text-white text-sm">
              {{ selectedClaim.item?.title }}
            </p>
            <p class="text-[11px] text-slate-500 font-mono">
              Ref: {{ selectedClaim.item?.reference_code || 'N/A' }}
            </p>
          </div>
          <span
            :class="[
              'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize',
              getClaimBadgeClass(selectedClaim.status),
            ]"
          >
            {{ statusLabels[selectedClaim.status] ?? selectedClaim.status }}
          </span>
        </div>

        <div class="space-y-2">
          <p class="font-bold text-slate-700 dark:text-slate-300">
            Ownership Proof / Notes:
          </p>
          <div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-xl text-slate-600 dark:text-slate-300 leading-relaxed">
            {{ selectedClaim.explanation || 'No additional message provided.' }}
          </div>
        </div>

        <!-- Return Confirmation Action if Approved -->
        <div
          v-if="selectedClaim.status === 'approved' && selectedClaim.return_record"
          class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 space-y-2"
        >
          <div class="flex items-center gap-2 text-emerald-800 dark:text-emerald-300 font-bold">
            <CheckCircle2 class="h-4 w-4" />
            <span>Claim Approved & Ready for Handover</span>
          </div>
          <p class="text-[11px] text-emerald-700 dark:text-emerald-400">
            Custodian handover scheduled. If you have received your item from the campus lost & found office, please confirm below.
          </p>
          <div v-if="!selectedClaim.return_record.recipient_confirmed && !selectedClaim.return_record.confirmed_at" class="pt-2">
            <AppButton
              variant="primary"
              size="sm"
              :loading="confirmingReturn"
              @click="handleConfirmCollection"
            >
              Confirm Item Received
            </AppButton>
          </div>
          <div v-else class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5 pt-1">
            <CheckCircle2 class="h-3.5 w-3.5" />
            Item handover confirmed on {{ formatDate(selectedClaim.return_record.confirmed_at || selectedClaim.return_record.created_at) }}.
          </div>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <AppButton variant="ghost" size="sm" @click="detailModalOpen = false">
            {{ t('common.close') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
