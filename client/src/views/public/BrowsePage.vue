<script setup lang="ts">
import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import { usePublicItems } from '@/features/lookups/composables/usePublicItems'
import { useItemFilters } from '@/features/items/composables/useItemFilters'
import ItemGrid from '@/features/items/components/ItemGrid.vue'
import ItemFilters from '@/features/items/components/ItemFilters.vue'
import AppPagination from '@/components/ui/AppPagination.vue'
import { t } from '@/i18n'

const route = useRoute()
const { items, loading, pagination, fetchItems } = usePublicItems()
const { filters, resetFilters, setPage } = useItemFilters()

function syncFromQuery() {
  if (route.query.search !== undefined) filters.search = String(route.query.search || '')
  if (route.query.type !== undefined) filters.type = (route.query.type as any) || ''
  if (route.query.status !== undefined) filters.status = (route.query.status as any) || ''
  if (route.query.category_id !== undefined) filters.category_id = route.query.category_id ? Number(route.query.category_id) : ''
  if (route.query.campus_id !== undefined) filters.campus_id = route.query.campus_id ? Number(route.query.campus_id) : ''
  if (route.query.sort !== undefined) filters.sort = String(route.query.sort || 'newest')
  if (route.query.page !== undefined) filters.page = Number(route.query.page) || 1
}

async function loadItems() {
  await fetchItems({
    search: filters.search && filters.search.length >= 3 ? filters.search : undefined,
    type: filters.type || undefined,
    status: filters.status || undefined,
    category_id: filters.category_id || undefined,
    campus_id: filters.campus_id || undefined,
    sort: filters.sort || undefined,
    page: filters.page,
  })
}

let filterTimer: ReturnType<typeof setTimeout>
function onFilterChange() {
  clearTimeout(filterTimer)
  filterTimer = setTimeout(() => {
    filters.page = 1
    loadItems()
  }, 250)
}

function onReset() {
  resetFilters()
  loadItems()
}

function onPageChange(page: number) {
  setPage(page)
  loadItems()
}

let isInitialMount = true

watch(() => route.query, () => {
  if (isInitialMount) return
  syncFromQuery()
  loadItems()
})

onMounted(() => {
  syncFromQuery()
  loadItems()
  isInitialMount = false
})
</script>

<template>
  <DefaultLayout>
    <div class="space-y-4 sm:space-y-5">
      <!-- Page Header -->
      <div>
        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-slate-900 dark:text-white">{{ t('browse.title') }}</h1>
        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5 font-medium">
          {{ t('browse.subtitle') }}
        </p>
      </div>

      <!-- Filters -->
      <ItemFilters
        :filters="filters"
        @filter-change="onFilterChange"
        @reset="onReset"
      />

      <!-- Results Count -->
      <div v-if="pagination" class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
        <span>{{ pagination.total }} {{ t('browse.itemsFound') }}</span>
        <span>{{ t('common.showing') }} {{ pagination.current_page }} {{ t('common.of') }} {{ pagination.last_page }}</span>
      </div>

      <!-- Grid -->
      <ItemGrid :items="items" :loading="loading" />

      <!-- Pagination -->
      <AppPagination
        v-if="pagination && pagination.last_page > 1"
        :current-page="pagination.current_page"
        :last-page="pagination.last_page"
        :total="pagination.total"
        @change="onPageChange"
      />
    </div>
  </DefaultLayout>
</template>
