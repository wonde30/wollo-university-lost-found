<script setup lang="ts">
import { onMounted, reactive, computed, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useItemsStore } from '@/features/items/stores/items.store'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { useAuthStore } from '@/features/auth/stores/auth.store'
import { currentLocale, t } from '@/i18n'
import { formatDate } from '@/utils/date'
import { getErrorMessage } from '@/utils/error-handler'
import type { Item, ItemDetail, ItemStatus } from '@/features/items/types/item.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import AppAvatar from '@/components/ui/AppAvatar.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Package,
  Search,
  Filter,
  X,
  Plus,
  RotateCcw,
  Download,
  Copy,
  Check,
  Eye,
  Trash2,
  ShieldCheck,
  CheckSquare,
  Square,
  AlertCircle,
  CheckCircle2,
  Tag,
  MapPin,
  Calendar,
  ExternalLink,
  Sparkles,
  Archive,
  AlertTriangle,
  User as UserIcon,
} from 'lucide-vue-next'

const route = useRoute()
const itemsStore = useItemsStore()
const referencesStore = useReferencesStore()
const uiStore = useUiStore()
const authStore = useAuthStore()

// State
const showFilters = ref(false)
const isRefreshing = ref(false)
const copiedKey = ref<string | null>(null)
const selectedItemIds = ref<number[]>([])

// Filter State
const filters = reactive({
  search: '',
  type: (route.query.type as string) || 'all',
  status: (route.query.status as string) || 'all',
  campus_id: 'all',
  category_id: 'all',
})

const currentPage = ref(1)
const perPage = ref(10)

// Modals State
const detailModalOpen = ref(false)
const selectedItemDetail = ref<ItemDetail | null>(null)
const loadingDetail = ref(false)

const statusModalOpen = ref(false)
const itemToUpdateStatus = ref<Item | null>(null)
const statusForm = reactive({
  status: '' as ItemStatus | '',
  remarks: '',
})
const isUpdatingStatus = ref(false)

const deleteModalOpen = ref(false)
const itemToDelete = ref<Item | null>(null)
const isDeleting = ref(false)

// Options
const typeOptions = computed(() => [
  { value: 'all', label: currentLocale.value === 'am' ? 'ሁሉም የሪፖርት ዓይነቶች' : 'All Report Types' },
  { value: 'lost', label: currentLocale.value === 'am' ? 'የጠፉ ዕቃዎች (ሪፖርት የተደረጉ)' : 'Lost Items (Reported Lost)' },
  { value: 'found', label: currentLocale.value === 'am' ? 'የተገኙ ዕቃዎች (የገቡ)' : 'Found Items (Turned In)' },
])

const statusOptions = computed(() => [
  { value: 'all', label: currentLocale.value === 'am' ? 'ሁሉም ሁኔታዎች' : 'All Statuses' },
  { value: 'lost', label: currentLocale.value === 'am' ? 'የጠፉ (ፍለጋ ላይ ያሉ)' : 'Lost (Active Inquiries)' },
  { value: 'found_unclaimed', label: currentLocale.value === 'am' ? 'የተገኙ - ባለቤት ያልተገኘላቸው' : 'Found - Unclaimed in Vault' },
  { value: 'found_claimed', label: currentLocale.value === 'am' ? 'የተገኙ - ይገባኛል የተባለላቸው / ለመረከብ ዝግጁ' : 'Found - Claim Approved / Pending Handover' },
  { value: 'returned', label: currentLocale.value === 'am' ? 'ለባለቤቱ የተመለሱ' : 'Returned to Owner (Resolved)' },
  { value: 'withdrawn', label: currentLocale.value === 'am' ? 'በአሳዋቂው የተሰረዙ' : 'Withdrawn by Reporter' },
  { value: 'disposed', label: currentLocale.value === 'am' ? 'የተወገዱ / የተለገሱ' : 'Disposed / Donated' },
])

const campusOptions = computed(() => [
  { value: 'all', label: 'All Campuses' },
  ...referencesStore.campuses.map(c => ({
    value: String(c.id),
    label: (currentLocale.value === 'am' && c.display_name_am) ? `${c.display_name_am} (${c.code || c.short_code})` : `${c.name} (${c.code || c.short_code})`,
  })),
])

const categoryOptions = computed(() => [
  { value: 'all', label: 'All Categories' },
  ...referencesStore.categories.map(c => ({
    value: String(c.id),
    label: (currentLocale.value === 'am' && c.display_name_am) ? c.display_name_am : c.name,
  })),
])

// Metrics Calculation
const totalItemsCount = computed(() => itemsStore.pagination?.total || itemsStore.items.length)
const lostItemsCount = computed(() => itemsStore.items.filter(i => i.type === 'lost').length)
const foundItemsCount = computed(() => itemsStore.items.filter(i => i.type === 'found').length)
const returnedItemsCount = computed(() => itemsStore.items.filter(i => i.status === 'returned').length)

// Multi-Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (itemsStore.items.length === 0) return false
  return itemsStore.items.every(i => selectedItemIds.value.includes(i.id))
})

function toggleSelectAll() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = itemsStore.items.map(i => i.id)
    selectedItemIds.value = selectedItemIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = itemsStore.items.map(i => i.id)
    selectedItemIds.value = Array.from(new Set([...selectedItemIds.value, ...pageIds]))
  }
}

function toggleSelectItem(id: number) {
  const idx = selectedItemIds.value.indexOf(id)
  if (idx !== -1) selectedItemIds.value.splice(idx, 1)
  else selectedItemIds.value.push(id)
}

function copyReferenceCode(code: string) {
  navigator.clipboard.writeText(code)
  copiedKey.value = code
  uiStore.success(`Copied: ${code}`)
  setTimeout(() => {
    if (copiedKey.value === code) copiedKey.value = null
  }, 2000)
}

// Fetch Items
async function loadItems(page = 1) {
  currentPage.value = page
  const params: any = {
    page,
    per_page: perPage.value,
  }

  if (filters.search.trim()) params.search = filters.search.trim()
  if (filters.type !== 'all') params.type = filters.type
  if (filters.status !== 'all') params.status = filters.status
  if (filters.campus_id !== 'all') params.campus_id = Number(filters.campus_id)
  if (filters.category_id !== 'all') params.category_id = Number(filters.category_id)

  try {
    await itemsStore.fetchItems(params, true)
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to fetch items catalogue'))
  }
}

let searchTimer: any = null
function onSearchInput(val: string) {
  filters.search = val
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    loadItems(1)
  }, 350)
}

function onFilterChange() {
  loadItems(1)
}

function resetFilters() {
  filters.search = ''
  filters.type = 'all'
  filters.status = 'all'
  filters.campus_id = 'all'
  filters.category_id = 'all'
  loadItems(1)
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await Promise.all([
      loadItems(currentPage.value),
      referencesStore.fetchCampuses(true),
      referencesStore.fetchCategories(true),
    ])
    uiStore.success('Items directory refreshed')
  } catch (err) {
    uiStore.error('Failed to refresh data')
  } finally {
    isRefreshing.value = false
  }
}

// View Detail Modal
async function openDetailModal(item: Item) {
  detailModalOpen.value = true
  loadingDetail.value = true
  try {
    const detail = await itemsStore.fetchItemDetail(item.id)
    selectedItemDetail.value = detail
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to load item full details'))
  } finally {
    loadingDetail.value = false
  }
}

// Status Change Modal
function openStatusModal(item: Item) {
  itemToUpdateStatus.value = item
  statusForm.status = item.status
  statusForm.remarks = ''
  statusModalOpen.value = true
}

async function handleSaveStatus() {
  if (!itemToUpdateStatus.value || !statusForm.status) return
  isUpdatingStatus.value = true
  try {
    await itemsStore.updateItemStatus(itemToUpdateStatus.value.id, {
      status: statusForm.status,
      remarks: statusForm.remarks.trim() || undefined,
    })
    uiStore.success(`Item status updated to "${statusForm.status.replace(/_/g, ' ')}"`)
    statusModalOpen.value = false
    await loadItems(currentPage.value)
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to update item status'))
  } finally {
    isUpdatingStatus.value = false
  }
}

// Delete Item Modal
function openDeleteModal(item: Item) {
  itemToDelete.value = item
  deleteModalOpen.value = true
}

async function handleConfirmDelete() {
  if (!itemToDelete.value) return
  isDeleting.value = true
  try {
    await itemsStore.deleteItem(itemToDelete.value.id)
    uiStore.success('Item removed successfully')
    deleteModalOpen.value = false
    await loadItems(currentPage.value)
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to delete item'))
  } finally {
    isDeleting.value = false
  }
}

// Export CSV
function exportItemsCsv() {
  if (itemsStore.items.length === 0) {
    uiStore.info('No items available to export.')
    return
  }
  const headers = ['Reference Code', 'Type', 'Title', 'Category', 'Campus', 'Location', 'Reporter Name', 'Reporter Email', 'Status', 'Date Reported']
  const rows = itemsStore.items.map(i => [
    `"${i.reference_code || i.id}"`,
    `"${i.type}"`,
    `"${i.title.replace(/"/g, '""')}"`,
    `"${i.category?.name || ''}"`,
    `"${i.campus?.name || ''}"`,
    `"${i.location?.name || ''}"`,
    `"${i.reporter?.full_name || i.reporter?.name || ''}"`,
    `"${i.reporter?.email || ''}"`,
    `"${i.status}"`,
    `"${formatDate(i.created_at)}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const link = document.createElement('a')
  link.setAttribute('href', encodeURI(csvContent))
  link.setAttribute('download', `wollo_items_master_registry_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Exported items directory to CSV')
}

// Status Badges
function getStatusBadgeClass(status: string): string {
  switch (status) {
    case 'lost':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/80 dark:border-amber-800'
    case 'found_unclaimed':
      return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/80 dark:border-blue-800'
    case 'found_claimed':
      return 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/80 dark:border-purple-800'
    case 'returned':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800'
    case 'withdrawn':
      return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300 dark:border-slate-700'
    case 'disposed':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/80 dark:border-rose-800'
    default:
      return 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700'
  }
}

onMounted(async () => {
  await Promise.all([
    loadItems(1),
    referencesStore.fetchCampuses(true),
    referencesStore.fetchCategories(true),
    referencesStore.fetchLocations(true),
  ])
})

watch(() => route.query, () => {
  if (route.query.type) filters.type = route.query.type as string
  if (route.query.status) filters.status = route.query.status as string
  loadItems(1)
})
</script>

<template>
  <div class="space-y-4 sm:space-y-5">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Package class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.lostAndFound') || 'Lost & Found' }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">Items Directory & Registry</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Items -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ currentLocale === 'am' ? 'አጠቃላይ የተመዘገቡ ዕቃዎች' : 'Total Catalogued Items' }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalItemsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Package class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Lost -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ currentLocale === 'am' ? 'የጠፉ ዕቃዎች ሪፖርት' : 'Active Lost Reports' }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ lostItemsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <AlertCircle class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Found in Vaults -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ currentLocale === 'am' ? 'የገቡ የተገኙ ዕቃዎች' : 'Found Items Turned In' }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ foundItemsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Returned / Resolved -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            {{ currentLocale === 'am' ? 'ለባለቤቱ የተመለሱ' : 'Returned & Resolved' }}
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ returnedItemsCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
          <ShieldCheck class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title & Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ currentLocale === 'am' ? 'የዕቃዎች ማውጫ እና መዝገብ' : 'Items Directory & Registry' }}
        </h1>
        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800">
          {{ totalItemsCount }} {{ currentLocale === 'am' ? 'አጠቃላይ' : 'Total' }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ currentLocale === 'am' ? 'በወሎ ዩኒቨርሲቲ ግቢዎች የተመዘገቡ የጠፉ፣ የተገኙ እና የማከማቻ ዕቃዎች ዝርዝር መዝገብ።' : 'Comprehensive institutional ledger of all reported lost items, turned-in found items, and custody assignments across Wollo University.' }}
      </p>
    </div>

    <!-- Main Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search and Filter Toggle -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="admin-items-search"
            :placeholder="currentLocale === 'am' ? 'በርዕስ፣ መለያ ቁጥር፣ ብራንድ ወይም አሳዋቂ ይፈልጉ...' : 'Search by title, reference #, brand, serial, reporter...'"
            :model-value="filters.search"
            class="w-full text-sm"
            @update:model-value="onSearchInput"
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
          <span>{{ showFilters ? (currentLocale === 'am' ? 'አጣራ ዝጋ' : 'Hide Filter') : (currentLocale === 'am' ? 'አጣራ' : 'Filter') }}</span>
        </button>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 shrink-0">
        <!-- Export CSV -->
        <button
          type="button"
          :title="t('common.exportCsv')"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer"
          @click="exportItemsCsv"
        >
          <Download class="h-4 w-4" />
        </button>

        <!-- Refresh -->
        <button
          type="button"
          :title="t('common.refresh')"
          :disabled="isRefreshing || itemsStore.loading"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer disabled:opacity-50"
          @click="handleRefresh"
        >
          <RotateCcw class="h-4 w-4" :class="isRefreshing || itemsStore.loading ? 'animate-spin' : ''" />
        </button>

        <!-- Report Lost Button -->
        <RouterLink to="/student/report-lost">
          <AppButton variant="outline" size="md">
            <template #icon-left>
              <AlertCircle class="h-4 w-4 mr-1 text-amber-600" />
            </template>
            {{ t('items.reportLost') }}
          </AppButton>
        </RouterLink>

        <!-- Report Found Button -->
        <RouterLink to="/student/report-found">
          <AppButton variant="primary" size="md">
            <template #icon-left>
              <Plus class="h-4 w-4 mr-1" />
            </template>
            {{ t('items.reportFound') }}
          </AppButton>
        </RouterLink>
      </div>
    </div>

    <!-- Collapsible Filters -->
    <div
      v-if="showFilters"
      class="p-4 rounded-2xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 shadow-2xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 transition-all duration-200"
    >
      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ currentLocale === 'am' ? 'የሪፖርት ዓይነት' : 'Report Type' }}
        </label>
        <AppSelect
          v-model="filters.type"
          :options="typeOptions"
          class="w-full"
          @change="onFilterChange"
        />
      </div>

      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ currentLocale === 'am' ? 'ሁኔታ' : 'Status' }}
        </label>
        <AppSelect
          v-model="filters.status"
          :options="statusOptions"
          class="w-full"
          @change="onFilterChange"
        />
      </div>

      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ currentLocale === 'am' ? 'ግቢ' : 'Campus' }}
        </label>
        <AppSelect
          v-model="filters.campus_id"
          :options="campusOptions"
          class="w-full"
          @change="onFilterChange"
        />
      </div>

      <div>
        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
          {{ currentLocale === 'am' ? 'ምድብ' : 'Category' }}
        </label>
        <AppSelect
          v-model="filters.category_id"
          :options="categoryOptions"
          class="w-full"
          @change="onFilterChange"
        />
      </div>

      <div class="sm:col-span-2 lg:col-span-4 flex justify-end gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
        <AppButton variant="ghost" size="sm" @click="resetFilters">
          {{ currentLocale === 'am' ? 'ማጣሪያዎችን ዳግም አስጀምር' : 'Reset Filters' }}
        </AppButton>
      </div>
    </div>

    <!-- Main Data Table Container -->
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] overflow-hidden shadow-2xs">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 dark:bg-slate-800/60 text-slate-600 dark:text-slate-300 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200 dark:border-slate-800">
            <tr>
              <th class="py-3 px-4 w-10 text-center">
                <button
                  type="button"
                  class="cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                  @click="toggleSelectAll"
                >
                  <CheckSquare v-if="isAllCurrentPageSelected" class="h-4 w-4 text-[#0B5D3B]" />
                  <Square v-else class="h-4 w-4" />
                </button>
              </th>
              <th class="py-3 px-4">{{ currentLocale === 'am' ? 'ዓይነት እና መለያ' : 'Type & Reference' }}</th>
              <th class="py-3 px-4">{{ currentLocale === 'am' ? 'የዕቃው ዝርዝር' : 'Item Details' }}</th>
              <th class="py-3 px-4">{{ currentLocale === 'am' ? 'አሳዋቂ / ምንጭ' : 'Reporter / Source' }}</th>
              <th class="py-3 px-4">{{ currentLocale === 'am' ? 'ግቢ እና ቦታ' : 'Campus & Location' }}</th>
              <th class="py-3 px-4">{{ t('common.date') }}</th>
              <th class="py-3 px-4">{{ t('common.status') }}</th>
              <th class="py-3 px-4 text-center w-24">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-normal">
            <!-- Loading Skeletons -->
            <tr v-if="itemsStore.loading" v-for="n in 6" :key="n" class="animate-pulse">
              <td class="p-4"><div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-28 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-48 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-28 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-20 bg-slate-200 dark:bg-slate-800 rounded"></div></td>
              <td class="p-4"><div class="h-5 w-24 bg-slate-200 dark:bg-slate-800 rounded-full"></div></td>
              <td class="p-4"><div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-lg mx-auto"></div></td>
            </tr>

            <!-- Empty State -->
            <tr v-else-if="itemsStore.items.length === 0">
              <td colspan="8" class="text-center py-16 px-4">
                <div class="max-w-sm mx-auto flex flex-col items-center">
                  <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                    <Package class="h-7 w-7" />
                  </div>
                  <h3 class="text-base font-bold text-slate-900 dark:text-white">No items found</h3>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    No items match the active filter criteria or search keyword.
                  </p>
                  <AppButton variant="outline" size="sm" @click="resetFilters">
                    Clear Filters
                  </AppButton>
                </div>
              </td>
            </tr>

            <!-- Table Rows -->
            <tr
              v-else
              v-for="item in itemsStore.items"
              :key="item.id"
              :class="[
                'hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors',
                selectedItemIds.includes(item.id) ? 'bg-emerald-50/40 dark:bg-emerald-950/20' : '',
              ]"
            >
              <!-- Checkbox -->
              <td class="p-4 text-center">
                <button
                  type="button"
                  class="cursor-pointer text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                  @click="toggleSelectItem(item.id)"
                >
                  <CheckSquare v-if="selectedItemIds.includes(item.id)" class="h-4 w-4 text-[#0B5D3B]" />
                  <Square v-else class="h-4 w-4" />
                </button>
              </td>

              <!-- Type & Reference Code -->
              <td class="p-4">
                <div class="flex flex-col gap-1 items-start">
                  <div class="flex items-center gap-1.5">
                    <span
                      :class="[
                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase tracking-wide',
                        item.type === 'lost'
                          ? 'bg-amber-100 dark:bg-amber-950/80 text-amber-800 dark:text-amber-300'
                          : 'bg-emerald-100 dark:bg-emerald-950/80 text-emerald-800 dark:text-emerald-300',
                      ]"
                    >
                      <AlertCircle v-if="item.type === 'lost'" class="h-3 w-3" />
                      <CheckCircle2 v-else class="h-3 w-3" />
                      {{ item.type }}
                    </span>

                    <span
                      v-if="item.is_high_value"
                      class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-yellow-100 dark:bg-yellow-950/80 text-yellow-800 dark:text-yellow-300"
                      title="High Value Item (>5,000 ETB)"
                    >
                      <Sparkles class="h-2.5 w-2.5" />
                      High Value
                    </span>
                  </div>

                  <button
                    type="button"
                    class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200 hover:text-[#0B5D3B] dark:hover:text-[#75bd97] inline-flex items-center gap-1 cursor-pointer transition-colors"
                    title="Click to copy reference code"
                    @click="copyReferenceCode(item.reference_code)"
                  >
                    <span>{{ item.reference_code }}</span>
                    <Check v-if="copiedKey === item.reference_code" class="h-3 w-3 text-emerald-500" />
                    <Copy v-else class="h-3 w-3 text-slate-400" />
                  </button>
                </div>
              </td>

              <!-- Item Details (Title, Category, Brand) -->
              <td class="p-4 max-w-xs">
                <div>
                  <button
                    type="button"
                    class="font-bold text-slate-900 dark:text-white text-left hover:text-[#0B5D3B] dark:hover:text-[#75bd97] transition-colors cursor-pointer line-clamp-1"
                    @click="openDetailModal(item)"
                  >
                    {{ item.title }}
                  </button>

                  <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    <span v-if="item.category" class="inline-flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                      <Tag class="h-3 w-3 text-slate-400" />
                      {{ (currentLocale === 'am' && item.category.display_name_am) ? item.category.display_name_am : item.category.name }}
                    </span>
                    <span v-if="item.brand">• Brand: {{ item.brand }}</span>
                    <span v-if="item.color">• Color: {{ item.color }}</span>
                  </div>
                </div>
              </td>

              <!-- Reporter / Source -->
              <td class="p-4">
                <div v-if="item.reporter" class="flex items-center gap-2">
                  <AppAvatar
                    :name="item.reporter.full_name || item.reporter.name"
                    size="sm"
                  />
                  <div class="flex flex-col text-xs">
                    <span class="font-bold text-slate-900 dark:text-white leading-tight">
                      {{ item.reporter.full_name || item.reporter.name }}
                    </span>
                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                      {{ item.reporter.university_id || item.reporter.email }}
                    </span>
                  </div>
                </div>
                <div v-else class="text-xs text-slate-400 italic">
                  Security Turned-In
                </div>
              </td>

              <!-- Campus & Location -->
              <td class="p-4">
                <div class="flex flex-col text-xs">
                  <span class="font-bold text-slate-800 dark:text-slate-200">
                    {{ item.campus?.name || 'Dessie Main Campus' }}
                  </span>
                  <span class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 flex items-center gap-1">
                    <MapPin class="h-3 w-3 text-slate-400 shrink-0" />
                    {{ item.location?.name || 'General Campus Grounds' }}
                  </span>
                </div>
              </td>

              <!-- Date -->
              <td class="p-4 text-xs text-slate-600 dark:text-slate-400 whitespace-nowrap">
                <div class="flex items-center gap-1">
                  <Calendar class="h-3.5 w-3.5 text-slate-400 shrink-0" />
                  <span>{{ formatDate(item.created_at) }}</span>
                </div>
              </td>

              <!-- Status -->
              <td class="p-4">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border capitalize whitespace-nowrap',
                    getStatusBadgeClass(item.status),
                  ]"
                >
                  {{ item.status.replace(/_/g, ' ') }}
                </span>
              </td>

              <!-- Actions 3-Dots Menu -->
              <td class="p-4 text-center">
                <AppActionMenu v-slot="{ close }" width-class="w-56">
                  <!-- View Full Details -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 text-xs font-semibold transition-colors cursor-pointer"
                    @click="close(); openDetailModal(item)"
                  >
                    <Eye class="h-4 w-4 text-blue-500" />
                    <span>Inspect Details</span>
                  </button>

                  <!-- Change Status -->
                  <button
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 text-xs font-semibold transition-colors cursor-pointer"
                    @click="close(); openStatusModal(item)"
                  >
                    <ShieldCheck class="h-4 w-4 text-[#0B5D3B]" />
                    <span>Change Status</span>
                  </button>

                  <!-- Public Tracking -->
                  <RouterLink
                    :to="`/items/${item.id}`"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 text-xs font-semibold transition-colors cursor-pointer"
                    @click="close()"
                  >
                    <ExternalLink class="h-4 w-4 text-slate-400" />
                    <span>Public Item Page</span>
                  </RouterLink>

                  <!-- Custody / Vault -->
                  <RouterLink
                    to="/staff/manage-custody"
                    class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 text-xs font-semibold transition-colors cursor-pointer"
                    @click="close()"
                  >
                    <Archive class="h-4 w-4 text-amber-500" />
                    <span>Manage Vault Custody</span>
                  </RouterLink>

                  <!-- Delete -->
                  <button
                    v-if="authStore.isAdmin"
                    type="button"
                    class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 text-xs font-semibold transition-colors cursor-pointer border-t border-slate-100 dark:border-slate-800"
                    @click="close(); openDeleteModal(item)"
                  >
                    <Trash2 class="h-4 w-4 text-rose-500" />
                    <span>Delete Item</span>
                  </button>
                </AppActionMenu>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="itemsStore.pagination && itemsStore.pagination.total > 0" class="p-3.5 border-t border-slate-100 dark:border-slate-800">
        <AppPagination
          :current-page="itemsStore.pagination.current_page"
          :last-page="itemsStore.pagination.last_page"
          :total="itemsStore.pagination.total"
          :per-page="itemsStore.pagination.per_page"
          @page-change="loadItems"
        />
      </div>
    </div>

    <!-- 1. INSPECT ITEM DETAIL MODAL -->
    <AppModal
      :open="detailModalOpen"
      title="Item Details & Lifecycle"
      size="lg"
      @close="detailModalOpen = false"
    >
      <div v-if="loadingDetail" class="py-12 text-center text-slate-400">
        <RotateCcw class="h-8 w-8 animate-spin mx-auto mb-2 text-[#0B5D3B]" />
        <p class="text-xs font-bold">Loading item telemetry...</p>
      </div>
      <div v-else-if="selectedItemDetail" class="space-y-4 text-sm">
        <!-- Header Info Card -->
        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700 flex flex-col sm:flex-row justify-between gap-3">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <span
                :class="[
                  'px-2 py-0.5 rounded text-[10px] font-extrabold uppercase',
                  selectedItemDetail.type === 'lost' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800',
                ]"
              >
                {{ selectedItemDetail.type }}
              </span>
              <span class="font-mono text-xs font-bold text-slate-800 dark:text-slate-200">
                {{ selectedItemDetail.reference_code }}
              </span>
            </div>
            <h3 class="text-lg font-black text-slate-900 dark:text-white">
              {{ selectedItemDetail.title }}
            </h3>
          </div>
          <div class="shrink-0 flex items-start">
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-bold border capitalize',
                getStatusBadgeClass(selectedItemDetail.status),
              ]"
            >
              {{ selectedItemDetail.status.replace(/_/g, ' ') }}
            </span>
          </div>
        </div>

        <!-- Description -->
        <div>
          <h4 class="text-xs font-bold uppercase text-slate-500 dark:text-slate-400 tracking-wider mb-1">
            Description
          </h4>
          <p class="text-xs text-slate-800 dark:text-slate-200 bg-white dark:bg-slate-900 p-3 rounded-lg border border-slate-200 dark:border-slate-800">
            {{ selectedItemDetail.description }}
          </p>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Campus</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedItemDetail.campus?.name || 'N/A' }}</span>
          </div>
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Category</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedItemDetail.category?.name || 'N/A' }}</span>
          </div>
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Location</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedItemDetail.location?.name || 'N/A' }}</span>
          </div>
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Estimated Value</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedItemDetail.estimated_value ? `${Number(selectedItemDetail.estimated_value).toLocaleString()} ETB` : 'Not Stated' }}</span>
          </div>
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Brand / Serial #</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ selectedItemDetail.brand || '' }} {{ selectedItemDetail.serial_number ? `(${selectedItemDetail.serial_number})` : 'N/A' }}</span>
          </div>
          <div class="p-2.5 rounded-lg bg-slate-50 dark:bg-slate-800/40 border border-slate-200/60 dark:border-slate-800">
            <span class="text-slate-400 block font-medium">Reported Date</span>
            <span class="font-bold text-slate-800 dark:text-slate-200">{{ formatDate(selectedItemDetail.created_at) }}</span>
          </div>
        </div>

        <!-- Reporter Section -->
        <div v-if="selectedItemDetail.reporter" class="p-3 rounded-lg bg-blue-50/60 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 flex items-center justify-between text-xs">
          <div class="flex items-center gap-2.5">
            <UserIcon class="h-4 w-4 text-blue-600 dark:text-blue-400" />
            <div>
              <p class="font-bold text-slate-900 dark:text-white">
                Reporter: {{ selectedItemDetail.reporter.full_name || selectedItemDetail.reporter.name }}
              </p>
              <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                ID: {{ selectedItemDetail.reporter.university_id || 'N/A' }} • Email: {{ selectedItemDetail.reporter.email }}
              </p>
            </div>
          </div>
          <RouterLink
            v-if="authStore.isAdmin"
            :to="`/admin/users/${selectedItemDetail.reporter.id}`"
            class="text-blue-600 dark:text-blue-400 font-bold hover:underline"
          >
            View User &rsaquo;
          </RouterLink>
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <AppButton variant="outline" size="sm" @click="detailModalOpen = false">
            Close
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- 2. CHANGE STATUS MODAL -->
    <AppModal
      :open="statusModalOpen"
      title="Update Item Operational Status"
      size="md"
      @close="statusModalOpen = false"
    >
      <div v-if="itemToUpdateStatus" class="space-y-4 text-sm">
        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
          <p class="text-xs text-slate-500">Target Item:</p>
          <p class="font-bold text-slate-900 dark:text-white">{{ itemToUpdateStatus.title }} ({{ itemToUpdateStatus.reference_code }})</p>
        </div>

        <div>
          <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
            New Operational Status *
          </label>
          <AppSelect
            v-model="statusForm.status"
            :options="statusOptions.filter(o => o.value !== 'all')"
            class="w-full"
          />
        </div>

        <div>
          <label class="block text-xs font-bold uppercase text-slate-600 dark:text-slate-400 mb-1.5">
            Audit Remark / Operational Note
          </label>
          <AppTextarea
            v-model="statusForm.remarks"
            placeholder="Provide reason for this status transition (e.g. Returned to claimant at Central Depot)..."
            :rows="3"
            class="w-full text-xs"
          />
        </div>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <AppButton variant="outline" size="sm" @click="statusModalOpen = false">
            Cancel
          </AppButton>
          <AppButton
            variant="primary"
            size="sm"
            :disabled="isUpdatingStatus || !statusForm.status"
            @click="handleSaveStatus"
          >
            <RotateCcw v-if="isUpdatingStatus" class="h-4 w-4 animate-spin mr-1" />
            Apply Transition
          </AppButton>
        </div>
      </template>
    </AppModal>

    <!-- 3. CONFIRM DELETE MODAL -->
    <AppModal
      :open="deleteModalOpen"
      title="Delete Item Confirmation"
      size="sm"
      @close="deleteModalOpen = false"
    >
      <div v-if="itemToDelete" class="space-y-3 text-sm">
        <div class="h-10 w-10 rounded-full bg-rose-100 dark:bg-rose-950/60 text-rose-600 flex items-center justify-center mx-auto mb-2">
          <AlertTriangle class="h-5 w-5" />
        </div>
        <p class="text-center font-bold text-slate-900 dark:text-white">
          Permanently delete item "{{ itemToDelete.title }}"?
        </p>
        <p class="text-center text-xs text-slate-500 dark:text-slate-400">
          This will withdraw and soft-delete reference <span class="font-mono font-bold">{{ itemToDelete.reference_code }}</span> and record an administrative audit entry.
        </p>
      </div>

      <template #footer>
        <div class="flex justify-end gap-2">
          <AppButton variant="outline" size="sm" @click="deleteModalOpen = false">
            Cancel
          </AppButton>
          <AppButton
            variant="danger"
            size="sm"
            :disabled="isDeleting"
            @click="handleConfirmDelete"
          >
            <RotateCcw v-if="isDeleting" class="h-4 w-4 animate-spin mr-1" />
            Delete Item
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
