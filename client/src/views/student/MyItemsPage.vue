<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { useItemsStore } from '@/features/items/stores/items.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { formatDate } from '@/utils/date'
import { t } from '@/i18n'
import type { Item } from '@/features/items/types/item.types'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppInput from '@/components/ui/AppInput.vue'
import AppSelect from '@/components/ui/AppSelect.vue'
import AppTextarea from '@/components/ui/AppTextarea.vue'
import {
  Package,
  Search,
  Filter,
  X,
  RotateCcw,
  Download,
  Plus,
  Edit2,
  Trash2,
  CheckCircle2,
  CheckSquare,
  Square,
  HelpCircle,
  PackageSearch,
} from 'lucide-vue-next'

import { getUserSummary, type UserSummaryData } from '@/features/auth/api/auth.api'

const itemsStore = useItemsStore()
const { items, loading, pagination } = storeToRefs(itemsStore)
const uiStore = useUiStore()

const isRefreshing = ref(false)
const showFilters = ref(false)
const searchQuery = ref('')
const selectedType = ref<string>('all')
const perPage = ref(10)
const currentPage = ref(1)

const userSummary = ref<UserSummaryData | null>(null)

// Multi-Selection State
const selectedItemIds = ref<number[]>([])

function closeItemMenu() {
  // Context menu handled by AppActionMenu
}

const editModalOpen = ref(false)
const editingItem = ref<Item | null>(null)
const editForm = ref({ title: '', description: '' })
const editErrors = ref<Record<string, string>>({})
const saving = ref(false)

// Metrics
const totalCount = computed(() => userSummary.value ? (userSummary.value.my_lost_count + userSummary.value.my_found_count) : (pagination.value?.total || items.value.length))
const lostCount = computed(() => userSummary.value?.my_lost_count ?? items.value.filter(i => i.type === 'lost').length)
const foundCount = computed(() => userSummary.value?.my_found_count ?? items.value.filter(i => i.type === 'found').length)
const returnedCount = computed(() => userSummary.value?.returned_items_count ?? items.value.filter(i => i.status === 'returned').length)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (items.value.length === 0) return false
  return items.value.every(i => selectedItemIds.value.includes(i.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = items.value.map(i => i.id)
    selectedItemIds.value = selectedItemIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = items.value.map(i => i.id)
    const newSelected = new Set([...selectedItemIds.value, ...pageIds])
    selectedItemIds.value = Array.from(newSelected)
  }
}

function toggleSelectItem(id: number) {
  const index = selectedItemIds.value.indexOf(id)
  if (index !== -1) {
    selectedItemIds.value.splice(index, 1)
  } else {
    selectedItemIds.value.push(id)
  }
}

const typeFilterOptions = computed(() => [
  { label: 'All Item Reports', value: 'all' },
  { label: 'Lost Items', value: 'lost' },
  { label: 'Found Items', value: 'found' },
])

async function load(page = 1) {
  currentPage.value = page
  try {
    const [summary] = await Promise.all([
      getUserSummary().catch(() => null),
      itemsStore.fetchItems({
        mine: true,
        page,
        per_page: perPage.value,
        type: selectedType.value === 'all' ? undefined : (selectedType.value as any),
        search: searchQuery.value.trim() || undefined,
      }, true),
    ])
    if (summary) {
      userSummary.value = summary
    }
  } catch (err) {
    uiStore.error(getErrorMessage(err, 'Failed to load items'))
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

function onTypeChange(val: string) {
  selectedType.value = val
  load(1)
}

function onPerPageChange() {
  load(1)
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await load(currentPage.value)
    uiStore.success('My items list refreshed')
  } catch {
    uiStore.error('Failed to refresh items')
  } finally {
    isRefreshing.value = false
  }
}

function handleEdit(item: Item) {
  closeItemMenu()
  editingItem.value = item
  editForm.value = { title: item.title, description: item.description || '' }
  editErrors.value = {}
  editModalOpen.value = true
}

async function handleSaveEdit() {
  if (!editingItem.value) return

  editErrors.value = {}
  if (!editForm.value.title.trim()) {
    editErrors.value.title = t('validation.required')
  } else if (editForm.value.title.trim().length < 3) {
    editErrors.value.title = t('validation.minLength', { min: 3 })
  }
  if (!editForm.value.description.trim()) {
    editErrors.value.description = t('validation.required')
  } else if (editForm.value.description.trim().length < 5) {
    editErrors.value.description = t('validation.minLength', { min: 5 })
  }
  if (Object.keys(editErrors.value).length > 0) return

  saving.value = true
  try {
    await itemsStore.updateItem(editingItem.value.id, {
      title: editForm.value.title.trim(),
      description: editForm.value.description.trim(),
    })
    uiStore.success(t('items.updatedSuccess'))
    editModalOpen.value = false
    editingItem.value = null
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    saving.value = false
  }
}

function handleWithdraw(item: Item) {
  closeItemMenu()
  uiStore.confirm({
    title: t('items.withdrawTitle'),
    message: t('items.withdrawConfirm', { title: item.title }),
    confirmText: t('common.withdraw'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        await itemsStore.withdrawItem(item.id)
        uiStore.success(t('items.withdrawnSuccess'))
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

function canEdit(item: Item): boolean {
  if (item.status !== 'lost' && item.status !== 'found_unclaimed') return false
  const createdAt = new Date(item.created_at).getTime()
  const now = new Date().getTime()
  const hoursSince = (now - createdAt) / (1000 * 60 * 60)
  return item.type === 'lost' ? hoursSince <= 48 : hoursSince <= 24
}

function canWithdraw(item: Item): boolean {
  return item.status === 'lost' || item.status === 'found_unclaimed'
}

function getItemBadgeClass(status: string): string {
  switch (status) {
    case 'returned':
      return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800'
    case 'in_custody':
      return 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 border-teal-200/60 dark:border-teal-800'
    case 'matched':
    case 'claimed':
    case 'found_claimed':
      return 'bg-purple-50 dark:bg-purple-950/60 text-purple-700 dark:text-purple-300 border-purple-200/60 dark:border-purple-800'
    case 'found_unclaimed':
      return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border-blue-200/60 dark:border-blue-800'
    case 'lost':
    case 'pending_verification':
    case 'pending_surrender':
      return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/60 dark:border-amber-800'
    case 'disposed':
    case 'expired':
      return 'bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border-rose-200/60 dark:border-rose-800'
    default:
      return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700'
  }
}

// Export CSV
function exportItemsCsv() {
  if (items.value.length === 0) {
    uiStore.warning('No items to export')
    return
  }
  const headers = ['Ref Code', 'Type', 'Title', 'Campus', 'Category', 'Date', 'Status']
  const rows = items.value.map(i => [
    `"${i.reference_code || i.id}"`,
    `"${i.type}"`,
    `"${i.title}"`,
    `"${i.campus?.name || ''}"`,
    `"${i.category?.name || ''}"`,
    `"${formatDate(i.created_at)}"`,
    `"${i.status}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_my_reported_items_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Items exported as CSV.')
}

onMounted(() => load())
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeItemMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Package class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.portal') || 'Student Portal' }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('nav.myItems') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Items -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Reported Items
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Package class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Lost Reports -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Lost Item Inquiries
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ lostCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <HelpCircle class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Found Submissions -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Found Items Turned In
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ foundCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Returned to Owner -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Reunited & Returned
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ returnedCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('nav.myItems') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('items.myItems.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input & Filter Toggle on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="my-items-search"
            placeholder="Search my reported items by title, category, campus..."
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
          @click="exportItemsCsv"
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

        <!-- Report Lost Button -->
        <RouterLink to="/student/report-lost">
          <AppButton variant="outline" size="md">
            <template #icon-left>
              <Plus class="h-4 w-4 mr-1" />
            </template>
            Report Lost
          </AppButton>
        </RouterLink>

        <!-- Report Found Button -->
        <RouterLink to="/student/report-found">
          <AppButton variant="primary" size="md">
            <template #icon-left>
              <Plus class="h-4 w-4 mr-1" />
            </template>
            Report Found
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
          Report Type
        </label>
        <AppSelect
          :options="typeFilterOptions"
          :model-value="selectedType"
          class="w-full text-xs"
          @update:model-value="onTypeChange(String($event))"
        />
      </div>
    </div>

    <!-- Enterprise My Items Data Table with Mobile Scroll Hints -->
    <div class="relative overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
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
              Type
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Item / Reference
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Campus & Location
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Date Reported
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              Status
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && items.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr v-for="n in 4" :key="n" class="animate-pulse">
            <td class="px-4 py-4 text-center">
              <div class="h-4 w-4 bg-slate-200 dark:bg-slate-800 rounded mx-auto" />
            </td>
            <td class="px-4 py-4">
              <div class="h-5 w-16 bg-slate-200 dark:bg-slate-800 rounded-full" />
            </td>
            <td class="px-4 py-4 space-y-1.5">
              <div class="h-4 w-40 bg-slate-200 dark:bg-slate-800 rounded" />
              <div class="h-3 w-20 bg-slate-100 dark:bg-slate-800/60 rounded" />
            </td>
            <td class="px-4 py-4">
              <div class="h-4 w-28 bg-slate-200 dark:bg-slate-800 rounded" />
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
        <tbody v-else-if="items.length === 0">
          <tr>
            <td colspan="7" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery || selectedType !== 'all' ? 'No items match your active filters' : 'No reported items yet' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery || selectedType !== 'all' ? 'Try adjusting your search terms or filter selection.' : 'Items you report as lost or found will appear in this ledger with real-time status tracking.' }}
                </p>
                <div v-if="!searchQuery && selectedType === 'all'" class="pt-2 flex items-center justify-center gap-2">
                  <RouterLink to="/student/report-lost" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold shadow-2xs transition-colors">
                    Report Lost
                  </RouterLink>
                  <RouterLink to="/student/report-found" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors">
                    Report Found
                  </RouterLink>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="item in items"
            :key="item.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedItemIds.includes(item.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectItem(item.id)"
              >
                <CheckSquare v-if="selectedItemIds.includes(item.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Type Pill -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border capitalize',
                  item.type === 'lost'
                    ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border-amber-200/60 dark:border-amber-800'
                    : 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800',
                ]"
              >
                {{ item.type }}
              </span>
            </td>

            <!-- Item / Reference -->
            <td class="px-4 py-3.5">
              <div>
                <p class="font-bold text-slate-900 dark:text-white">
                  {{ item.title }}
                </p>
                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                  <span class="font-mono">Ref: {{ item.reference_code || `#ITM-${item.id}` }}</span>
                  <span v-if="item.category?.name"> • {{ item.category.name }}</span>
                </p>
              </div>
            </td>

            <!-- Campus & Location -->
            <td class="px-4 py-3.5 text-slate-700 dark:text-slate-300 font-medium">
              {{ item.campus?.name || 'Main Campus' }}
            </td>

            <!-- Date Reported -->
            <td class="px-4 py-3.5 text-slate-500 dark:text-slate-400">
              {{ formatDate(item.created_at) }}
            </td>

            <!-- Status Pill Badge -->
            <td class="px-4 py-3.5">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border capitalize',
                  getItemBadgeClass(item.status),
                ]"
              >
                {{ item.status.replace(/_/g, ' ') }}
              </span>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Track Public Item -->
                <RouterLink
                  :to="`/items/${item.id}`"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                >
                  <Search class="h-4 w-4 text-slate-400" />
                  <span>View Public Item</span>
                </RouterLink>

                <!-- 2. Edit within Grace Period -->
                <button
                  v-if="canEdit(item)"
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleEdit(item)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <div v-if="canWithdraw(item)" class="my-1 border-t border-slate-100 dark:border-slate-800" />

                <!-- 3. Withdraw Report -->
                <button
                  v-if="canWithdraw(item)"
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleWithdraw(item)"
                >
                  <Trash2 class="h-4 w-4 text-rose-500" />
                  <span>{{ t('common.withdraw') }}</span>
                </button>
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

    <!-- Edit Modal -->
    <AppModal
      v-model:open="editModalOpen"
      :title="t('items.editTitle')"
      max-width="md"
    >
      <div v-if="editingItem" class="space-y-4 py-2 text-xs">
        <AppInput
          id="edit-item-title"
          :label="t('items.titleLabel')"
          :model-value="editForm.title"
          :error="editErrors.title"
          required
          @update:model-value="editForm.title = $event"
        />

        <AppTextarea
          id="edit-item-desc"
          :label="t('items.descriptionLabel')"
          :model-value="editForm.description"
          :error="editErrors.description"
          :rows="4"
          required
          @update:model-value="editForm.description = $event"
        />
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="editModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton
            variant="primary"
            :loading="saving"
            @click="handleSaveEdit"
          >
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
