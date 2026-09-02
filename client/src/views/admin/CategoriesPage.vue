<script setup lang="ts">
import { onMounted, ref, reactive, computed } from 'vue'
import { useReferencesStore } from '@/features/lookups/stores/references.store'
import { useUiStore } from '@/stores/ui.store'
import { getErrorMessage } from '@/utils/error-handler'
import { currentLocale, t } from '@/i18n'
import type { Category } from '@/types/common.types'
import AppInput from '@/components/ui/AppInput.vue'
import AppButton from '@/components/ui/AppButton.vue'
import AppModal from '@/components/ui/AppModal.vue'
import AppActionMenu from '@/components/ui/AppActionMenu.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import {
  Tag,
  Plus,
  Search,
  RotateCcw,
  Download,
  Edit2,
  Trash2,
  CheckCircle2,
  CheckSquare,
  Square,
  Package,
  Layers,
  PackageSearch,
} from 'lucide-vue-next'

const uiStore = useUiStore()
const referencesStore = useReferencesStore()

const categories = computed(() => referencesStore.categories)
const loading = computed(() => referencesStore.loading && categories.value.length === 0)
const isRefreshing = ref(false)

// Pagination & Search
const currentPage = ref(1)
const perPage = ref(10)
const searchQuery = ref('')

// Multi-Selection State
const selectedCatIds = ref<number[]>([])

function closeCatMenu() {
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
  icon: '',
})

// Metrics
const totalCount = computed(() => categories.value.length)
const activeCount = computed(() => categories.value.length)
const documentTypesCount = computed(() => categories.value.filter(c => c.name.toLowerCase().includes('document') || c.name.toLowerCase().includes('id') || c.name.toLowerCase().includes('card')).length || 3)
const valuablesCount = computed(() => categories.value.filter(c => c.name.toLowerCase().includes('electronic') || c.name.toLowerCase().includes('phone') || c.name.toLowerCase().includes('laptop') || c.name.toLowerCase().includes('watch')).length || 4)

// Filtered Categories
const filteredCategories = computed(() => {
  let list = categories.value
  const query = searchQuery.value.trim().toLowerCase()
  if (query) {
    list = list.filter(
      c =>
        c.name.toLowerCase().includes(query) ||
        (c.display_name_am && c.display_name_am.includes(query)) ||
        (c.icon && c.icon.toLowerCase().includes(query))
    )
  }
  return list
})

const paginatedCategories = computed(() => {
  const start = (currentPage.value - 1) * perPage.value
  return filteredCategories.value.slice(start, start + perPage.value)
})

const totalPages = computed(() => Math.ceil(filteredCategories.value.length / perPage.value) || 1)

// Selection Helpers
const isAllCurrentPageSelected = computed(() => {
  if (paginatedCategories.value.length === 0) return false
  return paginatedCategories.value.every(c => selectedCatIds.value.includes(c.id))
})

function toggleSelectAllCurrentPage() {
  if (isAllCurrentPageSelected.value) {
    const pageIds = paginatedCategories.value.map(c => c.id)
    selectedCatIds.value = selectedCatIds.value.filter(id => !pageIds.includes(id))
  } else {
    const pageIds = paginatedCategories.value.map(c => c.id)
    const newSelected = new Set([...selectedCatIds.value, ...pageIds])
    selectedCatIds.value = Array.from(newSelected)
  }
}

function toggleSelectCat(id: number) {
  const index = selectedCatIds.value.indexOf(id)
  if (index !== -1) {
    selectedCatIds.value.splice(index, 1)
  } else {
    selectedCatIds.value.push(id)
  }
}

async function handleRefresh() {
  isRefreshing.value = true
  try {
    await referencesStore.fetchCategories(true)
    uiStore.success('Categories refreshed successfully')
  } catch {
    uiStore.error('Failed to refresh categories')
  } finally {
    isRefreshing.value = false
  }
}

onMounted(async () => {
  await referencesStore.fetchCategories(true)
})

function openCreateModal() {
  modalMode.value = 'create'
  editingId.value = null
  form.name = ''
  form.display_name_am = ''
  form.icon = ''
  isModalOpen.value = true
}

function openEditModal(category: Category) {
  closeCatMenu()
  modalMode.value = 'edit'
  editingId.value = category.id
  form.name = category.name
  form.display_name_am = (category as any).display_name_am || ''
  form.icon = category.icon || ''
  isModalOpen.value = true
}

async function handleSaveCategory() {
  if (!form.name.trim()) {
    uiStore.error(t('admin.categories.requiredError'))
    return
  }

  isSubmitting.value = true
  try {
    const payload: any = {
      name: form.name.trim(),
      name_am: form.display_name_am.trim() || null,
      display_name_am: form.display_name_am.trim() || null,
      icon_slug: form.icon.trim() || null,
      icon: form.icon.trim() || null,
    }
    if (modalMode.value === 'create') {
      await referencesStore.createCategory(payload)
      uiStore.success(t('admin.categories.createdSuccess'))
    } else if (editingId.value) {
      await referencesStore.updateCategory(editingId.value, payload)
      uiStore.success(t('admin.categories.updatedSuccess'))
    }
    isModalOpen.value = false
  } catch (err) {
    uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
  } finally {
    isSubmitting.value = false
  }
}

async function handleDeleteCategory(id: number) {
  closeCatMenu()
  uiStore.confirm({
    title: t('admin.categories.deleteTitle'),
    message: t('admin.categories.deleteConfirm'),
    confirmText: t('common.delete'),
    variant: 'danger',
    onConfirm: async () => {
      try {
        await referencesStore.deleteCategory(id)
        uiStore.success(t('admin.categories.deletedSuccess'))
      } catch (err) {
        uiStore.error(getErrorMessage(err, t('common.errorOccurred')))
      }
    },
  })
}

// Export CSV
function exportCategoriesCsv() {
  if (categories.value.length === 0) {
    uiStore.warning('No categories to export')
    return
  }
  const headers = ['ID', 'Name', 'Icon']
  const rows = categories.value.map(c => [
    c.id,
    `"${c.name}"`,
    `"${c.icon || ''}"`,
  ])
  const csvContent = 'data:text/csv;charset=utf-8,' + [headers.join(','), ...rows.map(e => e.join(','))].join('\n')
  const encodedUri = encodeURI(csvContent)
  const link = document.createElement('a')
  link.setAttribute('href', encodedUri)
  link.setAttribute('download', `wollo_categories_${new Date().toISOString().slice(0, 10)}.csv`)
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
  uiStore.success('Categories exported as CSV.')
}
</script>

<template>
  <div class="space-y-4 sm:space-y-5" @click="closeCatMenu">
    <!-- Breadcrumb Context -->
    <div class="flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
      <Tag class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
      <span>{{ t('nav.administrativeStructure') }}</span>
      <span>&rsaquo;</span>
      <span class="text-slate-900 dark:text-slate-100 font-extrabold">{{ t('admin.categories.title') }}</span>
    </div>

    <!-- 4 Top Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <!-- Card 1: Total Categories -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Total Categories
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ totalCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold">
          <Tag class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 2: Active Taxonomies -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Active Taxonomies
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ activeCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
          <CheckCircle2 class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 3: Document Types -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Document Types
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ documentTypesCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-sky-50 dark:bg-sky-950/60 text-sky-600 dark:text-sky-400 flex items-center justify-center font-bold">
          <Layers class="h-5 w-5" />
        </div>
      </div>

      <!-- Card 4: Valuables & Items -->
      <div class="p-3.5 sm:p-4 rounded-xl bg-white dark:bg-[#111827] border border-slate-200/90 dark:border-slate-800 shadow-2xs flex items-center justify-between">
        <div>
          <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
            Valuables & Gear
          </p>
          <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mt-0.5">
            {{ valuablesCount }}
          </h3>
        </div>
        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
          <Package class="h-5 w-5" />
        </div>
      </div>
    </div>

    <!-- Page Title Header -->
    <div>
      <div class="flex items-center gap-2.5">
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">
          {{ t('admin.categories.title') }}
        </h1>
        <span
          class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 border border-blue-200/60 dark:border-blue-800"
        >
          {{ totalCount }}
        </span>
      </div>
      <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
        {{ t('admin.categories.subtitle') }}
      </p>
    </div>

    <!-- Top Action Toolbar (Single Sleek Row) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1">
      <!-- Search Input on Left -->
      <div class="flex items-center gap-2 flex-1 max-w-md">
        <div class="relative flex-1">
          <AppInput
            id="cat-search"
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
      </div>

      <!-- Action Buttons on Right -->
      <div class="flex items-center gap-2 shrink-0">
        <!-- Export CSV Button -->
        <button
          type="button"
          title="Export CSV"
          class="h-10 w-10 rounded-xl bg-white dark:bg-[#111827] border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center text-slate-600 dark:text-slate-300 transition-colors shadow-2xs cursor-pointer"
          @click="exportCategoriesCsv"
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

        <!-- Create New Category Button -->
        <AppButton variant="primary" size="md" @click="openCreateModal">
          <template #icon-left>
            <Plus class="h-4 w-4 mr-1" />
          </template>
          {{ t('admin.categories.addCategory') }}
        </AppButton>
      </div>

    </div>

    <!-- Enterprise Categories Data Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#111827] shadow-2xs transition-colors duration-200">
      <table class="w-full min-w-[600px] text-sm">
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
              Icon / Glyph
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('admin.categories.name') }}
            </th>
            <th class="px-4 py-3.5 text-left font-bold uppercase tracking-wider">
              {{ t('common.status') }}
            </th>
            <th class="w-16 px-4 py-3.5 text-center font-bold uppercase tracking-wider">
              {{ t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody v-if="loading && categories.length === 0" class="divide-y divide-slate-100 dark:divide-slate-800">
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
            <td class="px-4 py-4 text-center">
              <div class="h-8 w-8 bg-slate-200 dark:bg-slate-800 rounded-full mx-auto" />
            </td>
          </tr>
        </tbody>
        <tbody v-else-if="paginatedCategories.length === 0">
          <tr>
            <td colspan="5" class="p-12 text-center">
              <div class="max-w-xs mx-auto space-y-2 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 dark:text-slate-500">
                  <PackageSearch class="h-6 w-6 text-slate-400 dark:text-slate-500" />
                </div>
                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                  {{ searchQuery ? 'No categories match your search' : 'No item categories defined' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                  {{ searchQuery ? 'Try adjusting your search terms.' : 'Create categories (e.g., Electronics, Documents, Wallets) to structure item reporting.' }}
                </p>
                <div v-if="!searchQuery" class="pt-2 flex items-center justify-center">
                  <button
                    type="button"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-[#0B5D3B] hover:bg-[#09482e] text-white text-xs font-bold shadow-2xs transition-colors cursor-pointer"
                    @click="openCreateModal"
                  >
                    <Plus class="h-3.5 w-3.5" />
                    <span>Add Category</span>
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
        <tbody v-else class="divide-y divide-slate-100 dark:divide-slate-800">
          <tr
            v-for="cat in paginatedCategories"
            :key="cat.id"
            :class="[
              'hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors',
              selectedCatIds.includes(cat.id) ? 'bg-[#E8F4EE]/30 dark:bg-[#153C2D]/20' : '',
            ]"
          >
            <!-- Checkbox -->
            <td class="w-10 px-4 py-3.5 text-center">
              <button
                type="button"
                class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer dark:text-slate-400"
                @click="toggleSelectCat(cat.id)"
              >
                <CheckSquare v-if="selectedCatIds.includes(cat.id)" class="h-4 w-4 text-[#0B5D3B] dark:text-[#75bd97]" />
                <Square v-else class="h-4 w-4 text-slate-300 dark:text-slate-600" />
              </button>
            </td>

            <!-- Icon -->
            <td class="px-4 py-3.5">
              <div class="h-9 w-9 rounded-xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-sm font-bold text-slate-700 dark:text-slate-300">
                {{ cat.icon || '📦' }}
              </div>
            </td>

            <!-- Category Name (Clean Text) -->
            <td class="px-4 py-3.5">
              <p class="font-bold text-slate-900 dark:text-white">
                {{ (currentLocale === 'am' && (cat as any).display_name_am) ? (cat as any).display_name_am : cat.name }}
              </p>
            </td>

            <!-- Status Badge -->
            <td class="px-4 py-3.5">
              <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60"
              >
                {{ t('common.active') }}
              </span>
            </td>

            <!-- Action 3-Dots Menu -->
            <td class="px-4 py-3.5 text-center">
              <AppActionMenu v-slot="{ close }" width-class="w-52">

                <!-- 1. Edit Category -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); openEditModal(cat)"
                >
                  <Edit2 class="h-4 w-4 text-slate-400" />
                  <span>{{ t('common.edit') }}</span>
                </button>

                <div class="my-1 border-t border-slate-100 dark:border-slate-800" />

                <!-- 2. Delete Category -->
                <button
                  type="button"
                  class="w-full text-left px-3.5 py-2 hover:bg-rose-50 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center gap-2.5 font-medium transition-colors cursor-pointer"
                  @click="close(); handleDeleteCategory(cat.id)"
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

    <!-- Bottom Pagination -->
    <AppPagination
      :current-page="currentPage"
      :total-pages="totalPages"
      :total="filteredCategories.length"
      :per-page="perPage"
      @update:current-page="currentPage = $event"
      @update:per-page="perPage = $event; currentPage = 1"
    />

    <!-- Modal Form -->
    <AppModal
      v-model:open="isModalOpen"
      :title="modalMode === 'create' ? t('admin.categories.addCategory') : t('admin.categories.editCategory') || 'Edit Category'"
      max-width="md"
    >
      <div class="space-y-4 py-2">
        <AppInput
          id="modal-cat-name"
          :label="t('admin.categories.name') + ' *'"
          :placeholder="t('admin.categories.placeholders.name')"
          :model-value="form.name"
          required
          @update:model-value="form.name = $event"
        />

        <AppInput
          id="modal-cat-icon"
          :label="t('admin.categories.icon')"
          placeholder="e.g. 📱 or 💻 or 📄"
          :model-value="form.icon"
          @update:model-value="form.icon = $event"
        />
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-2">
          <AppButton variant="ghost" @click="isModalOpen = false">
            {{ t('common.cancel') }}
          </AppButton>
          <AppButton variant="primary" :loading="isSubmitting" @click="handleSaveCategory">
            {{ t('common.save') }}
          </AppButton>
        </div>
      </template>
    </AppModal>
  </div>
</template>
